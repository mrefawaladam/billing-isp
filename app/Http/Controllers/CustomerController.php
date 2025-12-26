<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Customer::with('assignedUsers')->select('customers.*');

            // Filter berdasarkan type
            if ($request->filled('type') && $request->type !== null && $request->type !== '') {
                $query->where('type', $request->type);
            }

            // Filter berdasarkan assigned_to (using pivot table)
            if ($request->filled('assigned_to') && $request->assigned_to !== null && $request->assigned_to !== '') {
                $query->whereHas('assignedUsers', function ($q) use ($request) {
                    $q->where('users.id', $request->assigned_to);
                });
            }

            // Filter berdasarkan active
            if ($request->filled('active') && $request->active !== null && $request->active !== '') {
                $query->where('active', $request->active === '1' || $request->active === 1);
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function ($customer) {
                    return '<input type="checkbox" class="form-check-input customer-checkbox" value="' . $customer->id . '">';
                })
                ->addColumn('assigned_user', function ($customer) {
                    if ($customer->assignedUsers->count() > 0) {
                        $names = $customer->assignedUsers->pluck('name')->toArray();
                        return implode(', ', $names);
                    }
                    return '-';
                })
                ->addColumn('type_badge', function ($customer) {
                    $badges = [
                        'rumahan' => 'bg-primary',
                        'kantor' => 'bg-success',
                        'sekolah' => 'bg-info',
                        'free' => 'bg-secondary',
                    ];
                    $badge = $badges[$customer->type] ?? 'bg-secondary';
                    return '<span class="badge ' . $badge . '">' . ucfirst($customer->type) . '</span>';
                })
                ->addColumn('status_badge', function ($customer) {
                    $badge = $customer->active ? 'bg-success' : 'bg-danger';
                    $text = $customer->active ? 'Aktif' : 'Tidak Aktif';
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->addColumn('total_fee_formatted', function ($customer) {
                    return 'Rp ' . number_format($customer->total_fee ?? 0, 0, ',', '.');
                })
                ->addColumn('action', function ($customer) {
                    return view('features.customers.partials.action-buttons', compact('customer'))->render();
                })
                ->editColumn('customer_code', function ($customer) {
                    return $customer->customer_code ?? '-';
                })
                ->editColumn('phone', function ($customer) {
                    return $customer->phone ?? '-';
                })
                ->editColumn('created_at', function ($customer) {
                    return $customer->created_at ? $customer->created_at->format('d/m/Y') : '-';
                })
                ->filterColumn('assigned_user', function ($query, $keyword) {
                    $query->whereHas('assignedUsers', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['checkbox', 'type_badge', 'status_badge', 'action'])
                ->make(true);
        }

        // Get all users that can be assigned (admin, manager, staff/field officer)
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager', 'staff']);
        })->orderBy('name')->get();

        return view('features.customers.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all users that can be assigned (admin, manager, staff/field officer)
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager', 'staff']);
        })->orderBy('name')->get();

        // Get active packages
        $packages = Package::where('active', true)->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'html' => view('features.customers.partials.form', [
                'users' => $users,
                'packages' => $packages,
                'customer' => null,
                'formAction' => route('customers.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Tambah Pelanggan Baru'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(CustomerService::getCreateRules());

            $housePhoto = $request->hasFile('house_photo') ? $request->file('house_photo') : null;
            $identityPhoto = $request->hasFile('identity_photo') ? $request->file('identity_photo') : null;
            $this->customerService->create($validated, $housePhoto, $identityPhoto);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pelanggan berhasil ditambahkan.'
                ]);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Pelanggan berhasil ditambahkan.');
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
    public function show(Customer $customer)
    {
        $customer->load('assignedUsers', 'devices', 'invoices');
        // Get all users that can be assigned (admin, manager, staff/field officer)
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager', 'staff']);
        })->orderBy('name')->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.customers.partials.show', compact('customer', 'users'))->render()
            ]);
        }

        return redirect()->route('customers.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        // Get all users that can be assigned (admin, manager, staff/field officer)
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager', 'staff']);
        })->orderBy('name')->get();

        // Get active packages
        $packages = Package::where('active', true)->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([
            'html' => view('features.customers.partials.form', [
                'users' => $users,
                'packages' => $packages,
                'customer' => $customer,
                'formAction' => route('customers.update', $customer),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Pelanggan'
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate(CustomerService::getUpdateRules($customer));

            // Pastikan discount selalu ada dan tidak null
            if (!isset($validated['discount']) || $validated['discount'] === null || $validated['discount'] === '') {
                $validated['discount'] = 0;
            }

            $housePhoto = $request->hasFile('house_photo') ? $request->file('house_photo') : null;
            $identityPhoto = $request->hasFile('identity_photo') ? $request->file('identity_photo') : null;
            $this->customerService->update($customer, $validated, $housePhoto, $identityPhoto);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pelanggan berhasil diperbarui.'
                ]);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Pelanggan berhasil diperbarui.');
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
     * Show device management for customer
     */
    public function devices(Customer $customer)
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.customers.partials.device-management', [
                    'customerId' => $customer->id
                ])->render()
            ]);
        }

        return view('features.customers.partials.device-management', [
            'customerId' => $customer->id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $this->customerService->delete($customer);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil dihapus.'
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    /**
     * Bulk assign customers to staff
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'required|uuid|exists:customers,id',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'nullable|uuid|exists:users,id',
            'assigned_to' => 'nullable|uuid|exists:users,id', // Backward compatibility
        ]);

        $customerIds = $request->customer_ids;
        $assignedUsers = $request->assigned_users ?? [];
        
        // Backward compatibility: handle single assigned_to
        if (empty($assignedUsers) && $request->filled('assigned_to')) {
            $assignedUsers = [$request->assigned_to];
        }
        
        // Filter out null/empty values
        $assignedUsers = array_filter($assignedUsers);

        $customers = Customer::whereIn('id', $customerIds)->get();
        
        foreach ($customers as $customer) {
            $customer->assignedUsers()->sync($assignedUsers);
        }

        $count = count($customerIds);
        $userCount = count($assignedUsers);
        $message = !empty($assignedUsers)
            ? "Berhasil menugaskan {$count} pelanggan ke {$userCount} staff."
            : "Berhasil menghapus penugasan dari {$count} pelanggan.";

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', $message);
    }
}

