<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class SecurityController extends Controller
{
    // ── UI Tab → DB module slug ───────────────────────────────────────────────
    private array $permissionMap = [
        'Dashboard'        => 'dashboard',
        'Registration'     => 'registration',
        'Visitor Records'  => 'visitor_records',
        'Payment'          => 'payment',          // ← NEW: controls No Show action
        'Reports'          => 'reports',
        'Settings'         => 'settings',
        'General Settings' => 'system_settings',
        'User Management'  => 'user_management',
        'Audit Logs'       => 'audit_logs',
        'Website Content'  => 'website_content',
        'Virtual Tour'     => 'virtual_tour',
        'Security'         => 'security',
    ];

    public function index()
    {
        $modules = array_keys($this->permissionMap);

        $roles = Role::with('permissions')->get()->map(function($role) use ($modules) {
            $matrix = [];
            $rolePermissions = $role->permissions->pluck('name')->toArray();

            foreach ($modules as $module) {
                $slug = $this->permissionMap[$module];
                $matrix[$module] = [
                    'view' => in_array("view_$slug", $rolePermissions),
                    'edit' => in_array("edit_$slug", $rolePermissions),
                ];
            }
            return [
                'id'         => $role->id,
                'name'       => $role->name,
                'colorClass' => match(strtolower($role->name)) {
                    'admin'       => 'bg-indigo-600',
                    'staff'       => 'bg-emerald-500',
                    'coordinator' => 'bg-amber-500',
                    default       => 'bg-gray-500'
                },
                'modulePermissions' => $matrix,
            ];
        });

        return Inertia::render('AdminSetSecPage', [
            'roles'   => $roles,
            'modules' => $modules,
            'recentActivities' => AuditLog::with('user')->latest()->limit(5)->get()->map(fn($l) => [
                'id'       => $l->id,
                'action'   => $l->action,
                'user'     => $l->user->name ?? 'System',
                'time_ago' => $l->created_at->diffForHumans(),
            ]),
            'securitySettings' => DB::table('security_settings')->first(),
        ]);
    }

    // ── Save RBAC ─────────────────────────────────────────────────────────────
    public function updateRBAC(Request $request)
    {
        $request->validate(['roles' => 'required|array']);

        foreach ($request->roles as $roleData) {
            $role = Role::find($roleData['id']);
            if (!$role || strtolower($role->name) === 'admin') continue;

            $permissionsToSync = [];

            foreach ($roleData['modulePermissions'] as $moduleName => $actions) {
                $slug = $this->permissionMap[trim($moduleName)] ?? null;
                if (!$slug) continue;

                if (!empty($actions['view'])) {
                    $permissionsToSync[] = "view_{$slug}";
                }
                if (!empty($actions['edit'])) {
                    $permissionsToSync[] = "view_{$slug}";
                    $permissionsToSync[] = "edit_{$slug}";
                }
            }

            $validStrings = array_unique($permissionsToSync);
            $existsInDb   = Permission::where('guard_name', 'web')
                ->whereIn('name', $validStrings)
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($existsInDb);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', 'Permissions updated successfully.');
    }

    // ── Password Update ───────────────────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|min:8',
        ]);

        $request->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return back()->with('success', 'Your password has been changed.');
    }

    // ── Security Settings ─────────────────────────────────────────────────────
    public function updateSecuritySettings(Request $request)
    {
        $request->validate(['strong_password' => 'boolean']);

        DB::table('security_settings')->where('id', 1)->update([
            'require_strong_password' => $request->strong_password,
            'updated_at'              => now(),
        ]);

        return back()->with('success', 'Security configurations updated.');
    }

    // ── Session Management ────────────────────────────────────────────────────
    public function logoutOthers(Request $request)
    {
        Auth::logoutOtherDevices($request->current_password);

        return back()->with('success', 'Logged out from all other active sessions.');
    }
}