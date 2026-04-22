<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\VisitorDestination;
use App\Models\BarangayAttraction;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ── Analytics — AdminRepOvPage ────────────────────────────────────────────
    // GET /reports/analytics
    // Individual visitor rows: Name, Origin, Purpose, Duration, Destinations.
    // ─────────────────────────────────────────────────────────────────────────
    public function analytics(Request $request)
    {
        $query = VisitorVisit::query()
            ->select(
                'id',
                'snapshot_first_name',
                'snapshot_last_name',
                'snapshot_place_of_origin',
                'purpose',
                'duration_of_stay'
            )
            ->orderByDesc('arrival_at');

        if ($request->filled('date_from')) {
            $query->whereDate('arrival_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('arrival_at', '<=', $request->date_to);
        }
        if ($request->filled('area')) {
            $query->where('snapshot_place_of_origin', 'like', "%{$request->area}%");
        }

        $visits = $query->get();

        // Bulk-load all destinations in TWO queries (avoids N+1)
        $visitIds = $visits->pluck('id');

        // Named attractions grouped by visit_id
        $named = DB::table('visitor_destinations')
            ->join('barangay_attractions', 'visitor_destinations.attraction_id', '=', 'barangay_attractions.id')
            ->whereIn('visitor_destinations.visit_id', $visitIds)
            ->whereNotNull('visitor_destinations.attraction_id')
            ->select('visitor_destinations.visit_id', 'barangay_attractions.name')
            ->get()
            ->groupBy('visit_id');

        // "Other" free-text destinations grouped by visit_id
        $others = DB::table('visitor_destinations')
            ->whereIn('visit_id', $visitIds)
            ->whereNull('attraction_id')
            ->whereNotNull('other_destination')
            ->where('other_destination', '!=', '')
            ->select('visit_id', 'other_destination')
            ->get()
            ->groupBy('visit_id');

        $rows = $visits->map(function ($v) use ($named, $others) {
            $parts = collect();

            if (isset($named[$v->id])) {
                $parts = $parts->merge(
                    collect($named[$v->id])->pluck('name')
                );
            }
            if (isset($others[$v->id])) {
                $parts = $parts->merge(
                    collect($others[$v->id])->pluck('other_destination')
                );
            }

            $firstName = trim($v->snapshot_first_name ?? '');
            $lastName  = trim($v->snapshot_last_name  ?? '');
            $fullName  = trim("$firstName $lastName") ?: '—';

            return [
                'full_name'        => $fullName,
                'place_of_origin'  => $v->snapshot_place_of_origin ?? '—',
                'purpose'          => $v->purpose          ?? '—',
                'duration_of_stay' => $v->duration_of_stay ?? '—',
                'destinations'     => $parts->isNotEmpty() ? $parts->implode(', ') : '—',
            ];
        });

        return Inertia::render('AdminRepOvPage', [
            'rows'    => $rows,
            'filters' => [
                'date_from' => $request->date_from ?? '',
                'date_to'   => $request->date_to   ?? '',
                'area'      => $request->area       ?? '',
            ],
        ]);
    }

    // ── Demographics ──────────────────────────────────────────────────────────
    // GET /reports/demographics
    // Grouped by origin: Origin, Total Tourist count.
    // ─────────────────────────────────────────────────────────────────────────
    public function demographics(Request $request)
    {
        $query = VisitorVisit::query()
            ->select(
                'snapshot_place_of_origin as place_of_origin',
                DB::raw('COUNT(*) as total_tourists')
            )
            ->groupBy('snapshot_place_of_origin')
            ->orderByDesc(DB::raw('COUNT(*)'));

        if ($request->filled('date_from')) {
            $query->whereDate('arrival_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('arrival_at', '<=', $request->date_to);
        }
        if ($request->filled('area')) {
            $query->where('snapshot_place_of_origin', 'like', "%{$request->area}%");
        }

        $rows = $query->get()->map(fn($r) => [
            'place_of_origin' => $r->place_of_origin ?? '—',
            'total_tourists'  => $r->total_tourists,
        ]);

        return Inertia::render('AdminRepDemoPage', [
            'rows'    => $rows,
            'filters' => [
                'date_from' => $request->date_from ?? '',
                'date_to'   => $request->date_to   ?? '',
                'area'      => $request->area       ?? '',
            ],
        ]);
    }

    // ── Fee Revenue ───────────────────────────────────────────────────────────
    // GET /reports/fee-revenue
    // ─────────────────────────────────────────────────────────────────────────
    public function feeRevenue(Request $request)
    {
        $query = VisitorVisit::query()
            ->join('receipts', 'visitor_visits.id', '=', 'receipts.visit_id')
            ->select(
                'visitor_visits.visitor_category as visit_category',
                DB::raw("CONCAT(visitor_visits.snapshot_first_name, ' ', visitor_visits.snapshot_last_name) as full_name"),
                'receipts.total_amount as revenue',
                'receipts.fee_type',
                'receipts.collected_at'
            );

        if ($request->filled('date_from')) {
            $query->whereDate('receipts.collected_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('receipts.collected_at', '<=', $request->date_to);
        }
        if ($request->filled('area')) {
            $query->where('visitor_visits.snapshot_place_of_origin', 'like', "%{$request->area}%");
        }

        $rows = $query->orderByDesc('receipts.collected_at')->get();

        $totalRevenue = $rows->where('fee_type', '!=', 'Waived')->sum('revenue');
        $days         = $rows->groupBy(fn($r) => \Carbon\Carbon::parse($r->collected_at)->toDateString())->count();
        $avgDaily     = $days > 0 ? round($totalRevenue / $days, 2) : 0;

        return Inertia::render('AdminRepFeePage', [
            'rows'         => $rows->map(fn($r) => [
                'visit_category' => $r->visit_category ?? '—',
                'full_name'      => trim($r->full_name) ?: '—',
                'revenue'        => $r->fee_type === 'Waived' ? 'Waived' : number_format((float) $r->revenue, 2),
            ]),
            'totalRevenue' => number_format($totalRevenue, 2),
            'avgDaily'     => number_format($avgDaily, 2),
            'filters'      => [
                'date_from' => $request->date_from ?? '',
                'date_to'   => $request->date_to   ?? '',
                'area'      => $request->area       ?? '',
            ],
        ]);
    }

    // ── Temporal (stub — add logic when page is built) ────────────────────────
    public function temporal(Request $request)
    {
        return Inertia::render('AdminRepTemporalPage', []);
    }

    // ── Behavioral (stub — add logic when page is built) ─────────────────────
    public function behavioral(Request $request)
    {
        return Inertia::render('AdminRepBehavioralPage', []);
    }
}