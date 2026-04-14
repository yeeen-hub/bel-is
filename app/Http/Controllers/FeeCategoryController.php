<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeCategoryController extends Controller
{
    /**
     * Display the System Settings page with fee categories.
     */
    public function index()
    {
        return Inertia::render('Admin/AdminSetPage', [
            'feeCategories' => FeeCategory::orderBy('id')->get(),
        ]);
    }

    /**
     * Save updated fee categories.
     * Expects: { rows: [ { id?, category, age_range, fee } ] }
     */
    public function update(Request $request)
    {
        $request->validate([
            'rows'             => 'required|array',
            'rows.*.category'  => 'required|string|max:100',
            'rows.*.age_range' => 'required|string|max:50',
            'rows.*.fee'       => 'required|integer|min:0',
        ]);

        $incomingIds = collect($request->rows)
            ->filter(fn($r) => !empty($r['id']))
            ->pluck('id')
            ->toArray();

        // Delete rows that were removed on the frontend
        FeeCategory::whereNotIn('id', $incomingIds)->delete();

        $adminName = auth()->user()->name ?? 'Admin';

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

        return redirect()->back()->with('success', 'Fee categories saved successfully.');
    }
}