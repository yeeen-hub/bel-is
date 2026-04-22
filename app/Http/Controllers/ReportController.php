<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorVisit;
use App\Models\VisitorDestination;
use App\Models\BarangayAttraction;
use App\Models\Sitio;
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
        // Area = sitio name → filter visits whose selected destinations belong to that sitio
        if ($request->filled('area')) {
            $query->whereHas('destinations', function ($q) use ($request) {
                $q->whereHas('attraction', function ($q2) use ($request) {
                    $q2->whereHas('sitio', function ($q3) use ($request) {
                        $q3->where('name', $request->area);
                    });
                });
            });
        }
        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereRaw("CONCAT(IFNULL(snapshot_first_name,''), ' ', IFNULL(snapshot_last_name,'')) LIKE ?", ["%{$s}%"])
                  ->orWhere('snapshot_place_of_origin', 'like', "%{$s}%");
            });
        }
        // Attraction filter — only visits that include this attraction
        if ($request->filled('attraction_id')) {
            $query->whereHas('destinations', fn($q) =>
                $q->where('attraction_id', $request->attraction_id)
            );
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
            'sitios'  => Sitio::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'attractions' => BarangayAttraction::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search'        => $request->search        ?? '',
                'purpose'       => $request->purpose       ?? '',
                'area'          => $request->area          ?? '',
                'attraction_id' => $request->attraction_id ?? '',
                'date_from'     => $request->date_from     ?? '',
                'date_to'       => $request->date_to       ?? '',
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
                'snapshot_place_of_origin',
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
        if ($request->filled('search')) {
            $query->where('snapshot_place_of_origin', 'like', "%{$request->search}%");
        }
        // Area = sitio name → filter visits whose destinations belong to that sitio
        if ($request->filled('area')) {
            $query->whereHas('destinations', function ($q) use ($request) {
                $q->whereHas('attraction', function ($q2) use ($request) {
                    $q2->whereHas('sitio', function ($q3) use ($request) {
                        $q3->where('name', $request->area);
                    });
                });
            });
        }

        $rows = $query->get()->map(fn($r) => [
            'place_of_origin' => $r->snapshot_place_of_origin ?? '—',
            'total_tourists'  => (int) $r->total_tourists,
        ])->values();

        return Inertia::render('AdminRepDemoPage', [
            'rows'   => $rows,
            'sitios' => Sitio::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search'    => $request->search    ?? '',
                'area'      => $request->area      ?? '',
                'date_from' => $request->date_from ?? '',
                'date_to'   => $request->date_to   ?? '',
            ],
        ]);
    }

    // ── Fee Revenue ───────────────────────────────────────────────────────────
    // GET /reports/fee-revenue
    // ─────────────────────────────────────────────────────────────────────────
    public function feeRevenue(Request $request)
    {
        $feeTypeFilter = $request->fee_type ?? '';

        // ── No Show — these visits have no receipt so we query visitor_visits
        // directly (no join with receipts) and return them with '—' revenue. ──
        if ($feeTypeFilter === 'No Show') {
            $noShowQuery = DB::table('visitor_visits')
                ->select(
                    DB::raw("TRIM(CONCAT(IFNULL(snapshot_first_name,''), ' ', IFNULL(snapshot_last_name,''))) as full_name"),
                    'visitor_category as visit_category',
                    'snapshot_place_of_origin as place_of_origin',
                    'arrival_at as collected_at'
                )
                ->where('fee_status', 'No Show')
                ->orderByDesc('arrival_at');

            if ($request->filled('date_from')) {
                $noShowQuery->whereDate('arrival_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $noShowQuery->whereDate('arrival_at', '<=', $request->date_to);
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $noShowQuery->whereRaw("TRIM(CONCAT(IFNULL(snapshot_first_name,''), ' ', IFNULL(snapshot_last_name,''))) LIKE ?", ["%{$s}%"]);
            }
            if ($request->filled('category')) {
                $noShowQuery->where('visitor_category', $request->category);
            }
            if ($request->filled('area')) {
                $visitIdsInSitio = DB::table('visitor_destinations')
                    ->join('barangay_attractions', 'visitor_destinations.attraction_id', '=', 'barangay_attractions.id')
                    ->join('sitios', 'barangay_attractions.sitio_id', '=', 'sitios.id')
                    ->where('sitios.name', $request->area)
                    ->pluck('visitor_destinations.visit_id');
                $noShowQuery->whereIn('id', $visitIdsInSitio);
            }

            $rows = $noShowQuery->get();

            return Inertia::render('AdminRepFeePage', [
                'rows' => $rows->map(fn($r) => [
                    'visit_category' => $r->visit_category ?: '—',
                    'full_name'      => trim($r->full_name) ?: '—',
                    'revenue'        => 'No Show',
                ]),
                'totalRevenue' => '0.00',
                'avgDaily'     => '0.00',
                'sitios'       => Sitio::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'filters'      => [
                    'search'    => $request->search    ?? '',
                    'category'  => $request->category  ?? '',
                    'fee_type'  => $feeTypeFilter,
                    'area'      => $request->area       ?? '',
                    'date_from' => $request->date_from  ?? '',
                    'date_to'   => $request->date_to    ?? '',
                ],
            ]);
        }

        // ── Standard / Waived / All — join with receipts as before ───────────
        $query = DB::table('visitor_visits')
            ->join('receipts', 'visitor_visits.id', '=', 'receipts.visit_id')
            ->select(
                DB::raw("TRIM(CONCAT(IFNULL(visitor_visits.snapshot_first_name,''), ' ', IFNULL(visitor_visits.snapshot_last_name,''))) as full_name"),
                'visitor_visits.visitor_category as visit_category',
                'visitor_visits.snapshot_place_of_origin as place_of_origin',
                'receipts.total_amount as revenue',
                'receipts.fee_type',
                'receipts.collected_at'
            )
            ->orderByDesc('receipts.collected_at');

        if ($request->filled('date_from')) {
            $query->whereDate('receipts.collected_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('receipts.collected_at', '<=', $request->date_to);
        }
        if ($request->filled('area')) {
            $visitIdsInSitio = DB::table('visitor_destinations')
                ->join('barangay_attractions', 'visitor_destinations.attraction_id', '=', 'barangay_attractions.id')
                ->join('sitios', 'barangay_attractions.sitio_id', '=', 'sitios.id')
                ->where('sitios.name', $request->area)
                ->pluck('visitor_destinations.visit_id');
            $query->whereIn('visitor_visits.id', $visitIdsInSitio);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereRaw("TRIM(CONCAT(IFNULL(visitor_visits.snapshot_first_name,''), ' ', IFNULL(visitor_visits.snapshot_last_name,''))) LIKE ?", ["%{$s}%"]);
        }
        if ($request->filled('category')) {
            $query->where('visitor_visits.visitor_category', $request->category);
        }
        if ($feeTypeFilter) {
            $query->where('receipts.fee_type', $feeTypeFilter);
        }

        $rows = $query->get();

        $totalRevenue = $rows->where('fee_type', '!=', 'Waived')->sum('revenue');
        $days         = $rows->groupBy(fn($r) => Carbon::parse($r->collected_at)->toDateString())->count();
        $avgDaily     = $days > 0 ? round($totalRevenue / $days, 2) : 0;

        return Inertia::render('AdminRepFeePage', [
            'rows' => $rows->map(fn($r) => [
                'visit_category' => $r->visit_category ?: '—',
                'full_name'      => trim($r->full_name) ?: '—',
                'revenue'        => $r->fee_type === 'Waived'
                    ? 'Waived'
                    : number_format((float) $r->revenue, 2),
            ]),
            'totalRevenue' => number_format($totalRevenue, 2),
            'avgDaily'     => number_format($avgDaily, 2),
            'sitios'       => Sitio::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters'      => [
                'search'    => $request->search    ?? '',
                'category'  => $request->category  ?? '',
                'fee_type'  => $feeTypeFilter,
                'area'      => $request->area       ?? '',
                'date_from' => $request->date_from  ?? '',
                'date_to'   => $request->date_to    ?? '',
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