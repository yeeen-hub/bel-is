<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ClearExpiredSessions extends Command
{
    protected $signature   = 'sse:clear-expired-sessions';
    protected $description = 'Clear current_session_id for users whose session has expired (ghost session cleanup)';

    public function handle(): void
    {
        // Users are considered "ghost" when their account appears Occupied
        // but last_login_at is older than SESSION_LIFETIME minutes.
        // This covers: closed browser tabs, power outages, laptop lid closure.
        $lifetime     = (int) config('session.lifetime', 60);
        $cutoff       = Carbon::now()->subMinutes($lifetime);

        $ghostUsers = User::whereNotNull('current_session_id')
            ->where('last_login_at', '<', $cutoff)
            ->get();

        foreach ($ghostUsers as $user) {
            $user->update(['current_session_id' => null]);

            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => 'session_expired_cleared',
                'module'      => 'auth',
                'target_type' => 'User',
                'target_id'   => (string) $user->id,
                'new_values'  => json_encode([
                    'reason'        => 'Ghost session cleared — session expired without logout',
                    'last_login_at' => $user->last_login_at,
                    'cutoff'        => $cutoff->toDateTimeString(),
                ]),
                'ip_address'  => 'system',
            ]);

            $this->info("Cleared: {$user->name} ({$user->email})");
        }

        $this->info($ghostUsers->isEmpty()
            ? 'No ghost sessions found.'
            : "Cleared {$ghostUsers->count()} ghost session(s)."
        );
    }
}