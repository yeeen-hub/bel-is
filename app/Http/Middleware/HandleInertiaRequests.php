<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],

            'flash' => [
                'success'        => session('success'),
                'mode'           => session('mode'),
                'reference_code' => session('reference_code'),
                'full_name'      => session('full_name'),
                'visit_id'       => session('visit_id'),
                'group_code'     => session('group_code'),
                'members'        => session('members'),
                'message'        => session('message'),
                'error'          => session('error'),
            ],
        ];
        
    }
}
