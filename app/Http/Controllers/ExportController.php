<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    // ── Shared: decode the JSON payload POSTed from ExportModal.vue ───────────
    // The modal submits all data as a single JSON string in `payload`
    // to avoid URL length limits when rows are large.
    // ─────────────────────────────────────────────────────────────────────────
    private function buildPayload(Request $request): array
    {
        $raw = json_decode($request->input('payload', '{}'), true) ?? [];

        return [
            'title'        => $raw['title']       ?? 'Report',
            'subtitle'     => $raw['subtitle']     ?? '',
            'notes'        => $raw['notes']        ?? '',
            'scope_label'  => $raw['scope_label']  ?? '',
            'columns'      => $raw['columns']      ?? [],   // [['key'=>'full_name','label'=>'Name'],...]
            'rows'         => $raw['rows']         ?? [],   // already scoped by Vue
            'generated_by' => auth()->user()->name  ?? 'Staff',
            'generated_at' => Carbon::now()->format('F d, Y h:i A'),
            'barangay'     => 'Barangay Bel-is, Buruanga, Aklan',
        ];
    }

    // ── Analytics ─────────────────────────────────────────────────────────────
    public function analyticsPdf(Request $request)
    {
        $data = $this->buildPayload($request);
        $pdf  = Pdf::loadView('exports.report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('analytics-report-' . Carbon::now()->format('Ymd') . '.pdf');
    }

    public function analyticsExcel(Request $request)
    {
        $data = $this->buildPayload($request);
        return Excel::download(
            new ReportExport($data),
            'analytics-report-' . Carbon::now()->format('Ymd') . '.xlsx'
        );
    }

    // ── Demographics ──────────────────────────────────────────────────────────
    public function demographicsPdf(Request $request)
    {
        $data = $this->buildPayload($request);
        $pdf  = Pdf::loadView('exports.report', $data)->setPaper('a4', 'portrait');
        return $pdf->download('demographics-report-' . Carbon::now()->format('Ymd') . '.pdf');
    }

    public function demographicsExcel(Request $request)
    {
        $data = $this->buildPayload($request);
        return Excel::download(
            new ReportExport($data),
            'demographics-report-' . Carbon::now()->format('Ymd') . '.xlsx'
        );
    }

    // ── Fee Revenue ───────────────────────────────────────────────────────────
    public function feeRevenuePdf(Request $request)
    {
        $data = $this->buildPayload($request);
        $pdf  = Pdf::loadView('exports.report', $data)->setPaper('a4', 'portrait');
        return $pdf->download('fee-revenue-report-' . Carbon::now()->format('Ymd') . '.pdf');
    }

    public function feeRevenueExcel(Request $request)
    {
        $data = $this->buildPayload($request);
        return Excel::download(
            new ReportExport($data),
            'fee-revenue-report-' . Carbon::now()->format('Ymd') . '.xlsx'
        );
    }
}