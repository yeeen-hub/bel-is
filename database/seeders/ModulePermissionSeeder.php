<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie Cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'system_settings',
            'user_management',
            'audit_logs',
            'website_content',
            'virtual_tour',
            'security',
            'dashboard',
            'registration',
            'visitor_records',
            'reports',
            'settings',
        ];

        foreach ($modules as $module) {
            Permission::findOrCreate("view_{$module}", 'web');
            Permission::findOrCreate("edit_{$module}", 'web');
        }

        // Admin: all permissions
        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(Permission::all());

        // Staff: specific underscored permissions
        $staff = Role::findOrCreate('staff', 'web');
        $staff->syncPermissions([
            'view_dashboard',
            'view_registration',
            'edit_registration',
            'view_visitor_records',
            'edit_visitor_records',
            'view_settings',
        ]);

        $this->command->info('Normalization complete: Permissions synced using underscores.');
    }
}