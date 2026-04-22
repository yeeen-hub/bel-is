<?php

namespace App\Http\Controllers;

use App\Models\BarangayAttraction;
use App\Models\Sitio;
use App\Models\UnrecognizedAttraction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangayAttractionController extends Controller
{
    public function index()
    {
        $attractions = BarangayAttraction::with('sitio')
            ->orderBy('name')
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'name'        => $a->name,
                'type'        => $a->type,
                'description' => $a->description,
                'sitio_id'    => $a->sitio_id,
                'sitio_name'  => $a->sitio?->name ?? '—',
                'is_active'   => $a->is_active,
            ]);

        $sitios = Sitio::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Unreviewed "other" destination reports
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

        return Inertia::render('AdminAttractionMgmtPage', [
            'attractions'   => $attractions,
            'sitios'        => $sitios,
            'unreviewed'    => $unreviewed,
            'unreviewedCount' => $unreviewed->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'sitio_id'    => 'nullable|exists:sitios,id',
            'is_active'   => 'boolean',
        ]);

        BarangayAttraction::create([
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description,
            'sitio_id'    => $request->sitio_id,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->back()->with('success', 'Attraction added successfully.');
    }

    public function update(Request $request, BarangayAttraction $barangayAttraction)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'sitio_id'    => 'nullable|exists:sitios,id',
            'is_active'   => 'boolean',
        ]);

        $barangayAttraction->update([
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description,
            'sitio_id'    => $request->sitio_id,
            'is_active'   => $request->boolean('is_active', $barangayAttraction->is_active),
        ]);

        return redirect()->back()->with('success', 'Attraction updated successfully.');
    }

    public function destroy(BarangayAttraction $barangayAttraction)
    {
        $barangayAttraction->delete();
        return redirect()->back()->with('success', 'Attraction deleted successfully.');
    }

    /** Mark an unrecognized attraction report as reviewed */
    public function reviewUnrecognized(Request $request, UnrecognizedAttraction $unrecognizedAttraction)
    {
        $unrecognizedAttraction->update([
            'is_reviewed' => true,
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Marked as reviewed.');
    }

    /** Return active attractions as JSON (used by registration dropdowns) */
    public function list()
    {
        $attractions = BarangayAttraction::with('sitio')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($a) => [
                'id'         => $a->id,
                'name'       => $a->name,
                'type'       => $a->type,
                'sitio_name' => $a->sitio?->name,
            ]);

        return response()->json($attractions);
    }
}