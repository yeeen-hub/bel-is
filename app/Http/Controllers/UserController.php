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
                'id'            => $u->id,
                'name'          => $u->name,
                'email'         => $u->email,
                'contact_no'    => $u->contact_no ?? 'N/A', 
                'role'          => $u->roles->first()?->name ?? 'User',
                'is_active'     => (bool)$u->is_active,
                'current_session_id' => $u->current_session_id,
                'last_login_at'      => $u->last_login_at?->format('M d, Y h:i A'),
            ]);

        $roles = Role::all();

        return Inertia::render('AdminSetUMPage', [
            'users' => $users,
            'roles' => $roles,
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

         if (!auth()->user()->can('edit_user_management')) {
            return back()->with('error', 'You do not have permission to edit users.');
        }
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'contact_no'     => 'nullable|string|max:20',
            'role'           => 'required|exists:roles,name',
            'password'       => 'nullable|min:8|confirmed',
            'admin_password' => 'required',
        ]);

        // Verify Admin identity
        if (!Hash::check($request->admin_password, Auth::user()->password)) {
            return back()->withErrors(['admin_password' => 'Incorrect admin password. Update denied.']);
        }

        $old = [
            'name' => $user->name, 
            'email' => $user->email, 
            'contact_no' => $user->contact_no
        ];

        $user->name = $request->name;
        $user->email = $request->email;
        $user->contact_no = $request->contact_no; // Explicitly update

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save(); // Use save() to ensure all attributes are persisted
        $user->syncRoles([$request->role]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'users',
            'target_type' => 'User',
            'target_id'   => $user->id,
            'old_values'  => $old,
            'new_values'  => $request->only(['name', 'email', 'contact_no', 'role']),
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'User updated successfully.');
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