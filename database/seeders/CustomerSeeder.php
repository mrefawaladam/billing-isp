<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user untuk assigned_to
        $admin = User::where('email', 'admin@example.com')->first();
        $manager = User::where('email', 'manager@example.com')->first();
        $penagih1 = User::firstOrCreate(
            ['email' => 'penagih1@example.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Arif Penagih',
                'password' => bcrypt('password'),
            ]
        );
        $penagih2 = User::firstOrCreate(
            ['email' => 'penagih2@example.com'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Budi Penagih',
                'password' => bcrypt('password'),
            ]
        );

        $customers = [
            [
                'id' => Str::uuid()->toString(),
                'customer_code' => 'CUST001',
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'lat' => -6.2088,
                'lng' => 106.8456,
                'type' => 'rumahan',
                'active' => true,
                'assigned_to' => $penagih1->id,
                'monthly_fee' => 150000,
                'discount' => 0,
                'ppn_included' => false,
                'total_fee' => 165000, // dengan PPN 10%
                'invoice_due_day' => 5,
            ],
            [
                'id' => Str::uuid()->toString(),
                'customer_code' => 'CUST002',
                'name' => 'Siti Nurhaliza',
                'phone' => '081234567891',
                'address' => 'Jl. Sudirman No. 456, Jakarta Selatan',
                'lat' => -6.2297,
                'lng' => 106.8044,
                'type' => 'rumahan',
                'active' => true,
                'assigned_to' => $penagih1->id,
                'monthly_fee' => 200000,
                'discount' => 10000,
                'ppn_included' => false,
                'total_fee' => 209000, // (200000 - 10000) + PPN 10%
                'invoice_due_day' => 10,
            ],
            [
                'id' => Str::uuid()->toString(),
                'customer_code' => 'CUST003',
                'name' => 'PT Maju Jaya',
                'phone' => '02112345678',
                'address' => 'Jl. Thamrin No. 789, Jakarta Pusat',
                'lat' => -6.1944,
                'lng' => 106.8229,
                'type' => 'kantor',
                'active' => true,
                'assigned_to' => $penagih2->id,
                'monthly_fee' => 500000,
                'discount' => 0,
                'ppn_included' => true,
                'total_fee' => 500000, // sudah termasuk PPN
                'invoice_due_day' => 15,
            ],
            [
                'id' => Str::uuid()->toString(),
                'customer_code' => 'CUST004',
                'name' => 'SMA Negeri 1 Jakarta',
                'phone' => '02156789012',
                'address' => 'Jl. Pendidikan No. 321, Jakarta Timur',
                'lat' => -6.2297,
                'lng' => 106.8806,
                'type' => 'sekolah',
                'active' => true,
                'assigned_to' => $admin->id,
                'monthly_fee' => 300000,
                'discount' => 50000,
                'ppn_included' => false,
                'total_fee' => 275000, // (300000 - 50000) + PPN 10%
                'invoice_due_day' => 20,
            ],
            [
                'id' => Str::uuid()->toString(),
                'customer_code' => 'CUST005',
                'name' => 'Ahmad Hidayat',
                'phone' => '081234567892',
                'address' => 'Jl. Kebon Jeruk No. 654, Jakarta Barat',
                'lat' => -6.1944,
                'lng' => 106.7889,
                'type' => 'rumahan',
                'active' => true,
                'assigned_to' => $penagih2->id,
                'monthly_fee' => 100000,
                'discount' => 0,
                'ppn_included' => false,
                'total_fee' => 110000, // dengan PPN 10%
                'invoice_due_day' => 1,
            ],
            [
                'id' => Str::uuid()->toString(),
                'customer_code' => 'CUST006',
                'name' => 'Free User Test',
                'phone' => '081234567893',
                'address' => 'Jl. Test No. 999, Jakarta Utara',
                'lat' => -6.1214,
                'lng' => 106.7741,
                'type' => 'free',
                'active' => true,
                'assigned_to' => $manager->id,
                'monthly_fee' => 0,
                'discount' => 0,
                'ppn_included' => false,
                'total_fee' => 0,
                'invoice_due_day' => 1,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('Berhasil membuat ' . count($customers) . ' pelanggan sample!');
    }
}
