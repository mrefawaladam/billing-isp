<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PackageController extends Controller
{
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Package::query();

            // Filter berdasarkan service_type
            if ($request->filled('service_type') && $request->service_type !== null && $request->service_type !== '') {
                $query->where('service_type', $request->service_type);
            }

            // Filter berdasarkan active
            if ($request->filled('active') && $request->active !== null && $request->active !== '') {
                $query->where('active', $request->active === '1' || $request->active === 1);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($package) {
                    $badge = $package->active ? 'bg-success' : 'bg-secondary';
                    $text = $package->active ? 'Aktif' : 'Tidak Aktif';
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->addColumn('price_formatted', function ($package) {
                    return 'Rp ' . number_format($package->price ?? 0, 0, ',', '.');
                })
                ->addColumn('action', function ($package) {
                    return view('features.packages.partials.action-buttons', compact('package'))->render();
                })
                ->editColumn('service_type', function ($package) {
                    return $package->service_type ?? '-';
                })
                ->editColumn('speed', function ($package) {
                    return $package->speed ?? '-';
                })
                ->editColumn('created_at', function ($package) {
                    return $package->created_at ? $package->created_at->format('d/m/Y') : '-';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('features.packages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'html' => view('features.packages.partials.form', [
                'package' => null,
                'formAction' => route('packages.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Tambah Paket Baru'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(PackageService::getCreateRules());
            $this->packageService->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil ditambahkan.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan paket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        $package->load('customers');

        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.packages.partials.show', compact('package'))->render()
            ]);
        }

        return view('features.packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        return response()->json([
            'html' => view('features.packages.partials.form', [
                'package' => $package,
                'formAction' => route('packages.update', $package),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Paket: ' . $package->name
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        try {
            $validated = $request->validate(PackageService::getUpdateRules($package));
            $this->packageService->update($package, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil diperbarui.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui paket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        try {
            // Check if package is being used by customers
            $customerCount = $package->customers()->count();
            if ($customerCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paket tidak dapat dihapus karena sedang digunakan oleh ' . $customerCount . ' pelanggan.'
                ], 422);
            }

            $this->packageService->delete($package);
            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus paket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active packages (API endpoint for dropdown/select)
     */
    public function getActivePackages()
    {
        $packages = $this->packageService->getActivePackages();
        
        return response()->json([
            'success' => true,
            'data' => $packages->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'package_code' => $package->package_code,
                    'speed' => $package->speed,
                    'service_type' => $package->service_type,
                    'price' => (float) $package->price,
                    'display_name' => $package->name . ' - ' . ($package->speed ?? '') . ' - Rp ' . number_format($package->price, 0, ',', '.'),
                ];
            })
        ]);
    }
}
