<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Build Query with Relationships
        $query = AuditLog::with('user.roles')->latest('created_at');

        // 2. Apply Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('action', 'like', "%{$request->search}%")
                  ->orWhere('target_type', 'like', "%{$request->search}%")
                  ->orWhere('ip_address', 'like', "%{$request->search}%");
            });
        }

        // 3. Paginate and Format Data
        $logs = $query->paginate(15)->withQueryString()->through(function ($log) {
            return [
                'id'         => $log->id,
                'user'       => $log->user->name ?? 'System',
                'role'       => $log->user->roles->first()->name ?? 'N/A',
                'action'     => str_replace('_', ' ', $log->action),
                'module'     => $log->module,
                'description'=> $log->target_type . ' (' . ($log->target_id ?? 'N/A') . ')',
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->format('M d, Y h:i A'),
            ];
        });

        return Inertia::render('AdminSetALPage', [
            'logs'    => $logs,
            'filters' => $request->only(['user_id', 'module', 'search']),
            'users'   => User::select('id', 'name')->get(),
            'modules' => AuditLog::distinct()->pluck('module'),
        ]);
    }
}