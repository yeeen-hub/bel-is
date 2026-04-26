<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->latest()
            ->get()
            ->map(fn($u) => [
                'id'                 => $u->id,
                'name'               => $u->name,
                'email'              => $u->email,
                'contact_no'         => $u->contact_no ?? 'N/A',
                // Always read live role from DB relationship — never cached
                'role'               => $u->roles->first()?->name ?? '',
                'is_active'          => (bool) $u->is_active,
                'current_session_id' => $u->current_session_id,
                'last_login_at'      => $u->last_login_at?->format('M d, Y h:i A'),
            ]);
    
        // Load ALL roles from DB — ordered so they appear consistently in dropdown.
        // Any role added via: php artisan permission:create-role new_role
        // or directly inserted into the roles table will appear here automatically.
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'name' => $r->name,   // raw slug — Vue converts to label dynamically
            ]);
    
        $pendingFees = \App\Models\Visitor::whereNull('deleted_at')
            ->where('fee_status', 'Pending')
            ->count();
    
        return Inertia::render('AdminSetUMPage', [
            'users'       => $users,
            'roles'       => $roles,
            'pendingFees' => $pendingFees,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'contact_no'     => 'nullable|string|max:20',
            'password'       => 'required|min:8|confirmed',
            'role'           => 'required|exists:roles,name',
            'admin_password' => 'required', // Added admin confirmation
        ]);

        // Verify Admin identity before creating
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'Incorrect admin password. Creation denied.']);
        }

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'contact_no' => $request->contact_no, // Ensuring this is passed
            'password'   => Hash::make($request->password),
            'is_active'  => true,
        ]);

        $user->assignRole($request->role);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'module'      => 'users',
            'target_type' => 'User',
            'target_id'   => $user->id,
            'new_values'  => $request->only(['name', 'email', 'contact_no', 'role']),
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        // Permission check
        if (!auth()->user()->can('edit_user_management')) {
            return back()->withErrors(['error' => 'You do not have permission to edit users.']);
        }
    
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'contact_no'     => 'nullable|string|max:20',
            'role'           => 'required|string|exists:roles,name',
            // password optional on edit — only validated if provided
            'password'       => 'nullable|min:8|confirmed',
            'admin_password' => 'required|string',
        ]);
    
        // Verify admin identity
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors([
                'admin_password' => 'Incorrect admin password. Update denied.',
            ]);
        }
    
        // Track old values for audit
        $oldRole = $user->roles->first()?->name ?? 'none';
        $old     = [
            'name'       => $user->name,
            'email'      => $user->email,
            'contact_no' => $user->contact_no,
            'role'       => $oldRole,
        ];
    
        // Update user fields
        $user->name       = $request->name;
        $user->email      = $request->email;
        $user->contact_no = $request->contact_no;
    
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
    
        $user->save();
    
        // Sync role — syncRoles replaces ALL current roles with the new one
        // This is the correct Spatie method for single-role systems
        $user->syncRoles([$request->role]);
    
        // Force reload the roles relationship so the response reflects the new role
        $user->load('roles');
    
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'users',
            'target_type' => 'User',
            'target_id'   => $user->id,
            'old_values'  => json_encode($old),
            'new_values'  => json_encode([
                'name'       => $request->name,
                'email'      => $request->email,
                'contact_no' => $request->contact_no,
                'role'       => $request->role,
            ]),
            'ip_address'  => $request->ip(),
        ]);
    
        return back()->with('success', "User {$user->name} updated successfully.");
    }

    

    public function toggleActive(Request $request, User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => $user->is_active ? 'activated' : 'deactivated',
            'module'      => 'users',
            'target_type' => 'User',
            'target_id'   => $user->id,
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'User status updated.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'admin_password' => 'required',
        ]);

        // 1. Verify Admin Password
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors([
                'admin_password' => 'The provided admin password was incorrect.'
            ]);
        }

        // 2. Filter out the current admin
        $idsToDelete = array_filter($request->ids, fn($id) => $id != Auth::id());

        if (empty($idsToDelete)) {
            return back()->withErrors(['admin_password' => 'You cannot delete your own account.']);
        }

        User::whereIn('id', $idsToDelete)->delete();

        return back()->with('success', count($idsToDelete) . ' user(s) deleted.');
    }


     public function forceSessionClear(Request $request, User $user)
    {
        if ($user->current_session_id === null) {
            return back()->with('info', "{$user->name}'s account has no active session.");
        }
 
        $cleared = $user->current_session_id;
        $user->update(['current_session_id' => null]);
 
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'session_force_cleared',
            'module'      => 'user_management',
            'target_type' => 'User',
            'target_id'   => $user->id,
            'new_values'  => json_encode([
                'cleared_for'   => $user->name,
                'cleared_by'    => Auth::user()->name,
                'session'       => $cleared,
            ]),
            'ip_address'  => $request->ip(),
        ]);
 
        return back()->with('success', "Session cleared for {$user->name}.");
    }
}