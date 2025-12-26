<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // Dedicated Internet
            [
                'name' => 'Bisma MAN 2 Ponorogo',
                'package_code' => '2504106992',
                'speed' => '1000Mbps',
                'service_type' => 'Dedicated Internet',
                'price' => 21500000,
                'description' => 'Paket Dedicated Internet untuk MAN 2 Ponorogo',
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Bisma MAN 2 Ponorogo',
                'package_code' => '2504116998',
                'speed' => '1000Mbps',
                'service_type' => 'Dedicated Internet',
                'price' => 8500000,
                'description' => 'Paket Dedicated Internet untuk MAN 2 Ponorogo',
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Bisma MTsN 1 Po',
                'package_code' => '2502066895',
                'speed' => '200Mbps',
                'service_type' => 'Dedicated Internet',
                'price' => 6000000,
                'description' => 'Paket Dedicated Internet untuk MTsN 1 Ponorogo',
                'active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Bisma SMKN 1 Slahung',
                'package_code' => '2502076898',
                'speed' => '200Mbps',
                'service_type' => 'Dedicated Internet',
                'price' => 5000000,
                'description' => 'Paket Dedicated Internet untuk SMKN 1 Slahung',
                'active' => true,
                'sort_order' => 4,
            ],
            
            // Internet Broadband
            [
                'name' => 'Bisnis 10',
                'package_code' => '2403145894',
                'speed' => '50Mbps',
                'service_type' => 'Internet Broadband',
                'price' => 850000,
                'description' => 'Paket Internet Broadband Bisnis 10',
                'active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Bisnis 7',
                'package_code' => '2212054979',
                'speed' => '30Mbps',
                'service_type' => 'Internet Broadband',
                'price' => 1700000,
                'description' => 'Paket Internet Broadband Bisnis 7',
                'active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Bisnis 8',
                'package_code' => '2308165323',
                'speed' => '30Mbps',
                'service_type' => 'Internet Broadband',
                'price' => 800000,
                'description' => 'Paket Internet Broadband Bisnis 8',
                'active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Bisnis 9',
                'package_code' => '2310165500',
                'speed' => '100Mbps',
                'service_type' => 'Internet Broadband',
                'price' => 3885000,
                'description' => 'Paket Internet Broadband Bisnis 9',
                'active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Data',
                'package_code' => '2512187408',
                'speed' => '2Mbps',
                'service_type' => 'Internet Broadband',
                'price' => 125000,
                'description' => 'Paket Internet Broadband Data',
                'active' => true,
                'sort_order' => 9,
            ],
        ];

        foreach ($packages as $packageData) {
            Package::firstOrCreate(
                ['package_code' => $packageData['package_code']],
                array_merge(['id' => Str::uuid()->toString()], $packageData)
            );
        }

        $this->command->info('Packages seeded successfully!');
    }
}
