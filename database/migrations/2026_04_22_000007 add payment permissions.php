<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create the two new permissions for the Payment module
        $view = Permission::firstOrCreate(['name' => 'view_payment', 'guard_name' => 'web']);
        $edit = Permission::firstOrCreate(['name' => 'edit_payment', 'guard_name' => 'web']);

        // Grant both to admin automatically (admin always gets everything)
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo([$view, $edit]);
        }

        // Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', ['view_payment', 'edit_payment'])->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};