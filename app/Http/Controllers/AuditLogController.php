<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                $request->date_from,
                $request->date_to,
            ]);
        }

        $logs = $query->paginate(20)->withQueryString();

        return Inertia::render('AdminAuditPage', [
            'logs' => $logs->through(function ($log) {
                return [
                    'id'         => $log->id,
                    'user'       => $log->user->name ?? 'Unknown',
                    'action'     => $log->action,
                    'module'     => $log->module,
                    // target_id is now a UUID string — no # prefix needed
                    'target'     => $log->target_type . ' ' . ($log->target_id ?? ''),
                    'ip_address' => $log->ip_address,
                    'device_id'  => $log->device_id,
                    'created_at' => $log->created_at->format('M d, Y h:i A'),
                ];
            }),
            'filters' => $request->only(['user_id', 'module', 'action', 'date_from', 'date_to']),
        ]);
    }
}