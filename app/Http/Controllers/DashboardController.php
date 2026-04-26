<?php

namespace App\Http\Controllers;

use App\Models\VisitorVisit;
use App\Models\Receipt;
use App\Models\TourismContent;
use App\Models\BarangayAttraction;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd   = Carbon::today()->endOfDay();
        $monthStart = Carbon::now()->startOfMonth()->startOfDay();
        $yearStart  = Carbon::now()->startOfYear()->startOfDay();

        // ── BASE SCOPE ────────────────────────────────────────────────────────
        // source = 'staff'  → confirmed walk-ins + confirmed pre-reg visitors
        // source = 'pre_registration' + Pending → excluded everywhere
        $base = VisitorVisit::whereNull('deleted_at')
                             ->where('source', 'staff');

        // ── CARD 1: Total Tourists ────────────────────────────────────────────
        $totalTourists = (clone $base)->count();

        $totalTouristsToday = (clone $base)
            ->whereBetween('arrival_at', [$todayStart, $todayEnd])
            ->count();

        // ── Visitors This Month ───────────────────────────────────────────────
        $visitorsThisMonth = (clone $base)
            ->where('arrival_at', '>=', $monthStart)
            ->count();

        // ── Pending Fees (walk-in only) ───────────────────────────────────────
        $pendingFees = (clone $base)
            ->where('fee_status', 'Pending')
            ->count();

        // ── Pending Pre-Registrations ─────────────────────────────────────────
        $pendingPreReg = VisitorVisit::whereNull('deleted_at')
            ->where('source', 'pre_registration')
            ->where('fee_status', 'Pending')
            ->count();

        // ── Revenue ───────────────────────────────────────────────────────────
        $receiptsExist = Receipt::count() > 0;

        if ($receiptsExist) {
            $revenueToday = (float) Receipt::whereBetween('collected_at', [$todayStart, $todayEnd])
                ->where('fee_type', '!=', 'Waived')
                ->sum('total_amount');

            $revenueThisMonth = (float) Receipt::where('collected_at', '>=', $monthStart)
                ->where('fee_type', '!=', 'Waived')
                ->sum('total_amount');

            $revenueThisYear = (float) Receipt::where('collected_at', '>=', $yearStart)
                ->where('fee_type', '!=', 'Waived')
                ->sum('total_amount');
        } else {
            $feePerVisitor = 100.00;

            $revenueToday = (float) (clone $base)
                ->where('fee_status', 'Collected')
                ->whereBetween('arrival_at', [$todayStart, $todayEnd])
                ->count() * $feePerVisitor;

            $revenueThisMonth = (float) (clone $base)
                ->where('fee_status', 'Collected')
                ->where('arrival_at', '>=', $monthStart)
                ->count() * $feePerVisitor;

            $revenueThisYear = (float) (clone $base)
                ->where('fee_status', 'Collected')
                ->where('arrival_at', '>=', $yearStart)
                ->count() * $feePerVisitor;
        }

            // ── Barangay Attraction Counts ────────────────────────────────────────
        // Source: barangay_attractions table (is_active = true)
        // This is where actual local spots are managed by staff.
        //
        // Card 2: All Tourist Spots — total active attractions of any type.
        //   Grand total is what staff quote when asked "how many spots do we have?"
        //
        // Card 3: Resorts — most visited category, highest fee relevance.
        //   Resorts = overnight stays → higher economic impact per visitor.
        //
        // Card 4: Beaches — primary draw for Bel-is tourism.
        //   Separate count useful for LGU coastal resource reporting.
        //
        // All counts use is_active=true only — deactivated spots don't count.

        $totalSpots   = BarangayAttraction::where('is_active', true)->count();
        $totalResorts = BarangayAttraction::where('is_active', true)->where('type', 'Resort')->count();
        $totalBeaches = BarangayAttraction::where('is_active', true)->where('type', 'Beach')->count();

        // ── Bar Chart: Visitors Per Day (last 7 days) ─────────────────────────
        $visitorsPerDay = collect(range(6, 0))->map(function ($i) use ($base) {
            $date  = Carbon::today()->subDays($i);
            $start = (clone $date)->startOfDay();
            $end   = (clone $date)->endOfDay();
            return [
                'day'   => $date->format('D'),
                'date'  => $date->format('M d'),
                'count' => (clone $base)->whereBetween('arrival_at', [$start, $end])->count(),
            ];
        })->values()->all();

        // ── Bar Chart: Visitors Per Month (last 6 months) ─────────────────────
        $visitorsPerMonth = collect(range(5, 0))->map(function ($i) use ($base) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            return [
                'month' => $month->format('M Y'),
                'count' => (clone $base)
                    ->whereYear('arrival_at', $month->year)
                    ->whereMonth('arrival_at', $month->month)
                    ->count(),
            ];
        })->values()->all();

        // ── Purpose Breakdown ─────────────────────────────────────────────────
        $purposeBreakdown = (clone $base)
            ->selectRaw('purpose, COUNT(*) as count')
            ->groupBy('purpose')
            ->orderByDesc('count')
            ->get()
            ->map(fn($v) => [
                'purpose' => $v->purpose,
                'count'   => (int) $v->count,
            ]);

        // ── Top 5 Places of Origin ────────────────────────────────────────────
        $topOrigins = (clone $base)
            ->selectRaw('snapshot_place_of_origin as origin, COUNT(*) as count')
            ->whereNotNull('snapshot_place_of_origin')
            ->where('snapshot_place_of_origin', '!=', '')
            ->groupBy('snapshot_place_of_origin')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($v) => [
                'origin' => $v->origin,
                'count'  => (int) $v->count,
            ]);

        // ── Recent Visits (latest 10) ─────────────────────────────────────────
        $recentVisitors = (clone $base)
            ->orderByDesc('arrival_at')
            ->limit(10)
            ->get()
            ->map(fn($v) => [
                'id'              => $v->id,
                'name'            => trim("{$v->snapshot_first_name} {$v->snapshot_last_name}"),
                'place_of_origin' => $v->snapshot_place_of_origin
                    ?? "{$v->snapshot_municipality}, {$v->snapshot_province}",
                'purpose'         => $v->purpose === 'Other' && $v->purpose_other
                                        ? "Other: {$v->purpose_other}"
                                        : $v->purpose,
                'duration'        => $v->duration_of_stay,
                'fee_status'      => $v->fee_status,
                'arrival_at'      => Carbon::parse($v->arrival_at)->format('M d, Y'),
            ]);

        return Inertia::render('AdmindbPage', [
            'stats' => [
                'total_tourists'          => $totalTourists,
                'total_tourists_today'    => $totalTouristsToday,
                'visitors_this_month'     => $visitorsThisMonth,
                'pending_fees'            => $pendingFees,
                'pending_pre_reg'         => $pendingPreReg,
                'revenue_today'           => $revenueToday,
                'revenue_this_month'      => $revenueThisMonth,
                'revenue_this_year'       => $revenueThisYear,
                'total_spots'   => $totalSpots,    // Card 2: all active tourist spots
                'total_resorts' => $totalResorts,  // Card 3: active resorts
                'total_beaches' => $totalBeaches,  // Card 4: active beaches
                'revenue_is_estimated'    => !$receiptsExist,
            ],
            'visitorsPerDay'   => $visitorsPerDay,
            'visitorsPerMonth' => $visitorsPerMonth,
            'purposeBreakdown' => $purposeBreakdown,
            'topOrigins'       => $topOrigins,
            'recentVisitors'   => $recentVisitors,
        ]);
    }
}