<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Customer;
use App\Models\Device;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = InventoryItem::select('inventory_items.*');

            // Filter berdasarkan type
            if ($request->filled('type') && $request->type !== null && $request->type !== '') {
                $query->where('type', $request->type);
            }

            // Filter berdasarkan active
            if ($request->filled('active') && $request->active !== null && $request->active !== '') {
                $query->where('active', $request->active === '1' || $request->active === 1);
            }

            // Filter low stock
            if ($request->filled('low_stock') && $request->low_stock === '1') {
                $query->whereRaw('stock_quantity <= min_stock');
            }

            return DataTables::of($query)
                ->addColumn('stock_status', function ($item) {
                    if ($item->stock_quantity == 0) {
                        return '<span class="badge bg-danger">Habis</span>';
                    } elseif ($item->isLowStock()) {
                        return '<span class="badge bg-warning">Stok Menipis</span>';
                    } else {
                        return '<span class="badge bg-success">Tersedia</span>';
                    }
                })
                ->addColumn('status_badge', function ($item) {
                    $badge = $item->active ? 'bg-success' : 'bg-secondary';
                    $text = $item->active ? 'Aktif' : 'Tidak Aktif';
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->addColumn('price_formatted', function ($item) {
                    return 'Rp ' . number_format($item->price ?? 0, 0, ',', '.');
                })
                ->addColumn('stock_info', function ($item) {
                    return $item->stock_quantity . ' ' . $item->unit . ' (Min: ' . $item->min_stock . ')';
                })
                ->addColumn('action', function ($item) {
                    return view('features.inventory.partials.action-buttons', compact('item'))->render();
                })
                ->editColumn('code', function ($item) {
                    return $item->code ?? '-';
                })
                ->editColumn('type', function ($item) {
                    return ucfirst($item->type);
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at ? $item->created_at->format('d/m/Y') : '-';
                })
                ->rawColumns(['stock_status', 'status_badge', 'action'])
                ->make(true);
        }

        // Get low stock items for alert
        $lowStockItems = $this->inventoryService->getLowStockItems();
        $outOfStockItems = $this->inventoryService->getOutOfStockItems();

        return view('features.inventory.index', compact('lowStockItems', 'outOfStockItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'html' => view('features.inventory.partials.form', [
                'item' => null,
                'formAction' => route('inventory.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Tambah Item Inventory Baru'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(InventoryService::getCreateRules());
            $this->inventoryService->create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item inventory berhasil ditambahkan.'
                ]);
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Item inventory berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryItem $inventory)
    {
        $inventory->load('usages.customer', 'usages.device', 'usages.usedBy');
        $usageHistory = $this->inventoryService->getUsageHistory($inventory, 20);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.inventory.partials.show', compact('inventory', 'usageHistory'))->render()
            ]);
        }

        return view('features.inventory.show', compact('inventory', 'usageHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryItem $inventory)
    {
        return response()->json([
            'html' => view('features.inventory.partials.form', [
                'item' => $inventory,
                'formAction' => route('inventory.update', $inventory),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Item Inventory'
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryItem $inventory)
    {
        try {
            $validated = $request->validate(InventoryService::getUpdateRules($inventory));
            $this->inventoryService->update($inventory, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item inventory berhasil diperbarui.'
                ]);
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Item inventory berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $inventory)
    {
        $this->inventoryService->delete($inventory);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item inventory berhasil dihapus.'
            ]);
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Item inventory berhasil dihapus.');
    }

    /**
     * Restock inventory item
     */
    public function restock(Request $request, InventoryItem $inventory)
    {
        try {
            $validated = $request->validate(InventoryService::getRestockRules());
            $this->inventoryService->addStock($inventory, $validated['quantity'], $validated['notes'] ?? null);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok berhasil ditambahkan.'
                ]);
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Stok berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Show form for using inventory item
     */
    public function showUseForm(InventoryItem $inventory)
    {
        $customers = Customer::where('active', true)->orderBy('name')->get();
        $devices = Device::with('customer')->orderBy('name')->get();

        return response()->json([
            'html' => view('features.inventory.partials.use-form', [
                'item' => $inventory,
                'customers' => $customers,
                'devices' => $devices,
                'formAction' => route('inventory.use', $inventory),
                'formMethod' => 'POST',
                'modalTitle' => 'Gunakan Item: ' . $inventory->name
            ])->render()
        ]);
    }

    /**
     * Use inventory item (reduce stock)
     */
    public function useItem(Request $request, InventoryItem $inventory)
    {
        try {
            $rules = InventoryService::getUseItemRules();
            // Remove inventory_item_id from rules since we get it from route
            unset($rules['inventory_item_id']);
            $validated = $request->validate($rules);
            
            $this->inventoryService->useItem(
                $inventory,
                $validated['quantity'],
                $validated['usage_type'],
                $validated['customer_id'] ?? null,
                $validated['device_id'] ?? null,
                $validated['notes'] ?? null
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item berhasil digunakan.'
                ]);
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Item berhasil digunakan.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    /**
     * Get usage history for customer
     */
    public function getCustomerUsageHistory(Customer $customer)
    {
        $usageHistory = $this->inventoryService->getCustomerUsageHistory($customer->id, 50);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.inventory.partials.customer-usage-history', compact('usageHistory', 'customer'))->render()
            ]);
        }

        return view('features.inventory.customer-usage-history', compact('usageHistory', 'customer'));
    }
}

