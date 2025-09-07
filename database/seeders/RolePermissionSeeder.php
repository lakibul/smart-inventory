<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // User Management
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',

            // Warehouse Management
            'warehouse.view',
            'warehouse.create',
            'warehouse.edit',
            'warehouse.delete',

            // Category Management
            'category.view',
            'category.create',
            'category.edit',
            'category.delete',

            // Product Management
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',

            // Stock Management
            'stock.view',
            'stock.edit',
            'stock.adjust',
            'stock.transfer',

            // Reports
            'reports.view',
            'reports.export',

            // Dashboard
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - All permissions
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Most permissions except user management
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            'warehouse.view', 'warehouse.create', 'warehouse.edit',
            'category.view', 'category.create', 'category.edit', 'category.delete',
            'product.view', 'product.create', 'product.edit', 'product.delete',
            'stock.view', 'stock.edit', 'stock.adjust', 'stock.transfer',
            'reports.view', 'reports.export',
            'dashboard.view',
        ]);

        // Warehouse Manager - Warehouse specific permissions
        $warehouseManager = Role::create(['name' => 'Warehouse Manager']);
        $warehouseManager->givePermissionTo([
            'product.view', 'product.create', 'product.edit',
            'stock.view', 'stock.edit', 'stock.adjust', 'stock.transfer',
            'reports.view',
            'dashboard.view',
        ]);

        // Staff - Basic permissions
        $staff = Role::create(['name' => 'Staff']);
        $staff->givePermissionTo([
            'product.view',
            'stock.view',
            'dashboard.view',
        ]);

        // Viewer - Read-only permissions
        $viewer = Role::create(['name' => 'Viewer']);
        $viewer->givePermissionTo([
            'warehouse.view',
            'category.view',
            'product.view',
            'stock.view',
            'reports.view',
            'dashboard.view',
        ]);
    }
}
