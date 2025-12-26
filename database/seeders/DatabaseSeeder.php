<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Roles and Permissions first
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        // Create Manager User
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Manager User',
                'password' => bcrypt('password'),
            ]
        );
        $manager->assignRole('manager');

        // Create Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Regular User',
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole('user');

        // Create Test User (if not exists)
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
        if (!$testUser->hasRole('user')) {
            $testUser->assignRole('user');
        }

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Manager: manager@example.com / password');
        $this->command->info('User: user@example.com / password');

        // Seed data aplikasi ISP Management
        $this->command->info('');
        $this->command->info('Seeding data aplikasi...');

        $this->call([
            PackageSeeder::class,
            CustomerSeeder::class,
            DeviceSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
            WaNotificationSeeder::class,
            InventoryItemSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Semua data berhasil di-seed!');
    }
}
