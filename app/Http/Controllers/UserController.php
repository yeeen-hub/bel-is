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
                'role'          => $u->roles->first()?->name ?? 'No Role',
                'is_active'     => $u->is_active,
                'last_login_at' => $u->last_login_at?->format('M d, Y h:i A') ?? 'Never',
            ]);

        $roles = Role::all()->map(fn($r) => [
            'id'   => $r->id,
            'name' => $r->name,
        ]);

        return Inertia::render('AdminSetPage', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'module'      => 'users',
            'target_type' => 'User',
            'target_id'   => $user->id,
            'new_values'  => ['name' => $user->name, 'email' => $user->email, 'role' => $request->role],
            'ip_address'  => $request->ip(),
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|exists:roles,name',
        ]);

        $old = ['name' => $user->name, 'email' => $user->email];

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        $user->syncRoles([$request->role]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'users',
            'target_type' => 'User',
            'target_id'   => $user->id,
            'old_values'  => $old,
            'new_values'  => ['name' => $user->name, 'email' => $user->email, 'role' => $request->role],
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
}