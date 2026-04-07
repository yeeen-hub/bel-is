<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourismContent;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TourismContentController extends Controller
{
    public function index()
    {
        $contents = TourismContent::with('createdBy')
            ->latest()
            ->get()
            ->map(fn($c) => [
                'id'           => $c->id,
                'type'         => $c->type,
                'title'        => $c->title,
                'slug'         => $c->slug,
                'excerpt'      => $c->excerpt,
                'cover_image'  => $c->cover_image,
                'is_published' => $c->is_published,
                'sort_order'   => $c->sort_order,
                'created_by'   => $c->createdBy->name,
                'created_at'   => $c->created_at->format('M d, Y'),
            ]);

        return Inertia::render('AdminSetPage', [
            'contents' => $contents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'         => 'required|in:attraction,package,circuit,history,virtual_scene',
            'title'        => 'required|string|max:255',
            'body'         => 'nullable|string',
            'excerpt'      => 'nullable|string|max:500',
            'cover_image'  => 'nullable|string',
            'is_published' => 'boolean',
            'sort_order'   => 'integer',
        ]);

        $content = TourismContent::create([
            'type'         => $request->type,
            'title'        => $request->title,
            'slug'         => Str::slug($request->title) . '-' . Str::random(5),
            'body'         => $request->body,
            'excerpt'      => $request->excerpt,
            'cover_image'  => $request->cover_image,
            'is_published' => $request->is_published ?? false,
            'sort_order'   => $request->sort_order ?? 0,
            'created_by'   => Auth::id(),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'module'      => 'tourism_contents',
            'target_type' => 'TourismContent',
            'target_id'   => $content->id,
            'new_values'  => $content->toArray(),
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Content created successfully.');
    }

    public function update(Request $request, TourismContent $tourismContent)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'nullable|string',
            'excerpt'      => 'nullable|string|max:500',
            'cover_image'  => 'nullable|string',
            'is_published' => 'boolean',
            'sort_order'   => 'integer',
        ]);

        $old = $tourismContent->toArray();

        $tourismContent->update([
            'title'        => $request->title,
            'slug'         => Str::slug($request->title) . '-' . Str::random(5),
            'body'         => $request->body,
            'excerpt'      => $request->excerpt,
            'cover_image'  => $request->cover_image,
            'is_published' => $request->is_published ?? false,
            'sort_order'   => $request->sort_order ?? 0,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'tourism_contents',
            'target_type' => 'TourismContent',
            'target_id'   => $tourismContent->id,
            'old_values'  => $old,
            'new_values'  => $tourismContent->toArray(),
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Content updated successfully.');
    }

    public function destroy(Request $request, TourismContent $tourismContent)
    {
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'deleted',
            'module'      => 'tourism_contents',
            'target_type' => 'TourismContent',
            'target_id'   => $tourismContent->id,
            'old_values'  => $tourismContent->toArray(),
            'ip_address'  => $request->ip(),
        ]);

        $tourismContent->delete();

        return back()->with('success', 'Content deleted successfully.');
    }
}