<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\Receipt;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    // ── Show payment form ─────────────────────────────────────────────────────
    // Route: GET /adminpay/{visitor} — note: {visitor} is actually a VisitorVisit
    public function showPayment(VisitorVisit $visitor)
    {
        return Inertia::render('AdminPayPage', [
            'visitor' => [
                'id'              => $visitor->id,
                'registration_id' => $visitor->registration_id,
                'full_name'       => $visitor->full_name,
                'place_of_origin' => $visitor->place_of_origin,
                'purpose'         => $visitor->purpose,
                'duration'        => $visitor->duration_of_stay,
            ],
        ]);
    }

    // ── Store receipt / collect payment ───────────────────────────────────────
    public function store(Request $request, VisitorVisit $visitor)
    {
        $request->validate([
            'fee_type'           => 'required|in:Standard,Group,Waived',
            'number_of_visitors' => 'required|integer|min:1',
            'payment_method'     => 'required|in:Cash',
            'notes'              => 'nullable|string|max:500',
            // Phase 4 Step 8: waiver_reason required when fee_type = Waived
            'waiver_reason'      => 'required_if:fee_type,Waived|nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $amount = $request->fee_type === 'Waived' ? 0 : 100.00;
            $total  = $amount * $request->number_of_visitors;

            // Generate sequential receipt number for the year
            $receiptNumber = 'OR-' . Carbon::now()->format('Y') . '-' . str_pad(
                Receipt::whereYear('collected_at', Carbon::now()->year)->count() + 1,
                7, '0', STR_PAD_LEFT
            );

            $receipt = Receipt::create([
                'receipt_number'     => $receiptNumber,
                'visit_id'           => $visitor->id,   // ← visit_id not visitor_id
                'amount'             => $amount,
                'currency'           => 'PHP',
                'fee_type'           => $request->fee_type,
                'number_of_visitors' => $request->number_of_visitors,
                'total_amount'       => $total,
                'waiver_reason'      => $request->waiver_reason,
                'payment_method'     => $request->payment_method,
                'collected_by'       => Auth::id(),
                'collected_at'       => now(),
                'notes'              => $request->notes,
            ]);

            // Update the visit's fee_status
            $visitor->update([
                'fee_status'    => $request->fee_type === 'Waived' ? 'Waived' : 'Collected',
                'waiver_reason' => $request->waiver_reason,
            ]);

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
    public function showReceipt(VisitorVisit $visitor)
    {
        $visitor->load('receipt', 'registeredBy');

        return Inertia::render('AdminRecpPage', [
            'visitor' => [
                'id'              => $visitor->id,
                'registration_id' => $visitor->registration_id,
                'full_name'       => $visitor->full_name,
                'place_of_origin' => $visitor->place_of_origin,
                'municipality'    => $visitor->snapshot_municipality,
                'province'        => $visitor->snapshot_province,
                'purpose'         => $visitor->purpose,
                'duration'        => $visitor->duration_of_stay,
                'contact_number'  => $visitor->snapshot_contact_number ?? 'N/A',
                'fee_status'      => $visitor->fee_status,
                'arrival_at'      => $visitor->arrival_at->format('M d, Y'),
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
            ] : null,
        ]);
    }
}