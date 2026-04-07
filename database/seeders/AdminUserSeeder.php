<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@belischeckpoint.ph'],
            [
                'name'      => 'System Admin',
                'password'  => Hash::make('Admin@12345'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // 2. Barangay Staff
        $staff = User::firstOrCreate(
            ['email' => 'staff@belischeckpoint.ph'],
            [
                'name'      => 'Barangay Staff',
                'password'  => Hash::make('Staff@12345'),
                'is_active' => true,
            ]
        );
        $staff->assignRole('staff');

        // 3. Tourism Coordinator
        $coordinator = User::firstOrCreate(
            ['email' => 'coordinator@belischeckpoint.ph'],
            [
                'name'      => 'Tourism Coordinator',
                'password'  => Hash::make('Coord@12345'),
                'is_active' => true,
            ]
        );
        $coordinator->assignRole('coordinator');

        // 4. LGU Official
        $lgu = User::firstOrCreate(
            ['email' => 'lgu@belischeckpoint.ph'],
            [
                'name'      => 'LGU Official',
                'password'  => Hash::make('LGU@12345'),
                'is_active' => true,
            ]
        );
        $lgu->assignRole('lgu_official');
    }
}