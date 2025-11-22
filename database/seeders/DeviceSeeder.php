<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->command->warn('Tidak ada pelanggan. Jalankan CustomerSeeder terlebih dahulu!');
            return;
        }

        $devices = [];

        foreach ($customers as $index => $customer) {
            // Setiap pelanggan punya minimal 1 device
            $devices[] = [
                'id' => Str::uuid()->toString(),
                'customer_id' => $customer->id,
                'name' => 'Router Utama ' . ($index + 1),
                'mac_address' => '00:1B:44:11:3A:B' . $index,
                'device_photo_url' => '/storage/devices/router-' . ($index + 1) . '.jpg',
                'location_photo_url' => '/storage/locations/location-' . ($index + 1) . '.jpg',
                'note' => 'Device utama untuk pelanggan ' . $customer->name,
            ];

            // Beberapa pelanggan punya device tambahan
            if ($index < 3) {
                $devices[] = [
                    'id' => Str::uuid()->toString(),
                    'customer_id' => $customer->id,
                    'name' => 'Access Point ' . ($index + 1),
                    'mac_address' => '00:1B:44:11:3A:C' . $index,
                    'device_photo_url' => '/storage/devices/ap-' . ($index + 1) . '.jpg',
                    'location_photo_url' => null,
                    'note' => 'Access point tambahan',
                ];
            }
        }

        foreach ($devices as $device) {
            Device::create($device);
        }

        $this->command->info('Berhasil membuat ' . count($devices) . ' perangkat sample!');
    }
}
