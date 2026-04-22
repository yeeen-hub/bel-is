<?php

namespace App\Http\Controllers;

use App\Models\Sitio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SitioController extends Controller
{
    public function index()
    {
        return Inertia::render('AdminSitioPage', [
            'sitios' => Sitio::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:sitios,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Sitio::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => true,
        ]);

        return redirect()->back()->with('success', 'Sitio added successfully.');
    }

    public function update(Request $request, Sitio $sitio)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:sitios,name,' . $sitio->id,
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        $sitio->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', $sitio->is_active),
        ]);

        return redirect()->back()->with('success', 'Sitio updated successfully.');
    }

    public function destroy(Sitio $sitio)
    {
        $sitio->delete();
        return redirect()->back()->with('success', 'Sitio deleted successfully.');
    }

    /** Return all active sitios as JSON (used by attraction management dropdown) */
    public function list()
    {
        return response()->json(
            Sitio::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }
}