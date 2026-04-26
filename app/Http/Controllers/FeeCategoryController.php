<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FeeCategory;
use App\Models\Sitio;
use App\Models\BarangayAttraction;
use App\Models\UnrecognizedAttraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FeeCategoryController extends Controller
{
    // ── System Settings Index ─────────────────────────────────────────────────
    public function index()
    {
        $unreviewedCount = UnrecognizedAttraction::where('is_reviewed', false)->count();

        $unreviewed = UnrecognizedAttraction::where('is_reviewed', false)
            ->with('visit:id,registration_id,snapshot_first_name,snapshot_last_name,arrival_at')
            ->latest()
            ->get()
            ->map(fn($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'visit_id'        => $u->visit_id,
                'registration_id' => $u->visit?->registration_id,
                'visitor_name'    => trim(($u->visit?->snapshot_first_name ?? '') . ' ' . ($u->visit?->snapshot_last_name ?? '')),
                'arrival_at'      => $u->visit?->arrival_at?->format('M d, Y'),
                'reported_at'     => $u->created_at->format('M d, Y h:i A'),
            ]);

        return Inertia::render('AdminSetPage', [
            'feeCategories' => FeeCategory::orderBy('id')->get(),

            'sitios' => Sitio::orderBy('name')->get()
                ->map(fn($s) => [
                    'id'          => $s->id,
                    'name'        => $s->name,
                    'description' => $s->description ?? '',
                    'is_active'   => (bool) $s->is_active,
                ]),

            'barangayAttractions' => BarangayAttraction::with('sitio')
                ->orderBy('name')
                ->get()
                ->map(fn($a) => [
                    'id'          => $a->id,
                    'name'        => $a->name,
                    'type'        => $a->type,
                    'description' => $a->description ?? '',
                    'sitio_id'    => $a->sitio_id,
                    'sitio_name'  => $a->sitio?->name ?? '—',
                    'is_active'   => (bool) $a->is_active,
                ]),

            'unreviewedCount' => $unreviewedCount,
            'unreviewed'      => $unreviewed,
        ]);
    }

    // ── Fee Categories ────────────────────────────────────────────────────────
    public function update(Request $request)
    {
        $request->validate([
            'rows'             => 'required|array',
            'rows.*.category'  => 'required|string|max:100',
            'rows.*.age_range' => 'required|string|max:50',
            'rows.*.fee'       => 'required|integer|min:0',
        ]);

        // Snapshot before changes for audit
        $before = FeeCategory::orderBy('id')->get(['id', 'category', 'age_range', 'fee'])->toArray();

        $incomingIds = collect($request->rows)
            ->filter(fn($r) => !empty($r['id']))
            ->pluck('id')
            ->toArray();

        FeeCategory::whereNotIn('id', $incomingIds)->delete();

        $adminName = Auth::user()->name ?? 'Admin';

        foreach ($request->rows as $row) {
            FeeCategory::updateOrCreate(
                ['id' => $row['id'] ?? null],
                [
                    'category'   => $row['category'],
                    'age_range'  => $row['age_range'],
                    'fee'        => $row['fee'],
                    'updated_by' => $adminName,
                ]
            );
        }

        $after = FeeCategory::orderBy('id')->get(['id', 'category', 'age_range', 'fee'])->toArray();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'fee_categories',
            'target_type' => 'FeeCategory',
            'target_id'   => null,
            'old_values'  => json_encode($before),
            'new_values'  => json_encode($after),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Fee categories saved successfully.');
    }

    // ── Sitio CRUD ────────────────────────────────────────────────────────────
    public function updateSitios(Request $request)
    {
        $request->validate([
            'rows'               => 'required|array',
            'rows.*.name'        => 'required|string|max:255',
            'rows.*.description' => 'nullable|string|max:1000',
            'rows.*.is_active'   => 'boolean',
        ]);

        $before = Sitio::orderBy('id')->get(['id', 'name', 'description', 'is_active'])->toArray();

        $incomingIds = collect($request->rows)
            ->filter(fn($r) => !empty($r['id']))
            ->pluck('id')
            ->toArray();

        Sitio::whereNotIn('id', $incomingIds)->delete();

        foreach ($request->rows as $row) {
            Sitio::updateOrCreate(
                ['id' => $row['id'] ?? null],
                [
                    'name'        => $row['name'],
                    'description' => $row['description'] ?? null,
                    'is_active'   => $row['is_active'] ?? true,
                ]
            );
        }

        $after = Sitio::orderBy('id')->get(['id', 'name', 'description', 'is_active'])->toArray();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'sitios',
            'target_type' => 'Sitio',
            'target_id'   => null,
            'old_values'  => json_encode($before),
            'new_values'  => json_encode($after),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Sitios saved successfully.');
    }

    // ── Barangay Attraction CRUD ──────────────────────────────────────────────
    public function updateAttractions(Request $request)
    {
        $request->validate([
            'rows'               => 'required|array',
            'rows.*.name'        => 'required|string|max:255',
            'rows.*.type'        => 'required|string|max:100',
            'rows.*.description' => 'nullable|string|max:1000',
            'rows.*.sitio_id'    => 'nullable|integer|exists:sitios,id',
            'rows.*.is_active'   => 'boolean',
        ]);

        $before = BarangayAttraction::orderBy('id')
            ->get(['id', 'name', 'type', 'sitio_id', 'is_active'])
            ->toArray();

        $incomingIds = collect($request->rows)
            ->filter(fn($r) => !empty($r['id']))
            ->pluck('id')
            ->toArray();

        BarangayAttraction::whereNotIn('id', $incomingIds)->delete();

        foreach ($request->rows as $row) {
            BarangayAttraction::updateOrCreate(
                ['id' => $row['id'] ?? null],
                [
                    'name'        => $row['name'],
                    'type'        => $row['type'],
                    'description' => $row['description'] ?? null,
                    'sitio_id'    => !empty($row['sitio_id']) ? $row['sitio_id'] : null,
                    'is_active'   => $row['is_active'] ?? true,
                ]
            );
        }

        $after = BarangayAttraction::orderBy('id')
            ->get(['id', 'name', 'type', 'sitio_id', 'is_active'])
            ->toArray();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'barangay_attractions',
            'target_type' => 'BarangayAttraction',
            'target_id'   => null,
            'old_values'  => json_encode($before),
            'new_values'  => json_encode($after),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Attractions saved successfully.');
    }

    // ── Mark unrecognized as reviewed (dismiss) ───────────────────────────────
    public function reviewUnrecognized(Request $request, $id)
    {
        $item = UnrecognizedAttraction::findOrFail($id);
        $item->update([
            'is_reviewed' => true,
            'reviewed_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'dismissed',
            'module'      => 'unrecognized_attractions',
            'target_type' => 'UnrecognizedAttraction',
            'target_id'   => (string) $id,
            'new_values'  => json_encode(['name' => $item->name, 'action' => 'dismissed']),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Marked as reviewed.');
    }

    // ── Add reported destination to official attractions ──────────────────────
    public function addFromUnrecognized(Request $request, $id)
    {
        $item = UnrecognizedAttraction::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|string|max:100',
            'sitio_id' => 'nullable|integer|exists:sitios,id',
        ]);

        $attraction = BarangayAttraction::create([
            'name'      => $request->name,
            'type'      => $request->type,
            'sitio_id'  => $request->sitio_id ?: null,
            'is_active' => true,
        ]);

        $item->update([
            'is_reviewed' => true,
            'reviewed_at' => now(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'added_from_unrecognized',
            'module'      => 'barangay_attractions',
            'target_type' => 'BarangayAttraction',
            'target_id'   => (string) $attraction->id,
            'new_values'  => json_encode([
                'name'             => $attraction->name,
                'type'             => $attraction->type,
                'sitio_id'         => $attraction->sitio_id,
                'source_report_id' => $id,
            ]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->back()->with(
            'success',
            '"' . $request->name . '" has been added to Attractions and marked as reviewed.'
        );
    }
}