<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FieldOfficerApiController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get dashboard statistics
     * GET /api/field-officer/dashboard
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();

            // Get customers assigned to this field officer
            $customers = Customer::where('assigned_to', $user->id)
                ->where('active', true)
                ->get();

            // Statistics
            $totalCustomers = $customers->count();

            $unpaidInvoices = Invoice::whereHas('customer', function($query) use ($user) {
                $query->where('assigned_to', $user->id);
            })
            ->whereIn('status', ['UNPAID', 'OVERDUE'])
            ->count();

            $paidToday = Invoice::whereHas('customer', function($query) use ($user) {
                $query->where('assigned_to', $user->id);
            })
            ->where('status', 'PAID')
            ->whereDate('paid_at', today())
            ->count();

            return ApiResponse::success([
                'total_customers' => $totalCustomers,
                'unpaid_invoices' => $unpaidInvoices,
                'paid_today' => $paidToday,
            ], 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve dashboard data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get list of customers assigned to field officer
     * GET /api/field-officer/customers
     */
    public function customers(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Customer::where('assigned_to', $user->id)
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

            $customers = $query->orderBy('name')->paginate($request->get('per_page', 20));

            return ApiResponse::success($customers, 'Customers retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve customers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get customer detail with invoices
     * GET /api/field-officer/customers/{customer}
     */
    public function showCustomer(Customer $customer)
    {
        try {
            $user = Auth::user();

            // Verify customer is assigned to this field officer
            if ($customer->assigned_to !== $user->id) {
                return ApiResponse::error('Anda tidak memiliki akses ke pelanggan ini.', 403);
            }

            $customer->load(['invoices' => function($query) {
                $query->orderBy('year', 'desc')->orderBy('month', 'desc');
            }, 'invoices.payments.receivedBy']);

            return ApiResponse::success($customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get customers for map
     * GET /api/field-officer/map/customers
     */
    public function getCustomersForMap()
    {
        try {
            $user = Auth::user();

            $customers = Customer::where('assigned_to', $user->id)
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

            return ApiResponse::success($customers, 'Map customers retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve map customers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get invoice detail
     * GET /api/field-officer/invoices/{invoice}
     */
    public function showInvoice(Invoice $invoice)
    {
        try {
            $user = Auth::user();

            // Verify invoice belongs to customer assigned to this field officer
            if ($invoice->customer->assigned_to !== $user->id) {
                return ApiResponse::error('Anda tidak memiliki akses ke tagihan ini.', 403);
            }

            $invoice->load('customer', 'payments.receivedBy');

            return ApiResponse::success($invoice, 'Invoice retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve invoice: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process payment
     * POST /api/field-officer/invoices/{invoice}/pay
     */
    public function processPayment(Request $request, Invoice $invoice)
    {
        try {
            $user = Auth::user();

            // Verify invoice belongs to customer assigned to this field officer
            if ($invoice->customer->assigned_to !== $user->id) {
                return ApiResponse::error('Anda tidak memiliki akses ke tagihan ini.', 403);
            }

            // Check if already paid
            if ($invoice->status === 'PAID') {
                return ApiResponse::error('Tagihan ini sudah dibayar.', 400);
            }

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
                    return ApiResponse::error('Bukti transfer wajib diunggah untuk pembayaran transfer.', 422);
                }
                $payment = $this->paymentService->markAsPaidTransfer(
                    $invoice,
                    $proofFile,
                    $validated['note'] ?? null,
                    $fieldPhoto
                );
            }

            return ApiResponse::success($payment->load('invoice', 'customer', 'receivedBy'), 'Pembayaran berhasil dicatat.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memproses pembayaran: ' . $e->getMessage(), 500);
        }
    }
}

