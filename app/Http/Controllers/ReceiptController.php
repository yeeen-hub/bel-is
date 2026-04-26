<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\FeeCategory;
use App\Models\Receipt;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    // ── Helper: resolve fee for a single visit ────────────────────────────────
    private function resolveFee(VisitorVisit $visit): float
    {
        if (!$visit->visitor_category) return 0;
        $cat = FeeCategory::where('category', $visit->visitor_category)->first();
        return $cat ? (float) $cat->fee : 0;
    }

    // ── Helper: format a single visit for the frontend ────────────────────────
    private function formatVisit(VisitorVisit $visit): array
    {
        return [
            'id'               => $visit->id,
            'registration_id'  => $visit->registration_id,
            'full_name'        => $visit->full_name,
            'place_of_origin'  => $visit->place_of_origin,
            'purpose'          => $visit->purpose,
            'duration'         => $visit->duration_of_stay,
            'visitor_category' => $visit->visitor_category,
            'category_fee'     => $this->resolveFee($visit),
            'fee_status'       => $visit->fee_status,
            'arrival_at'       => $visit->arrival_at->format('M d, Y'),
        ];
    }

    // ── Helper: get all visits in the same group payment batch ────────────────
    private function resolveGroupVisits(VisitorVisit $visitor, string $status = 'Pending')
    {
        if ($visitor->group_code) {
            $visits = VisitorVisit::where('group_code', $visitor->group_code)
                ->where('fee_status', $status)
                ->orderBy('created_at')
                ->get();

            if ($visits->isNotEmpty()) return $visits;
        }

        return collect([$visitor]);
    }

    // ── Helper: build member breakdown array ──────────────────────────────────
    private function buildBreakdown($visits, bool $isWaived = false): array
    {
        return $visits->map(function ($v) use ($isWaived) {
            return [
                'visit_id'         => $v->id,
                'registration_id'  => $v->registration_id,
                'full_name'        => $v->full_name,
                'visitor_category' => $v->visitor_category,
                'fee'              => $isWaived ? 0 : $this->resolveFee($v),
            ];
        })->values()->toArray();
    }

    // ── Show payment form ─────────────────────────────────────────────────────
    // GET /adminpay/{visitor}
    public function showPayment(VisitorVisit $visitor)
    {
        $visits   = $this->resolveGroupVisits($visitor, 'Pending');
        $isGroup  = $visits->count() > 1;
        $members  = $visits->map(fn($v) => $this->formatVisit($v))->values()->toArray();
        $totalDue = collect($members)->sum('category_fee');

        return Inertia::render('AdminPayPage', [
            'visitor'       => $this->formatVisit($visitor),
            'groupMembers'  => $members,
            'isGroup'       => $isGroup,
            'totalDue'      => $totalDue,
            'feeCategories' => FeeCategory::orderBy('id')->get(['id', 'category', 'age_range', 'fee']),
        ]);
    }

    // ── Store receipt / collect payment ───────────────────────────────────────
    // POST /adminpay/{visitor}
    public function store(Request $request, VisitorVisit $visitor)
    {
        $request->validate([
            'fee_type'       => 'required|in:Collected,Waived',
            'payment_method' => 'required|in:Cash',
            'notes'          => 'nullable|string|max:500',
            'waiver_reason'  => 'required_if:fee_type,Waived|nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $isWaived = $request->fee_type === 'Waived';

            $visits           = $this->resolveGroupVisits($visitor, 'Pending');
            $totalAmount      = $isWaived ? 0 : $visits->sum(fn($v) => $this->resolveFee($v));
            $numberOfVisitors = $visits->count();

            $receiptNumber = 'OR-' . Carbon::now()->format('Y') . '-' . str_pad(
                Receipt::whereYear('collected_at', Carbon::now()->year)->count() + 1,
                7, '0', STR_PAD_LEFT
            );

            $receipt = Receipt::create([
                'receipt_number'     => $receiptNumber,
                'visit_id'           => $visitor->id,
                'amount'             => $numberOfVisitors > 0
                                            ? round($totalAmount / $numberOfVisitors, 2)
                                            : 0,
                'currency'           => 'PHP',
                'fee_type'           => $isWaived ? 'Waived' : 'Standard',
                'number_of_visitors' => $numberOfVisitors,
                'total_amount'       => $totalAmount,
                'waiver_reason'      => $request->waiver_reason,
                'payment_method'     => $request->payment_method,
                'collected_by'       => Auth::id(),
                'collected_at'       => now(),
                'notes'              => $request->notes ?: null,
            ]);

            if (DB::getSchemaBuilder()->hasColumn('receipts', 'member_breakdown')) {
                $breakdown = $this->buildBreakdown($visits, $isWaived);
                $receipt->forceFill(['member_breakdown' => json_encode($breakdown)])->save();
            }

            foreach ($visits as $v) {
                $v->update([
                    'fee_status'    => $isWaived ? 'Waived' : 'Collected',
                    'waiver_reason' => $request->waiver_reason,
                ]);
            }

            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'payment_collected',
                'module'      => 'receipts',
                'target_type' => 'Receipt',
                'target_id'   => $receipt->id,
                'new_values'  => $receipt->toArray(),
                'ip_address'  => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('adminreceipt', $visitor->id)
                ->with('success', 'Payment collected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Payment failed: ' . $e->getMessage()]);
        }
    }

    // ── Show receipt ──────────────────────────────────────────────────────────
    // GET /adminreceipt/{visitor}
    public function showReceipt(VisitorVisit $visitor)
    {
        $visitor->load('receipt', 'registeredBy');

        // ── Option A: Find group receipt via group_code ───────────────────────
        // A group payment creates ONE receipt anchored to the leader's visit_id.
        // When a member's receipt page is opened, $visitor->receipt is null
        // because no receipt row has visit_id = member's id.
        //
        // Fix: if receipt is missing but the visit belongs to a group,
        // look up the receipt through any visit in the same group_code.
        // This means ALL members see the exact same receipt — which is correct
        // because they were paid together in a single transaction.
        // No duplicate receipt numbers, no data inconsistency.
        $receipt = $visitor->receipt;

        if (!$receipt && $visitor->group_code) {
            $receipt = Receipt::whereHas('visit', function ($q) use ($visitor) {
                $q->where('group_code', $visitor->group_code);
            })->first();
        }

        // ── Rebuild member breakdown ──────────────────────────────────────────
        $isWaived = $receipt?->fee_type === 'Waived';

        $memberBreakdown = [];

        if ($visitor->group_code) {
            $groupVisits = VisitorVisit::where('group_code', $visitor->group_code)
                ->whereIn('fee_status', ['Collected', 'Waived'])
                ->orderBy('created_at')
                ->get();

            if ($groupVisits->isNotEmpty()) {
                $memberBreakdown = $this->buildBreakdown($groupVisits, $isWaived);
            }
        }

        // Fallback for individual visit
        if (empty($memberBreakdown)) {
            $memberBreakdown = $this->buildBreakdown(collect([$visitor]), $isWaived);
        }

        $isGroup = count($memberBreakdown) > 1;

        return Inertia::render('AdminRecpPage', [
            'visitor' => [
                'id'               => $visitor->id,
                'registration_id'  => $visitor->registration_id,
                'full_name'        => $visitor->full_name,
                'place_of_origin'  => $visitor->place_of_origin,
                'municipality'     => $visitor->snapshot_municipality,
                'province'         => $visitor->snapshot_province,
                'purpose'          => $visitor->purpose,
                'duration'         => $visitor->duration_of_stay,
                'contact_number'   => $visitor->snapshot_contact_number ?? 'N/A',
                'visitor_category' => $visitor->visitor_category,
                'fee_status'       => $visitor->fee_status,
                'arrival_at'       => $visitor->arrival_at->format('M d, Y'),
            ],
            // $receipt is either the visit's own receipt or the group leader's
            // receipt found via group_code — same receipt number for all members,
            // which is correct: one transaction, one OR number.
            'receipt' => $receipt ? [
                'id'                 => $receipt->id,
                'receipt_number'     => $receipt->receipt_number,
                'fee_type'           => $receipt->fee_type,
                'waiver_reason'      => $receipt->waiver_reason,
                'number_of_visitors' => $receipt->number_of_visitors,
                'amount'             => $receipt->amount,
                'total_amount'       => $receipt->total_amount,
                'payment_method'     => $receipt->payment_method,
                'collected_at'       => $receipt->collected_at->format('M d, Y'),
                'notes'              => $receipt->notes,
                'member_breakdown'   => $memberBreakdown,
            ] : null,
            'isGroup' => $isGroup,
        ]);
    }

    // ── Mark as No Show ───────────────────────────────────────────────────────
    public function markNoShow(VisitorVisit $visitor): \Illuminate\Http\RedirectResponse
    {
        abort_unless($visitor->fee_status === 'Pending', 422, 'Only Pending visits can be marked as No Show.');

        DB::beginTransaction();
        try {
            if ($visitor->group_code) {
                $affected = VisitorVisit::where('group_code', $visitor->group_code)
                    ->where('fee_status', 'Pending')
                    ->get();
                foreach ($affected as $v) {
                    $v->fee_status = 'No Show';
                    $v->save();
                    AuditLog::create([
                        'user_id'     => Auth::id(),
                        'action'      => 'marked_no_show',
                        'module'      => 'visitor_visits',
                        'target_type' => 'VisitorVisit',
                        'target_id'   => $v->id,
                        'ip_address'  => request()->ip(),
                    ]);
                }
            } else {
                $visitor->fee_status = 'No Show';
                $visitor->save();
                AuditLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'marked_no_show',
                    'module'      => 'visitor_visits',
                    'target_type' => 'VisitorVisit',
                    'target_id'   => $visitor->id,
                    'ip_address'  => request()->ip(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('visitor-records')
            ->with('success', 'Visit marked as No Show.');
    }
}