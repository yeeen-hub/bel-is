<?php

namespace App\Http\Controllers;

use App\Models\VisitorVisit;
use App\Models\Receipt;
use App\Models\TourismContent;
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
        // ALL dashboard queries use this single base.
        // source = 'staff'  → confirmed walk-ins + confirmed pre-reg visitors
        // source = 'pre_registration' + Pending → excluded everywhere
        // Walk-in Pending (source='staff', fee_status='Pending') → included
        $base = VisitorVisit::whereNull('deleted_at')
                             ->where('source', 'staff');

        // ── CARD 1: Total Tourists ────────────────────────────────────────────
        $totalTourists = (clone $base)->count();

        $totalTouristsToday = (clone $base)
            ->whereBetween('arrival_at', [$todayStart, $todayEnd])
            ->count();

        // ── CARD 2: Visitors This Month ───────────────────────────────────────
        $visitorsThisMonth = (clone $base)
            ->where('arrival_at', '>=', $monthStart)
            ->count();

        // ── CARD 3: Pending Fees (walk-in only) ───────────────────────────────
        // Only staff-sourced visits awaiting payment.
        // Pre-reg pending is intentionally excluded — they haven't arrived yet.
        $pendingFees = (clone $base)
            ->where('fee_status', 'Pending')
            ->count();

        // ── INFO: Pending Pre-Registrations ───────────────────────────────────
        // Visitors who filled the public form but haven't been confirmed yet.
        // Shown as info only — not counted in tourist totals.
        $pendingPreReg = VisitorVisit::whereNull('deleted_at')
            ->where('source', 'pre_registration')
            ->where('fee_status', 'Pending')
            ->count();

        // ── CARDS 4-6: Revenue ────────────────────────────────────────────────
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

        // ── Tourism Content Counts ────────────────────────────────────────────
        $totalAttractions = TourismContent::where('type', 'attraction')->where('is_published', true)->count();
        $totalPackages    = TourismContent::where('type', 'package')->where('is_published', true)->count();
        $totalCircuits    = TourismContent::where('type', 'circuit')->where('is_published', true)->count();

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
        // Only staff-confirmed visits appear here.
        // Walk-in Pending shows (still needs fee collection).
        // Pre-reg Pending is hidden until staff scans code at checkpoint.
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
                'total_tourists'       => $totalTourists,
                'total_tourists_today' => $totalTouristsToday,
                'visitors_this_month'  => $visitorsThisMonth,
                'pending_fees'         => $pendingFees,
                'pending_pre_reg'      => $pendingPreReg,
                'revenue_today'        => $revenueToday,
                'revenue_this_month'   => $revenueThisMonth,
                'revenue_this_year'    => $revenueThisYear,
                'total_attractions'    => $totalAttractions,
                'total_packages'       => $totalPackages,
                'total_circuits'       => $totalCircuits,
                'revenue_is_estimated' => !$receiptsExist,
            ],
            'visitorsPerDay'   => $visitorsPerDay,
            'visitorsPerMonth' => $visitorsPerMonth,
            'purposeBreakdown' => $purposeBreakdown,
            'topOrigins'       => $topOrigins,
            'recentVisitors'   => $recentVisitors,
        ]);
    }
}