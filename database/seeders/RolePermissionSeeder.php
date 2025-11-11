<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management Permissions
            'user.create',
            'user.edit',
            'user.delete',
            'user.view',
            'user.list',

            // Role Management Permissions
            'role.create',
            'role.edit',
            'role.delete',
            'role.view',
            'role.list',

            // Permission Management Permissions
            'permission.create',
            'permission.edit',
            'permission.delete',
            'permission.view',
            'permission.list',

            // Dashboard Permissions
            'dashboard.view',

            // Chat Permissions
            'chat.view',
            'chat.create',
            'chat.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $user = Role::firstOrCreate(['name' => 'user']);
        $moderator = Role::firstOrCreate(['name' => 'moderator']);

        // Assign all permissions to admin
        $admin->givePermissionTo(Permission::all());

        // Assign permissions to manager
        $manager->givePermissionTo([
            'user.view',
            'user.list',
            'user.edit',
            'dashboard.view',
            'chat.view',
            'chat.create',
        ]);

        // Assign permissions to moderator
        $moderator->givePermissionTo([
            'user.view',
            'user.list',
            'dashboard.view',
            'chat.view',
            'chat.create',
            'chat.delete',
        ]);

        // Assign basic permissions to user
        $user->givePermissionTo([
            'dashboard.view',
            'chat.view',
            'chat.create',
        ]);

        $this->command->info('Roles and Permissions seeded successfully!');
        $this->command->info('Created Roles: Admin, Manager, Moderator, User');
        $this->command->info('Created ' . count($permissions) . ' permissions');
    }
}
