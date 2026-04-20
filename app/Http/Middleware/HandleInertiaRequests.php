<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'   => $request->user()->id,
                    'name' => $request->user()->name,
                    'role' => $request->user()->getRoleNames()->first() ?? 'staff',
                ] : null,
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')
                    : [],
            ],
            // FIXED: Added keys to capture registration data from the session
            'flash' => [
                'success'        => $request->session()->get('success'),
                'error'          => $request->session()->get('error'),
                'info'           => $request->session()->get('info'),
                
                // --- Add these keys below ---
                'mode'           => $request->session()->get('mode'),
                'reference_code' => $request->session()->get('reference_code'),
                'group_code'     => $request->session()->get('group_code'),
                'full_name'      => $request->session()->get('full_name'),
                'members'        => $request->session()->get('members'),
            ],
        ];
    }
}