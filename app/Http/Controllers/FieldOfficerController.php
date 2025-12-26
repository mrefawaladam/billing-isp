<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FieldOfficerController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Dashboard for field officer
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get customers assigned to this field officer
        $customers = Customer::whereHas('assignedUsers', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->where('active', true)
        ->with(['invoices' => function($query) {
            $query->latest()->limit(1);
        }])
        ->get();

        // Statistics
        $totalCustomers = $customers->count();
        $unpaidInvoices = Invoice::whereHas('customer.assignedUsers', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->whereIn('status', ['UNPAID', 'OVERDUE'])
        ->count();

        $paidToday = Invoice::whereHas('customer.assignedUsers', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })
        ->where('status', 'PAID')
        ->whereDate('paid_at', today())
        ->count();

        return view('features.field-officer.dashboard', compact(
            'customers',
            'totalCustomers',
            'unpaidInvoices',
            'paidToday'
        ));
    }

    /**
     * List customers assigned to field officer
     */
    public function customers(Request $request)
    {
        $user = Auth::user();

        $query = Customer::whereHas('assignedUsers', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->where('active', true)
        ->with(['invoices' => function($query) {
            $query->latest()->limit(1);
        }]);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        }

        return view('features.field-officer.customers', compact('customers'));
    }

    /**
     * Show customer detail with invoices
     */
    public function showCustomer(Customer $customer)
    {
        $user = Auth::user();

        // Verify customer is assigned to this field officer
        if (!$customer->assignedUsers->contains($user->id)) {
            abort(403, 'Anda tidak memiliki akses ke pelanggan ini.');
        }

        $customer->load(['invoices' => function($query) {
            $query->orderBy('year', 'desc')->orderBy('month', 'desc');
        }, 'invoices.payments.receivedBy']);

        return view('features.field-officer.customer-detail', compact('customer'));
    }

    /**
     * Show map view for field officer
     */
    public function map()
    {
        $user = Auth::user();

        // Get customers assigned to this field officer
        $customers = Customer::whereHas('assignedUsers', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->where('active', true)
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->get();

        return view('features.field-officer.map', compact('customers'));
    }

    /**
     * Get customers for map (AJAX)
     */
    public function getCustomersForMap()
    {
        $user = Auth::user();

        $customers = Customer::whereHas('assignedUsers', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->where('active', true)
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->with(['invoices' => function($query) {
            $query->latest()->limit(1);
        }])
        ->get()
            ->map(function($customer) {
                $latestInvoice = $customer->invoices->first();

                // Determine marker color based on invoice status
                $markerColor = 'gray';
                $invoiceStatusText = 'Belum Ada Tagihan';

                if ($latestInvoice) {
                    switch ($latestInvoice->status) {
                        case 'PAID':
                            $markerColor = 'green';
                            $invoiceStatusText = 'Sudah Dibayar';
                            break;
                        case 'OVERDUE':
                            $markerColor = 'red';
                            $invoiceStatusText = 'Terlambat';
                            break;
                        case 'UNPAID':
                            $dueDate = \Carbon\Carbon::parse($latestInvoice->due_date);
                            if ($dueDate->isPast()) {
                                $markerColor = 'red';
                                $invoiceStatusText = 'Belum Dibayar / Telat';
                            } else {
                                $markerColor = 'yellow';
                                $invoiceStatusText = 'Jatuh Tempo';
                            }
                            break;
                    }
                }

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'code' => $customer->customer_code,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'lat' => (float) $customer->lat,
                    'lng' => (float) $customer->lng,
                    'total_fee' => $customer->total_fee,
                    'marker_color' => $markerColor,
                    'invoice_status_text' => $invoiceStatusText,
                    'latest_invoice' => $latestInvoice ? [
                        'id' => $latestInvoice->id,
                        'invoice_number' => $latestInvoice->invoice_number,
                        'due_date' => $latestInvoice->due_date ? $latestInvoice->due_date->format('d/m/Y') : null,
                        'total_amount' => $latestInvoice->total_amount,
                        'status' => $latestInvoice->status,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    /**
     * Show payment form for invoice
     */
    public function showPaymentForm(Invoice $invoice)
    {
        $user = Auth::user();

        // Verify invoice belongs to customer assigned to this field officer
        if (!$invoice->customer->assignedUsers->contains($user->id)) {
            abort(403, 'Anda tidak memiliki akses ke tagihan ini.');
        }

        $invoice->load('customer', 'payments.receivedBy');

        return view('features.field-officer.payment-form', compact('invoice'));
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        // Verify invoice belongs to customer assigned to this field officer
        if ($invoice->customer->assigned_to !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke tagihan ini.'
            ], 403);
        }

        // Check if already paid
        if ($invoice->status === 'PAID') {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini sudah dibayar.'
            ], 400);
        }

        try {
            // Merge invoice_id from route parameter if not in request
            $request->merge(['invoice_id' => $invoice->id]);

            $validated = $request->validate(PaymentService::getCreateRules());

            $proofFile = $request->hasFile('transfer_proof') ? $request->file('transfer_proof') : null;
            $fieldPhoto = $request->hasFile('field_photo') ? $request->file('field_photo') : null;

            if ($validated['method'] === 'cash') {
                $payment = $this->paymentService->markAsPaidCash(
                    $invoice,
                    $validated['note'] ?? null,
                    $fieldPhoto
                );
            } else {
                if (!$proofFile) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bukti transfer wajib diunggah untuk pembayaran transfer.'
                    ], 422);
                }
                $payment = $this->paymentService->markAsPaidTransfer(
                    $invoice,
                    $proofFile,
                    $validated['note'] ?? null,
                    $fieldPhoto
                );
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran berhasil dicatat.',
                    'data' => $payment
                ]);
            }

            return redirect()->route('field-officer.customers')
                ->with('success', 'Pembayaran berhasil dicatat.');
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
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}

