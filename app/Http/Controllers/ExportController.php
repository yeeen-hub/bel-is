<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ReportExport;
use App\Models\AuditLog;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExportController extends Controller
{
    // ── Decode JSON payload from ExportModal.vue ──────────────────────────────
    private function buildPayload(Request $request): array
    {
        $raw = json_decode($request->input('payload', '{}'), true) ?? [];

        return [
            'title'        => $raw['title']       ?? 'Report',
            'subtitle'     => $raw['subtitle']     ?? '',
            'notes'        => $raw['notes']        ?? '',
            'scope_label'  => $raw['scope_label']  ?? '',
            'columns'      => $raw['columns']      ?? [],
            'rows'         => $raw['rows']         ?? [],
            'generated_by' => Auth::user()->name   ?? 'Staff',
            'generated_at' => Carbon::now()->format('F d, Y h:i A'),
            'barangay'     => 'Barangay Bel-is, Buruanga, Aklan',
        ];
    }

    // ── Shared audit log writer ───────────────────────────────────────────────
    private function logExport(Request $request, string $reportType, string $format, array $data): void
    {
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'exported',
            'module'      => 'reports',
            'target_type' => 'Report',
            'target_id'   => null,
            'new_values'  => json_encode([
                'report_type' => $reportType,
                'format'      => $format,
                'title'       => $data['title'],
                'scope'       => $data['scope_label'],
                'row_count'   => count($data['rows']),
                'columns'     => array_column($data['columns'], 'label'),
            ]),
            'ip_address'  => $request->ip(),
        ]);
    }

    // ── Analytics ─────────────────────────────────────────────────────────────
    public function analyticsPdf(Request $request)
    {
        $data = $this->buildPayload($request);
        $this->logExport($request, 'analytics', 'pdf', $data);
        $pdf  = Pdf::loadView('exports.report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('analytics-report-' . Carbon::now()->format('Ymd') . '.pdf');
    }

    public function analyticsExcel(Request $request)
    {
        $data = $this->buildPayload($request);
        $this->logExport($request, 'analytics', 'excel', $data);
        return Excel::download(
            new ReportExport($data),
            'analytics-report-' . Carbon::now()->format('Ymd') . '.xlsx'
        );
    }

    // ── Demographics ──────────────────────────────────────────────────────────
    public function demographicsPdf(Request $request)
    {
        $data = $this->buildPayload($request);
        $this->logExport($request, 'demographics', 'pdf', $data);
        $pdf  = Pdf::loadView('exports.report', $data)->setPaper('a4', 'portrait');
        return $pdf->download('demographics-report-' . Carbon::now()->format('Ymd') . '.pdf');
    }

    public function demographicsExcel(Request $request)
    {
        $data = $this->buildPayload($request);
        $this->logExport($request, 'demographics', 'excel', $data);
        return Excel::download(
            new ReportExport($data),
            'demographics-report-' . Carbon::now()->format('Ymd') . '.xlsx'
        );
    }

    // ── Fee Revenue ───────────────────────────────────────────────────────────
    public function feeRevenuePdf(Request $request)
    {
        $data = $this->buildPayload($request);
        $this->logExport($request, 'fee-revenue', 'pdf', $data);
        $pdf  = Pdf::loadView('exports.report', $data)->setPaper('a4', 'portrait');
        return $pdf->download('fee-revenue-report-' . Carbon::now()->format('Ymd') . '.pdf');
    }

    public function feeRevenueExcel(Request $request)
    {
        $data = $this->buildPayload($request);
        $this->logExport($request, 'fee-revenue', 'excel', $data);
        return Excel::download(
            new ReportExport($data),
            'fee-revenue-report-' . Carbon::now()->format('Ymd') . '.xlsx'
        );
    }
}