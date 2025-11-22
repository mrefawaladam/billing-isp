<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Display the specified device
     */
    public function show(Customer $customer, Device $device)
    {
        // Verify device belongs to customer
        if ($device->customer_id !== $customer->id) {
            abort(403, 'Perangkat tidak ditemukan untuk pelanggan ini.');
        }

        if (request()->ajax()) {
            return response()->json($device);
        }

        return response()->json($device);
    }

    /**
     * Store a newly created device
     */
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mac_address' => 'nullable|string|max:100',
            'device_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'location_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'note' => 'nullable|string|max:500',
        ]);

        $deviceData = [
            'id' => Str::uuid()->toString(),
            'customer_id' => $customer->id,
            'name' => $validated['name'],
            'mac_address' => $validated['mac_address'] ?? null,
            'note' => $validated['note'] ?? null,
        ];

        // Handle device photo upload
        if ($request->hasFile('device_photo')) {
            $devicePhoto = $request->file('device_photo');
            $filename = Str::uuid()->toString() . '.' . $devicePhoto->getClientOriginalExtension();
            $path = $devicePhoto->storeAs('devices/photos', $filename, 'public');
            $deviceData['device_photo_url'] = Storage::disk('public')->url($path);
        }

        // Handle location photo upload
        if ($request->hasFile('location_photo')) {
            $locationPhoto = $request->file('location_photo');
            $filename = Str::uuid()->toString() . '.' . $locationPhoto->getClientOriginalExtension();
            $path = $locationPhoto->storeAs('devices/locations', $filename, 'public');
            $deviceData['location_photo_url'] = Storage::disk('public')->url($path);
        }

        $device = Device::create($deviceData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perangkat berhasil ditambahkan.',
                'data' => $device
            ]);
        }

        return redirect()->back()->with('success', 'Perangkat berhasil ditambahkan.');
    }

    /**
     * Update the specified device
     */
    public function update(Request $request, Customer $customer, Device $device)
    {
        // Verify device belongs to customer
        if ($device->customer_id !== $customer->id) {
            abort(403, 'Perangkat tidak ditemukan untuk pelanggan ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mac_address' => 'nullable|string|max:100',
            'device_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'location_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'note' => 'nullable|string|max:500',
        ]);

        $deviceData = [
            'name' => $validated['name'],
            'mac_address' => $validated['mac_address'] ?? null,
            'note' => $validated['note'] ?? null,
        ];

        // Handle device photo upload
        if ($request->hasFile('device_photo')) {
            // Delete old photo if exists
            if ($device->device_photo_url) {
                $oldPath = str_replace(Storage::disk('public')->url(''), '', $device->device_photo_url);
                Storage::disk('public')->delete($oldPath);
            }

            $devicePhoto = $request->file('device_photo');
            $filename = Str::uuid()->toString() . '.' . $devicePhoto->getClientOriginalExtension();
            $path = $devicePhoto->storeAs('devices/photos', $filename, 'public');
            $deviceData['device_photo_url'] = Storage::disk('public')->url($path);
        }

        // Handle location photo upload
        if ($request->hasFile('location_photo')) {
            // Delete old photo if exists
            if ($device->location_photo_url) {
                $oldPath = str_replace(Storage::disk('public')->url(''), '', $device->location_photo_url);
                Storage::disk('public')->delete($oldPath);
            }

            $locationPhoto = $request->file('location_photo');
            $filename = Str::uuid()->toString() . '.' . $locationPhoto->getClientOriginalExtension();
            $path = $locationPhoto->storeAs('devices/locations', $filename, 'public');
            $deviceData['location_photo_url'] = Storage::disk('public')->url($path);
        }

        $device->update($deviceData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perangkat berhasil diperbarui.',
                'data' => $device->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Perangkat berhasil diperbarui.');
    }

    /**
     * Remove the specified device
     */
    public function destroy(Customer $customer, Device $device)
    {
        // Verify device belongs to customer
        if ($device->customer_id !== $customer->id) {
            abort(403, 'Perangkat tidak ditemukan untuk pelanggan ini.');
        }

        // Delete photos if exist
        if ($device->device_photo_url) {
            $oldPath = str_replace(Storage::disk('public')->url(''), '', $device->device_photo_url);
            Storage::disk('public')->delete($oldPath);
        }

        if ($device->location_photo_url) {
            $oldPath = str_replace(Storage::disk('public')->url(''), '', $device->location_photo_url);
            Storage::disk('public')->delete($oldPath);
        }

        $device->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Perangkat berhasil dihapus.'
            ]);
        }

        return redirect()->back()->with('success', 'Perangkat berhasil dihapus.');
    }
}

