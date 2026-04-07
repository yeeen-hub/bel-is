<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourismContent;
use App\Models\VirtualHotspot;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VirtualTourController extends Controller
{
    public function index()
    {
        $scenes = TourismContent::where('type', 'virtual_scene')
            ->with('hotspots')
            ->latest()
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'title'        => $s->title,
                'cover_image'  => $s->cover_image,
                'is_published' => $s->is_published,
                'hotspots'     => $s->hotspots->map(fn($h) => [
                    'id'              => $h->id,
                    'type'            => $h->type,
                    'label'           => $h->label,
                    'pitch'           => $h->pitch,
                    'yaw'             => $h->yaw,
                    'target_scene_id' => $h->target_scene_id,
                    'content'         => $h->content,
                    'media_url'       => $h->media_url,
                    'is_active'       => $h->is_active,
                ]),
            ]);

        return Inertia::render('AdminVRPage', [
            'scenes' => $scenes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'cover_image' => 'nullable|string',
            'hotspots'    => 'nullable|array',
        ]);

        $scene = TourismContent::create([
            'type'         => 'virtual_scene',
            'title'        => $request->title,
            'slug'         => Str::slug($request->title) . '-' . Str::random(5),
            'cover_image'  => $request->cover_image,
            'is_published' => $request->is_published ?? false,
            'created_by'   => Auth::id(),
        ]);

        // Create hotspots if provided
        if ($request->hotspots) {
            foreach ($request->hotspots as $hotspot) {
                VirtualHotspot::create([
                    'scene_id'        => $scene->id,
                    'type'            => $hotspot['type'],
                    'label'           => $hotspot['label'],
                    'pitch'           => $hotspot['pitch'] ?? 0,
                    'yaw'             => $hotspot['yaw'] ?? 0,
                    'target_scene_id' => $hotspot['target_scene_id'] ?? null,
                    'content'         => $hotspot['content'] ?? null,
                    'media_url'       => $hotspot['media_url'] ?? null,
                    'is_active'       => true,
                ]);
            }
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'module'      => 'virtual_tour',
            'target_type' => 'TourismContent',
            'target_id'   => $scene->id,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Virtual scene created successfully.');
    }

    public function update(Request $request, VirtualHotspot $virtualHotspot)
    {
        $request->validate([
            'type'            => 'required|in:info,scene_link,media',
            'label'           => 'required|string|max:255',
            'pitch'           => 'required|numeric',
            'yaw'             => 'required|numeric',
            'target_scene_id' => 'nullable|exists:tourism_contents,id',
            'content'         => 'nullable|string',
            'media_url'       => 'nullable|string',
            'is_active'       => 'boolean',
        ]);

        $virtualHotspot->update($request->only([
            'type', 'label', 'pitch', 'yaw',
            'target_scene_id', 'content', 'media_url', 'is_active',
        ]));

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'virtual_tour',
            'target_type' => 'VirtualHotspot',
            'target_id'   => $virtualHotspot->id,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'Hotspot updated successfully.');
    }

    public function destroy(Request $request, VirtualHotspot $virtualHotspot)
    {
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'deleted',
            'module'      => 'virtual_tour',
            'target_type' => 'VirtualHotspot',
            'target_id'   => $virtualHotspot->id,
            'ip_address'  => $request->ip(),
        ]);

        $virtualHotspot->delete();

        return back()->with('success', 'Hotspot deleted successfully.');
    }
}