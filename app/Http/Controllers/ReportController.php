<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\Receipt;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ── Shared: parse date range ──────────────────────────────────────────────
    private function parseDates(Request $request): array
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        return [$from, $to];
    }

    // ── Shared: base confirmed visit query ────────────────────────────────────
    // source='staff' = confirmed walk-ins + confirmed pre-reg visitors.
    // Pre-reg pending (not yet arrived) is excluded from all reports.
    private function visitQuery(Carbon $from, Carbon $to)
    {
        return VisitorVisit::whereNull('deleted_at')
                           ->where('source', 'staff')
                           ->whereBetween('arrival_at', [$from, $to]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 1. DEMOGRAPHICS
    //    Columns: snapshot_place_of_origin, snapshot_municipality,
    //             snapshot_province, arrival_at, deleted_at, source
    // ══════════════════════════════════════════════════════════════════════════
    public function analytics(Request $request)
    {
        [$from, $to] = $this->parseDates($request);
        $q = $this->visitQuery($from, $to);

        $total   = (clone $q)->count();
        $local   = (clone $q)->where('snapshot_province', 'Aklan')->count();
        $outside = (clone $q)->where('snapshot_province', '!=', 'Aklan')->count();

        // Top 10 origins grouped by snapshot fields
        $origins = (clone $q)
            ->selectRaw('
                snapshot_place_of_origin as place_of_origin,
                snapshot_municipality    as municipality,
                snapshot_province        as province,
                COUNT(*) as total
            ')
            ->whereNotNull('snapshot_place_of_origin')
            ->where('snapshot_place_of_origin', '!=', '')
            ->groupBy('snapshot_place_of_origin', 'snapshot_municipality', 'snapshot_province')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'place_of_origin' => $r->place_of_origin,
                'municipality'    => $r->municipality,
                'province'        => $r->province,
                'total'           => (int) $r->total,
            ]);

        return Inertia::render('AdminRepOvPage', [
            'tab'          => 'analytics',
            'totalLocal'   => $local,
            'totalForeign' => $outside,
            'total'        => $total,
            'origins'      => $origins,
            'filters'      => $request->only(['date_from', 'date_to']),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 2. TEMPORAL TRENDS
    //    Columns: arrival_at, deleted_at, source
    // ══════════════════════════════════════════════════════════════════════════
    public function temporal(Request $request)
    {
        [$from, $to] = $this->parseDates($request);
        $q    = $this->visitQuery($from, $to);
        $total = (clone $q)->count();
        $days  = max(1, (int) $from->diffInDays($to) + 1);

        // Arrivals by day of week
        $dowLabels = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $rawDow = (clone $q)
            ->selectRaw('(DAYOFWEEK(arrival_at) - 1) as dow, COUNT(*) as cnt')
            ->groupBy('dow')
            ->orderBy('dow')
            ->pluck('cnt', 'dow')
            ->toArray();

        $byDow = collect(range(0, 6))->map(fn($i) => [
            'day'   => $dowLabels[$i],
            'count' => (int) ($rawDow[$i] ?? 0),
        ])->values();

        // Monthly arrivals
        $byMonth = (clone $q)
            ->selectRaw("DATE_FORMAT(arrival_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->map(fn($r) => [
                'month' => Carbon::createFromFormat('Y-m', $r->ym)->format('F Y'),
                'count' => (int) $r->cnt,
            ]);

        // Peak single day
        $peakRow = (clone $q)
            ->selectRaw("DATE(arrival_at) as date_only, COUNT(*) as cnt")
            ->groupBy('date_only')
            ->orderByDesc('cnt')
            ->first();

        // Days exceeding capacity threshold
        $threshold = 50;
        $alertDays = (clone $q)
            ->selectRaw("DATE(arrival_at) as date_only, COUNT(*) as cnt")
            ->groupBy('date_only')
            ->havingRaw('cnt >= ?', [$threshold])
            ->orderByDesc('cnt')
            ->get()
            ->map(fn($r) => [
                'date'  => Carbon::parse($r->date_only)->format('M d, Y'),
                'count' => (int) $r->cnt,
            ]);

        return Inertia::render('AdminRepOvPage', [
            'tab'      => 'temporal',
            'temporal' => [
                'total'         => $total,
                'avgPerDay'     => round($total / $days, 1),
                'peakDay'       => $peakRow
                    ? Carbon::parse($peakRow->date_only)->format('M d, Y') . " ({$peakRow->cnt})"
                    : null,
                'peakAlertDays' => $alertDays->count(),
                'byDow'         => $byDow,
                'byMonth'       => $byMonth,
                'alertDays'     => $alertDays,
            ],
            'filters'  => $request->only(['date_from', 'date_to']),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 3. ECONOMIC TRANSPARENCY
    //    receipts: fee_type, number_of_visitors, total_amount, collected_at
    //    visitor_visits cross-audit: fee_status, source='staff'
    // ══════════════════════════════════════════════════════════════════════════
    public function feeRevenue(Request $request)
    {
        [$from, $to] = $this->parseDates($request);
        $days = max(1, (int) $from->diffInDays($to) + 1);

        // ── Receipts ──────────────────────────────────────────────────────────
        $rq = Receipt::whereBetween('collected_at', [$from, $to]);

        $totalVisitorsReceipted = (int)(clone $rq)->sum('number_of_visitors');
        $charged      = (int)(clone $rq)->where('fee_type', '!=', 'Waived')->sum('number_of_visitors');
        $waived       = (int)(clone $rq)->where('fee_type', 'Waived')->sum('number_of_visitors');
        $totalRevenue = (float)(clone $rq)->where('fee_type', '!=', 'Waived')->sum('total_amount');
        $avgDaily     = round($totalRevenue / $days, 2);

        $remainingDays    = max(1, (int) Carbon::now()->diffInDays(Carbon::now()->endOfYear()));
        $projectedYearEnd = round(($avgDaily * $remainingDays) + $totalRevenue, 2);

        $collectedPct = $totalVisitorsReceipted > 0
            ? round($charged / $totalVisitorsReceipted * 100, 1) : 0;
        $waivedPct = $totalVisitorsReceipted > 0
            ? round($waived  / $totalVisitorsReceipted * 100, 1) : 0;

        // ── Cross-audit from visitor_visits ───────────────────────────────────
        // source='staff' only — confirms these are real arrived visitors
        $vq = $this->visitQuery($from, $to);
        $visitorCollected = (clone $vq)->where('fee_status', 'Collected')->count();
        $visitorWaived    = (clone $vq)->where('fee_status', 'Waived')->count();
        $visitorPending   = (clone $vq)->where('fee_status', 'Pending')->count();

        // Discrepancy: more waived in visitor log than in receipts = red flag
        $waiverDiscrepancy = max(0, $visitorWaived - $waived);

        $breakdown = [
            [
                'category' => 'Standard (Individual)',
                'visitors' => (int)(clone $rq)->where('fee_type', 'Standard')->sum('number_of_visitors'),
                'revenue'  => (float)(clone $rq)->where('fee_type', 'Standard')->sum('total_amount'),
            ],
            [
                'category' => 'Group',
                'visitors' => (int)(clone $rq)->where('fee_type', 'Group')->sum('number_of_visitors'),
                'revenue'  => (float)(clone $rq)->where('fee_type', 'Group')->sum('total_amount'),
            ],
            [
                'category' => 'Waived',
                'visitors' => $waived,
                'revenue'  => 0.00,
            ],
        ];

        return Inertia::render('AdminRepOvPage', [
            'tab'     => 'fee_revenue',
            'revenue' => [
                'totalRevenue'          => $totalRevenue,
                'avgDailyRevenue'       => $avgDaily,
                'totalVisitorsCharged'  => $charged,
                'receiptWaivedVisitors' => $waived,
                'collectedPct'          => $collectedPct,
                'waivedPct'             => $waivedPct,
                'projectedYearEnd'      => $projectedYearEnd,
                'breakdown'             => $breakdown,
                'auditCollected'        => $visitorCollected,
                'auditWaived'           => $visitorWaived,
                'auditPending'          => $visitorPending,
                'waiverDiscrepancy'     => $waiverDiscrepancy,
            ],
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 4. BEHAVIORAL INSIGHTS
    //    Columns: purpose, purpose_other, duration_of_stay, source
    // ══════════════════════════════════════════════════════════════════════════
    public function behavioral(Request $request)
    {
        [$from, $to] = $this->parseDates($request);
        $q     = $this->visitQuery($from, $to);
        $total = (clone $q)->count();

        // Purpose distribution — merge purpose_other into display label
        $byPurpose = (clone $q)
            ->selectRaw('purpose, purpose_other, COUNT(*) as cnt')
            ->groupBy('purpose', 'purpose_other')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn($r) => [
                'purpose' => $r->purpose === 'Other' && $r->purpose_other
                                ? "Other: {$r->purpose_other}"
                                : $r->purpose,
                'count'   => (int) $r->cnt,
            ]);

        // Duration of stay distribution
        $byDuration = (clone $q)
            ->selectRaw('duration_of_stay as duration, COUNT(*) as cnt')
            ->whereNotNull('duration_of_stay')
            ->groupBy('duration_of_stay')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn($r) => [
                'duration' => $r->duration,
                'count'    => (int) $r->cnt,
            ]);

        // Short-stay detection
        $shortStay = (clone $q)
            ->where(function ($sq) {
                $sq->where('duration_of_stay', 'like', '%hour%')
                   ->orWhere('duration_of_stay', 'like', '%hrs%')
                   ->orWhere('duration_of_stay', 'like', '%half%')
                   ->orWhere('duration_of_stay', 'like', '%<1%');
            })
            ->count();
        $shortStayPct = $total > 0 ? round($shortStay / $total * 100, 1) : 0;

        // Research flag
        $researchCount = (clone $q)->where('purpose', 'Research')->count();
        $researchPct   = $total > 0 ? round($researchCount / $total * 100, 1) : 0;

        return Inertia::render('AdminRepOvPage', [
            'tab'      => 'behavior',
            'behavior' => [
                'totalVisitors' => $total,
                'byPurpose'     => $byPurpose,
                'byDuration'    => $byDuration,
                'shortStayPct'  => $shortStayPct,
                'researchPct'   => $researchPct,
                'highResearch'  => $researchPct >= 20,
            ],
            'filters'  => $request->only(['date_from', 'date_to']),
        ]);
    }
}