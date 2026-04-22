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
    // ─────────────────────────────────────────────────────────────────────────
    // Helper: resolve the fee amount for a single visit from its category.
    // Falls back to 0 if the category is not found in fee_categories.
    // ─────────────────────────────────────────────────────────────────────────
    private function resolveFee(VisitorVisit $visit): float
    {
        if (!$visit->visitor_category) return 0;
        $cat = FeeCategory::where('category', $visit->visitor_category)->first();
        return $cat ? (float) $cat->fee : 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: format a single visit into the shape the frontend expects.
    // ─────────────────────────────────────────────────────────────────────────
    private function formatVisit(VisitorVisit $visit): array
    {
        $fee = $this->resolveFee($visit);
        return [
            'id'               => $visit->id,
            'registration_id'  => $visit->registration_id,
            'full_name'        => $visit->full_name,
            'place_of_origin'  => $visit->place_of_origin,
            'purpose'          => $visit->purpose,
            'duration'         => $visit->duration_of_stay,
            'visitor_category' => $visit->visitor_category,
            'category_fee'     => $fee,
            'fee_status'       => $visit->fee_status,
            'arrival_at'       => $visit->arrival_at->format('M d, Y'),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Show payment form
    // Route: GET /adminpay/{visitor}
    // ─────────────────────────────────────────────────────────────────────────
    public function showPayment(VisitorVisit $visitor)
    {
        // Check if this visit is part of a group (shares registration_id prefix
        // from storeGroup which uses the same sequential ID for the first member,
        // OR they share a group_code).
        // We identify a "group payment" by checking session flash for group_visit_ids,
        // but since Inertia redirects lose flash on GET, we use group_code instead.
        $groupVisits = collect([$visitor]);

        if ($visitor->group_code) {
            // Load ALL pending members of this group (same group_code, not yet paid)
            $groupVisits = VisitorVisit::where('group_code', $visitor->group_code)
                ->where('fee_status', 'Pending')
                ->orderBy('created_at')
                ->get();

            // If none are pending any more, just show the current visitor alone
            if ($groupVisits->isEmpty()) {
                $groupVisits = collect([$visitor]);
            }
        }

        $isGroup    = $groupVisits->count() > 1;
        $members    = $groupVisits->map(fn($v) => $this->formatVisit($v))->values()->toArray();
        $totalDue   = collect($members)->sum('category_fee');

        return Inertia::render('AdminPayPage', [
            // Primary visitor (the one the route was opened for)
            'visitor' => $this->formatVisit($visitor),

            // Group members (includes the primary visitor; count=1 for individuals)
            'groupMembers' => $members,
            'isGroup'      => $isGroup,
            'totalDue'     => $totalDue,

            // Fee categories only needed if staff needs to see the list
            // (kept for the waiver reason UI but NOT for overriding)
            'feeCategories' => FeeCategory::orderBy('id')->get(['id', 'category', 'age_range', 'fee']),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Store receipt / collect payment
    // Route: POST /adminpay/{visitor}
    // For groups: creates ONE receipt covering all pending group members.
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request, VisitorVisit $visitor)
    {
        $request->validate([
            'fee_type'      => 'required|in:Collected,Waived',
            'payment_method'=> 'required|in:Cash',
            'notes'         => 'nullable|string|max:500',
            'waiver_reason' => 'required_if:fee_type,Waived|nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Resolve all visits covered by this payment
            $visits = collect([$visitor]);

            if ($visitor->group_code) {
                $visits = VisitorVisit::where('group_code', $visitor->group_code)
                    ->where('fee_status', 'Pending')
                    ->orderBy('created_at')
                    ->get();

                if ($visits->isEmpty()) {
                    $visits = collect([$visitor]);
                }
            }

            $isWaived = $request->fee_type === 'Waived';

            // Build per-member fee breakdown
            $memberBreakdown = $visits->map(function ($v) use ($isWaived) {
                $fee = $isWaived ? 0 : $this->resolveFee($v);
                return [
                    'visit_id'         => $v->id,
                    'registration_id'  => $v->registration_id,
                    'full_name'        => $v->full_name,
                    'visitor_category' => $v->visitor_category,
                    'fee'              => $fee,
                ];
            })->values()->toArray();

            $totalAmount       = collect($memberBreakdown)->sum('fee');
            $numberOfVisitors  = $visits->count();

            // Receipt number
            $receiptNumber = 'OR-' . Carbon::now()->format('Y') . '-' . str_pad(
                Receipt::whereYear('collected_at', Carbon::now()->year)->count() + 1,
                7, '0', STR_PAD_LEFT
            );

            // Create ONE receipt record linked to the primary visitor
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
                // Cast to array by the model — no manual json_encode needed
                'member_breakdown'   => $memberBreakdown,
            ]);

            // Mark all covered visits as paid/waived
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

    // ─────────────────────────────────────────────────────────────────────────
    // Show receipt
    // Route: GET /adminreceipt/{visitor}
    // ─────────────────────────────────────────────────────────────────────────
    public function showReceipt(VisitorVisit $visitor)
    {
        $visitor->load('receipt', 'registeredBy');

        // member_breakdown is cast as array by the Receipt model automatically
        $memberBreakdown = [];
        if ($visitor->receipt && !empty($visitor->receipt->member_breakdown)) {
            $memberBreakdown = $visitor->receipt->member_breakdown;
        }

        // Fallback: if no breakdown stored, build it from the current visit
        if (empty($memberBreakdown)) {
            $memberBreakdown = [[
                'visit_id'         => $visitor->id,
                'registration_id'  => $visitor->registration_id,
                'full_name'        => $visitor->full_name,
                'visitor_category' => $visitor->visitor_category,
                'fee'              => $visitor->receipt
                    ? (float) $visitor->receipt->amount
                    : 0,
            ]];
        }

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
            'receipt' => $visitor->receipt ? [
                'id'                 => $visitor->receipt->id,
                'receipt_number'     => $visitor->receipt->receipt_number,
                'fee_type'           => $visitor->receipt->fee_type,
                'waiver_reason'      => $visitor->receipt->waiver_reason,
                'number_of_visitors' => $visitor->receipt->number_of_visitors,
                'amount'             => $visitor->receipt->amount,
                'total_amount'       => $visitor->receipt->total_amount,
                'payment_method'     => $visitor->receipt->payment_method,
                'collected_at'       => $visitor->receipt->collected_at->format('M d, Y'),
                'notes'              => $visitor->receipt->notes,        // ← was missing
                'member_breakdown'   => $memberBreakdown,
            ] : null,
            'isGroup'  => count($memberBreakdown) > 1,
        ]);
    }
}