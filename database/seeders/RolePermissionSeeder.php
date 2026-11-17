<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Module: Products
            ['name' => 'View Products', 'code' => 'products.view', 'module' => 'products', 'description' => 'Can view catalog products'],
            ['name' => 'Create Products', 'code' => 'products.create', 'module' => 'products', 'description' => 'Can create new products'],
            ['name' => 'Edit Products', 'code' => 'products.edit', 'module' => 'products', 'description' => 'Can update existing products'],
            ['name' => 'Delete Products', 'code' => 'products.delete', 'module' => 'products', 'description' => 'Can delete products'],

            // Module: Categories
            ['name' => 'Manage Categories', 'code' => 'categories.manage', 'module' => 'categories', 'description' => 'Can manage product categories'],

            // Module: Orders
            ['name' => 'View Orders', 'code' => 'orders.view', 'module' => 'orders', 'description' => 'Can view order history'],
            ['name' => 'Create Orders', 'code' => 'orders.create', 'module' => 'orders', 'description' => 'Can place and create orders'],
            ['name' => 'Manage Orders', 'code' => 'orders.manage', 'module' => 'orders', 'description' => 'Can update status and manage orders'],
            ['name' => 'Cancel Orders', 'code' => 'orders.cancel', 'module' => 'orders', 'description' => 'Can cancel orders'],

            // Module: Tables & Sessions
            ['name' => 'View Tables', 'code' => 'tables.view', 'module' => 'tables', 'description' => 'Can view dining tables'],
            ['name' => 'Manage Tables', 'code' => 'tables.manage', 'module' => 'tables', 'description' => 'Can create and configure dining tables'],
            ['name' => 'Manage Sessions', 'code' => 'tables.sessions', 'module' => 'tables', 'description' => 'Can open and close table sessions'],

            // Module: Payments
            ['name' => 'Process Payments', 'code' => 'payments.process', 'module' => 'payments', 'description' => 'Can process payments and issue receipts'],

            // Module: Users & Roles
            ['name' => 'Manage Users', 'code' => 'users.manage', 'module' => 'users', 'description' => 'Can manage users and staff accounts'],
            ['name' => 'Manage Roles', 'code' => 'roles.manage', 'module' => 'roles', 'description' => 'Can manage roles and permissions'],

            // Module: Marketing & Wheel
            ['name' => 'Manage Promotions', 'code' => 'promotions.manage', 'module' => 'marketing', 'description' => 'Can manage discounts and lucky wheel'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['code']] = Permission::firstOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'module' => $p['module'],
                    'description' => $p['description'],
                ]
            );
        }

        // 1. Super Admin
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['description' => 'Full administrative access to all system features', 'is_active' => true]
        );
        $superAdmin->permissions()->sync(collect($permissionModels)->pluck('id'));

        // 2. Manager
        $manager = Role::firstOrCreate(
            ['name' => 'Manager'],
            ['description' => 'Branch operations, catalog, orders, and tables management', 'is_active' => true]
        );
        $managerPermissions = collect($permissionModels)
            ->filter(fn($p) => !in_array($p->code, ['roles.manage']))
            ->pluck('id');
        $manager->permissions()->sync($managerPermissions);

        // 3. Cashier
        $cashier = Role::firstOrCreate(
            ['name' => 'Cashier'],
            ['description' => 'Point of sale, orders processing, table sessions, and payments', 'is_active' => true]
        );
        $cashierPermissions = collect($permissionModels)
            ->filter(fn($p) => in_array($p->code, [
                'products.view', 'orders.view', 'orders.create', 'orders.manage',
                'tables.view', 'tables.sessions', 'payments.process'
            ]))
            ->pluck('id');
        $cashier->permissions()->sync($cashierPermissions);

        // 4. Waiter
        $waiter = Role::firstOrCreate(
            ['name' => 'Waiter'],
            ['description' => 'Take customer orders, view tables, and table sessions', 'is_active' => true]
        );
        $waiterPermissions = collect($permissionModels)
            ->filter(fn($p) => in_array($p->code, [
                'products.view', 'orders.view', 'orders.create', 'tables.view', 'tables.sessions'
            ]))
            ->pluck('id');
        $waiter->permissions()->sync($waiterPermissions);

        // 5. Customer
        $customer = Role::firstOrCreate(
            ['name' => 'Customer'],
            ['description' => 'E-Menu customer browsing, QR ordering, and lucky wheel', 'is_active' => true]
        );
        $customerPermissions = collect($permissionModels)
            ->filter(fn($p) => in_array($p->code, ['products.view', 'orders.create', 'orders.view']))
            ->pluck('id');
        $customer->permissions()->sync($customerPermissions);
    }
}
