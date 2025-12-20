<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Routers
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ROU-0001',
                'name' => 'Router TP-Link Archer C6',
                'type' => 'router',
                'brand' => 'TP-Link',
                'model' => 'Archer C6',
                'description' => 'Router WiFi AC1200 Dual Band',
                'stock_quantity' => 10,
                'min_stock' => 3,
                'unit' => 'pcs',
                'price' => 450000,
                'location' => 'Gudang A, Rak 1',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ROU-0002',
                'name' => 'Router MikroTik hAP ac2',
                'type' => 'router',
                'brand' => 'MikroTik',
                'model' => 'hAP ac2',
                'description' => 'Router WiFi AC Dual Band',
                'stock_quantity' => 8,
                'min_stock' => 2,
                'unit' => 'pcs',
                'price' => 1200000,
                'location' => 'Gudang A, Rak 1',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ROU-0003',
                'name' => 'Router Ubiquiti UniFi Dream Machine',
                'type' => 'router',
                'brand' => 'Ubiquiti',
                'model' => 'UniFi Dream Machine',
                'description' => 'Router WiFi 6 Enterprise',
                'stock_quantity' => 3,
                'min_stock' => 1,
                'unit' => 'pcs',
                'price' => 3500000,
                'location' => 'Gudang A, Rak 1',
                'active' => true,
            ],

            // ONT
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ONT-0001',
                'name' => 'ONT ZTE F660',
                'type' => 'ont',
                'brand' => 'ZTE',
                'model' => 'F660',
                'description' => 'ONT GPON 4 Port',
                'stock_quantity' => 15,
                'min_stock' => 5,
                'unit' => 'pcs',
                'price' => 350000,
                'location' => 'Gudang A, Rak 2',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ONT-0002',
                'name' => 'ONT Huawei HG8245H',
                'type' => 'ont',
                'brand' => 'Huawei',
                'model' => 'HG8245H',
                'description' => 'ONT GPON 4 Port WiFi',
                'stock_quantity' => 12,
                'min_stock' => 4,
                'unit' => 'pcs',
                'price' => 450000,
                'location' => 'Gudang A, Rak 2',
                'active' => true,
            ],

            // Kabel
            [
                'id' => Str::uuid()->toString(),
                'code' => 'KAB-0001',
                'name' => 'Kabel UTP Cat 6',
                'type' => 'kabel',
                'brand' => 'Belden',
                'model' => 'Cat 6',
                'description' => 'Kabel UTP Cat 6 305 meter',
                'stock_quantity' => 20,
                'min_stock' => 5,
                'unit' => 'roll',
                'price' => 850000,
                'location' => 'Gudang B, Rak 3',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'KAB-0002',
                'name' => 'Kabel Fiber Optic Single Mode',
                'type' => 'kabel',
                'brand' => 'Generic',
                'model' => 'SM 9/125',
                'description' => 'Kabel Fiber Optic Single Mode 1 Core',
                'stock_quantity' => 500,
                'min_stock' => 100,
                'unit' => 'meter',
                'price' => 15000,
                'location' => 'Gudang B, Rak 4',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'KAB-0003',
                'name' => 'Kabel Patch Cord Cat 6',
                'type' => 'kabel',
                'brand' => 'Generic',
                'model' => 'Cat 6',
                'description' => 'Kabel Patch Cord Cat 6 1 meter',
                'stock_quantity' => 50,
                'min_stock' => 10,
                'unit' => 'pcs',
                'price' => 25000,
                'location' => 'Gudang B, Rak 3',
                'active' => true,
            ],

            // Connector
            [
                'id' => Str::uuid()->toString(),
                'code' => 'CON-0001',
                'name' => 'Connector RJ45',
                'type' => 'connector',
                'brand' => 'Generic',
                'model' => 'RJ45',
                'description' => 'Connector RJ45 untuk kabel UTP',
                'stock_quantity' => 200,
                'min_stock' => 50,
                'unit' => 'pcs',
                'price' => 5000,
                'location' => 'Gudang B, Box 1',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'CON-0002',
                'name' => 'Connector SC/APC',
                'type' => 'connector',
                'brand' => 'Generic',
                'model' => 'SC/APC',
                'description' => 'Connector SC/APC untuk fiber optic',
                'stock_quantity' => 100,
                'min_stock' => 20,
                'unit' => 'pcs',
                'price' => 15000,
                'location' => 'Gudang B, Box 2',
                'active' => true,
            ],

            // Switch
            [
                'id' => Str::uuid()->toString(),
                'code' => 'SWI-0001',
                'name' => 'Switch TP-Link TL-SG108',
                'type' => 'switch',
                'brand' => 'TP-Link',
                'model' => 'TL-SG108',
                'description' => 'Switch 8 Port Gigabit',
                'stock_quantity' => 5,
                'min_stock' => 2,
                'unit' => 'pcs',
                'price' => 650000,
                'location' => 'Gudang A, Rak 3',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'SWI-0002',
                'name' => 'Switch MikroTik CRS326-24G-2S+',
                'type' => 'switch',
                'brand' => 'MikroTik',
                'model' => 'CRS326-24G-2S+',
                'description' => 'Switch 24 Port Gigabit + 2 SFP+',
                'stock_quantity' => 2,
                'min_stock' => 1,
                'unit' => 'pcs',
                'price' => 4500000,
                'location' => 'Gudang A, Rak 3',
                'active' => true,
            ],

            // Access Point
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ACC-0001',
                'name' => 'Access Point Ubiquiti UniFi AP AC Lite',
                'type' => 'access_point',
                'brand' => 'Ubiquiti',
                'model' => 'UniFi AP AC Lite',
                'description' => 'Access Point WiFi AC Dual Band',
                'stock_quantity' => 8,
                'min_stock' => 2,
                'unit' => 'pcs',
                'price' => 1800000,
                'location' => 'Gudang A, Rak 4',
                'active' => true,
            ],
            [
                'id' => Str::uuid()->toString(),
                'code' => 'ACC-0002',
                'name' => 'Access Point TP-Link EAP225',
                'type' => 'access_point',
                'brand' => 'TP-Link',
                'model' => 'EAP225',
                'description' => 'Access Point WiFi AC1200',
                'stock_quantity' => 6,
                'min_stock' => 2,
                'unit' => 'pcs',
                'price' => 1200000,
                'location' => 'Gudang A, Rak 4',
                'active' => true,
            ],
        ];

        foreach ($items as $item) {
            InventoryItem::firstOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
