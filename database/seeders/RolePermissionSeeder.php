<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Create Permissions ─────────────────────────────────────────────

        $permissions = [
            // Visitor permissions
            'view visitors',
            'create visitors',
            'update visitors',
            'delete visitors',

            // Receipt permissions
            'view receipts',
            'create receipts',
            'update receipts',

            // Report permissions
            'view reports',
            'export reports',

            // Analytics permissions
            'view analytics',

            // User management permissions
            'view users',
            'create users',
            'update users',
            'delete users',

            // Content management permissions
            'view content',
            'create content',
            'update content',
            'delete content',

            // Virtual tour permissions
            'view virtual tour',
            'manage virtual tour',

            // Audit log permissions
            'view audit logs',

            // Settings permissions
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── Create Roles & Assign Permissions ──────────────────────────────

        // 1. System Admin — full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // 2. Barangay Staff — registration + fees + reports
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'view visitors',
            'create visitors',
            'update visitors',
            'view receipts',
            'create receipts',
            'update receipts',
            'view reports',
            'export reports',
            'view analytics',
        ]);

        // 3. Tourism Coordinator — analytics + reports
        $coordinator = Role::firstOrCreate(['name' => 'coordinator']);
        $coordinator->syncPermissions([
            'view visitors',
            'view receipts',
            'view reports',
            'export reports',
            'view analytics',
            'view content',
            'create content',
            'update content',
            'view virtual tour',
        ]);

        // 4. LGU Official — read-only analytics
        $lgu = Role::firstOrCreate(['name' => 'lgu_official']);
        $lgu->syncPermissions([
            'view visitors',
            'view receipts',
            'view reports',
            'view analytics',
        ]);
    }
}