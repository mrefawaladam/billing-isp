<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\WaNotification;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class WhatsAppNotificationController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Display notification history
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = WaNotification::with(['customer', 'invoice'])->select('wa_notifications.*');

            // Filter berdasarkan status
            if ($request->filled('status') && $request->status !== null && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan customer
            if ($request->filled('customer_id') && $request->customer_id !== null && $request->customer_id !== '') {
                $query->where('customer_id', $request->customer_id);
            }

            // Filter berdasarkan tanggal
            if ($request->filled('date_from') && $request->date_from !== null && $request->date_from !== '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to') && $request->date_to !== null && $request->date_to !== '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addColumn('customer_name', function ($notification) {
                    return $notification->customer ? $notification->customer->name : '-';
                })
                ->addColumn('invoice_number', function ($notification) {
                    return $notification->invoice ? $notification->invoice->invoice_number : '-';
                })
                ->addColumn('status_badge', function ($notification) {
                    $badges = [
                        'sent' => 'bg-success',
                        'failed' => 'bg-danger',
                        'pending' => 'bg-warning',
                    ];
                    $badge = $badges[$notification->status] ?? 'bg-secondary';
                    $text = ucfirst($notification->status);
                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                })
                ->addColumn('message_preview', function ($notification) {
                    $preview = Str::limit($notification->message_text, 50);
                    return '<span title="' . htmlspecialchars($notification->message_text) . '">' . $preview . '</span>';
                })
                ->addColumn('action', function ($notification) {
                    return view('features.whatsapp.partials.action-buttons', compact('notification'))->render();
                })
                ->editColumn('sent_at', function ($notification) {
                    return $notification->sent_at ? $notification->sent_at->format('d/m/Y H:i') : '-';
                })
                ->editColumn('created_at', function ($notification) {
                    return $notification->created_at ? $notification->created_at->format('d/m/Y H:i') : '-';
                })
                ->rawColumns(['status_badge', 'message_preview', 'action'])
                ->make(true);
        }

        $customers = Customer::where('active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name')
            ->get();

        return view('features.whatsapp.index', compact('customers'));
    }

    /**
     * Show form to send WhatsApp message
     */
    public function create(Request $request)
    {
        $customers = Customer::where('active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('name')
            ->get();

        $invoices = Invoice::with('customer')
            ->whereHas('customer', function($query) {
                $query->where('active', true)
                      ->whereNotNull('phone')
                      ->where('phone', '!=', '');
            })
            ->where('status', '!=', 'PAID')
            ->orderBy('due_date', 'desc')
            ->get();

        // Pre-select customer or invoice if provided
        $selectedCustomer = $request->get('customer_id') ? Customer::find($request->get('customer_id')) : null;
        $selectedInvoice = $request->get('invoice_id') ? Invoice::with('customer')->find($request->get('invoice_id')) : null;

        return response()->json([
            'html' => view('features.whatsapp.partials.send-form', [
                'customers' => $customers,
                'invoices' => $invoices,
                'selectedCustomer' => $selectedCustomer,
                'selectedInvoice' => $selectedInvoice,
            ])->render()
        ]);
    }

    /**
     * Send WhatsApp message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:5000',
            'customer_id' => 'nullable|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        try {
            // Send message via Fonnte
            $result = $this->fonnteService->sendMessage($validated['phone'], $validated['message']);

            // Save notification record
            $notification = WaNotification::create([
                'id' => Str::uuid()->toString(),
                'invoice_id' => $validated['invoice_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'phone' => $validated['phone'],
                'template_name' => 'manual',
                'message_text' => $validated['message'],
                'scheduled_at' => now(),
                'sent_at' => $result['success'] ? now() : null,
                'status' => $result['success'] ? 'sent' : 'failed',
                'provider_response' => json_encode($result['data'] ?? []),
                'error_message' => $result['success'] ? null : $result['message'],
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Pesan berhasil dikirim' : $result['message'],
                    'notification' => $notification
                ]);
            }

            return redirect()->route('whatsapp.index')
                ->with($result['success'] ? 'success' : 'error', $result['success'] ? 'Pesan berhasil dikirim' : $result['message']);

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Send invoice notification
     */
    public function sendInvoice(Request $request, Invoice $invoice)
    {
        $customer = $invoice->customer;

        if (!$customer->phone) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer tidak memiliki nomor telepon'
                ], 422);
            }

            return redirect()->back()
                ->with('error', 'Customer tidak memiliki nomor telepon');
        }

        // Determine template
        $template = $invoice->due_date < now() ? 'overdue' : 'due_date';
        $message = $this->generateInvoiceMessage($invoice, $template);

        try {
            // Send via Fonnte
            $result = $this->fonnteService->sendMessage($customer->phone, $message);

            // Save notification record
            $notification = WaNotification::create([
                'id' => Str::uuid()->toString(),
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'phone' => $customer->phone,
                'template_name' => $template,
                'message_text' => $message,
                'scheduled_at' => now(),
                'sent_at' => $result['success'] ? now() : null,
                'status' => $result['success'] ? 'sent' : 'failed',
                'provider_response' => json_encode($result['data'] ?? []),
                'error_message' => $result['success'] ? null : $result['message'],
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Notifikasi tagihan berhasil dikirim' : $result['message'],
                    'notification' => $notification
                ]);
            }

            return redirect()->route('whatsapp.index')
                ->with($result['success'] ? 'success' : 'error', $result['success'] ? 'Notifikasi tagihan berhasil dikirim' : $result['message']);

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show notification detail
     */
    public function show(WaNotification $whatsapp)
    {
        $whatsapp->load(['customer', 'invoice']);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.whatsapp.partials.show', compact('whatsapp'))->render()
            ]);
        }

        return view('features.whatsapp.show', compact('whatsapp'));
    }

    /**
     * Resend failed notification
     */
    public function resend($whatsapp)
    {
        try {
            // Handle both model binding and ID
            if (!($whatsapp instanceof WaNotification)) {
                $whatsapp = WaNotification::findOrFail($whatsapp);
            }

            // Validate phone number
            if (!$whatsapp->phone) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nomor telepon tidak ditemukan'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Nomor telepon tidak ditemukan');
            }

            // Send message via Fonnte
            $result = $this->fonnteService->sendMessage($whatsapp->phone, $whatsapp->message_text);

            // Update notification record
            $whatsapp->update([
                'sent_at' => $result['success'] ? now() : null,
                'status' => $result['success'] ? 'sent' : 'failed',
                'provider_response' => json_encode($result['data'] ?? []),
                'error_message' => $result['success'] ? null : $result['message'],
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Pesan berhasil dikirim ulang' : ($result['message'] ?? 'Gagal mengirim pesan'),
                    'data' => $result['data'] ?? null
                ]);
            }

            return redirect()->route('whatsapp.index')
                ->with($result['success'] ? 'success' : 'error', $result['success'] ? 'Pesan berhasil dikirim ulang' : ($result['message'] ?? 'Gagal mengirim pesan'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notifikasi tidak ditemukan'
                ], 404);
            }
            return redirect()->back()->with('error', 'Notifikasi tidak ditemukan');
        } catch (\Exception $e) {
            \Log::error('WhatsApp Resend Error: ' . $e->getMessage(), [
                'notification_id' => is_string($whatsapp) ? $whatsapp : ($whatsapp->id ?? null),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Generate invoice message
     */
    private function generateInvoiceMessage(Invoice $invoice, string $template): string
    {
        $customer = $invoice->customer;
        $dueDate = $invoice->due_date->format('d/m/Y');
        $amount = number_format($invoice->total_amount, 0, ',', '.');
        $daysOverdue = $invoice->due_date->diffInDays(now());

        switch ($template) {
            case 'overdue':
                $message = "⚠️ *PEMBERITAHUAN TAGIHAN TERLAMBAT*\n\n";
                $message .= "Yth. {$customer->name}\n\n";
                $message .= "Tagihan Anda sudah *TERLAMBAT {$daysOverdue} hari*.\n\n";
                $message .= "📄 *No. Tagihan:* {$invoice->invoice_number}\n";
                $message .= "📅 *Jatuh Tempo:* {$dueDate}\n";
                $message .= "💰 *Total Tagihan:* Rp {$amount}\n\n";
                $message .= "Mohon segera lakukan pembayaran untuk menghindari gangguan layanan.\n\n";
                $message .= "Terima kasih.";
                break;

            case 'due_date':
            default:
                $message = "📋 *PEMBERITAHUAN TAGIHAN*\n\n";
                $message .= "Yth. {$customer->name}\n\n";
                $message .= "Tagihan Anda *jatuh tempo hari ini*.\n\n";
                $message .= "📄 *No. Tagihan:* {$invoice->invoice_number}\n";
                $message .= "📅 *Jatuh Tempo:* {$dueDate}\n";
                $message .= "💰 *Total Tagihan:* Rp {$amount}\n\n";
                $message .= "Mohon segera lakukan pembayaran.\n\n";
                $message .= "Terima kasih.";
                break;
        }

        return $message;
    }

    /**
     * Show form for bulk send by region
     */
    public function bulkByRegion(Request $request)
    {
        // Get unique values for dropdowns
        $kabupatens = Customer::where('active', true)
            ->whereNotNull('kabupaten')
            ->where('kabupaten', '!=', '')
            ->distinct()
            ->orderBy('kabupaten')
            ->pluck('kabupaten')
            ->toArray();

        // Get kecamatans and kelurahans (will be filtered via AJAX)
        $kecamatans = [];
        $kelurahans = [];

        // If kabupaten is provided, filter kecamatans
        if ($request->has('kabupaten') && $request->kabupaten) {
            $kecamatans = Customer::where('active', true)
                ->where('kabupaten', $request->kabupaten)
                ->whereNotNull('kecamatan')
                ->where('kecamatan', '!=', '')
                ->distinct()
                ->orderBy('kecamatan')
                ->pluck('kecamatan')
                ->toArray();
        }

        // If kecamatan is provided, filter kelurahans
        if ($request->has('kecamatan') && $request->kecamatan) {
            $kelurahans = Customer::where('active', true)
                ->where('kabupaten', $request->kabupaten ?? '')
                ->where('kecamatan', $request->kecamatan)
                ->whereNotNull('kelurahan')
                ->where('kelurahan', '!=', '')
                ->distinct()
                ->orderBy('kelurahan')
                ->pluck('kelurahan')
                ->toArray();
        }

        // If AJAX request for filtering
        if ($request->ajax() && $request->has('filter')) {
            if ($request->filter === 'kecamatan' && $request->kabupaten) {
                return response()->json([
                    'kecamatans' => Customer::where('active', true)
                        ->where('kabupaten', $request->kabupaten)
                        ->whereNotNull('kecamatan')
                        ->where('kecamatan', '!=', '')
                        ->distinct()
                        ->orderBy('kecamatan')
                        ->pluck('kecamatan')
                        ->toArray()
                ]);
            } elseif ($request->filter === 'kelurahan' && $request->kecamatan) {
                return response()->json([
                    'kelurahans' => Customer::where('active', true)
                        ->where('kabupaten', $request->kabupaten ?? '')
                        ->where('kecamatan', $request->kecamatan)
                        ->whereNotNull('kelurahan')
                        ->where('kelurahan', '!=', '')
                        ->distinct()
                        ->orderBy('kelurahan')
                        ->pluck('kelurahan')
                        ->toArray()
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('features.whatsapp.partials.bulk-by-region-form', [
                    'kabupatens' => $kabupatens,
                    'kecamatans' => $kecamatans,
                    'kelurahans' => $kelurahans,
                ])->render()
            ]);
        }

        return view('features.whatsapp.bulk-by-region', [
            'kabupatens' => $kabupatens,
            'kecamatans' => $kecamatans,
            'kelurahans' => $kelurahans,
        ]);
    }

    /**
     * Send bulk WhatsApp by region
     */
    public function sendBulkByRegion(Request $request)
    {
        $validated = $request->validate([
            'filter_type' => 'required|in:kabupaten,kecamatan,kelurahan',
            'kabupaten' => 'required_if:filter_type,kabupaten,kecamatan,kelurahan|string|max:255',
            'kecamatan' => 'required_if:filter_type,kecamatan,kelurahan|nullable|string|max:255',
            'kelurahan' => 'required_if:filter_type,kelurahan|nullable|string|max:255',
            'invoice_year' => 'required|integer|min:2020|max:2100',
            'invoice_month' => 'required|integer|min:1|max:12',
            'include_unpaid_previous' => 'nullable|boolean',
        ]);

        try {
            // Query customers based on filter
            $query = Customer::where('active', true)
                ->whereNotNull('phone')
                ->where('phone', '!=', '');

            // Apply filter based on type
            switch ($validated['filter_type']) {
                case 'kelurahan':
                    $query->where('kabupaten', $validated['kabupaten'])
                          ->where('kecamatan', $validated['kecamatan'])
                          ->where('kelurahan', $validated['kelurahan']);
                    break;
                case 'kecamatan':
                    $query->where('kabupaten', $validated['kabupaten'])
                          ->where('kecamatan', $validated['kecamatan']);
                    break;
                case 'kabupaten':
                default:
                    $query->where('kabupaten', $validated['kabupaten']);
                    break;
            }

            $customers = $query->get();

            if ($customers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pelanggan yang ditemukan dengan filter yang dipilih.'
                ], 422);
            }

            $year = $validated['invoice_year'];
            $month = $validated['invoice_month'];
            $includeUnpaidPrevious = $request->has('include_unpaid_previous') && $request->input('include_unpaid_previous');

            $results = [
                'total_customers' => $customers->count(),
                'sent' => 0,
                'failed' => 0,
                'no_invoice' => 0,
                'details' => []
            ];

            // Get delay interval from config (default 3 seconds)
            $delayInterval = (int) config('services.fonnte.delay_between_messages', 3);
            $lastSentTime = 0;
            $processedCount = 0;
            $totalCustomers = $customers->count();

            // Process each customer
            foreach ($customers as $customer) {
                $processedCount++;
                
                // Anti-ban: Add delay between messages
                if ($lastSentTime > 0) {
                    $timeSinceLastSent = time() - $lastSentTime;
                    if ($timeSinceLastSent < $delayInterval) {
                        $waitTime = $delayInterval - $timeSinceLastSent;
                        sleep($waitTime);
                    }
                }

                // Get invoices for the specified month
                $invoices = Invoice::where('customer_id', $customer->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('status', '!=', 'PAID')
                    ->get();

                // If include unpaid previous, get all unpaid invoices before the specified month
                if ($includeUnpaidPrevious) {
                    $previousInvoices = Invoice::where('customer_id', $customer->id)
                        ->where(function($q) use ($year, $month) {
                            $q->where('year', '<', $year)
                              ->orWhere(function($q2) use ($year, $month) {
                                  $q2->where('year', $year)
                                     ->where('month', '<', $month);
                              });
                        })
                        ->where('status', '!=', 'PAID')
                        ->get();
                    
                    $invoices = $invoices->merge($previousInvoices);
                }

                if ($invoices->isEmpty()) {
                    $results['no_invoice']++;
                    $results['details'][] = [
                        'customer' => $customer->name,
                        'phone' => $customer->phone,
                        'status' => 'no_invoice',
                        'message' => 'Tidak ada tagihan untuk bulan yang dipilih'
                    ];
                    continue;
                }

                // Generate message with all invoices
                $message = $this->generateBulkInvoiceMessage($customer, $invoices);

                // Send via Fonnte (FonnteService already has delay, but we add extra safety)
                $result = $this->fonnteService->sendMessage($customer->phone, $message);
                
                // Update last sent time for anti-ban
                $lastSentTime = time();

                // Save notification records for each invoice
                foreach ($invoices as $invoice) {
                    WaNotification::create([
                        'id' => Str::uuid()->toString(),
                        'invoice_id' => $invoice->id,
                        'customer_id' => $customer->id,
                        'phone' => $customer->phone,
                        'template_name' => 'bulk_by_region',
                        'message_text' => $message,
                        'scheduled_at' => now(),
                        'sent_at' => $result['success'] ? now() : null,
                        'status' => $result['success'] ? 'sent' : 'failed',
                        'provider_response' => json_encode($result['data'] ?? []),
                        'error_message' => $result['success'] ? null : $result['message'],
                    ]);
                }

                if ($result['success']) {
                    $results['sent']++;
                    $results['details'][] = [
                        'customer' => $customer->name,
                        'phone' => $customer->phone,
                        'status' => 'sent',
                        'invoice_count' => $invoices->count(),
                        'message' => 'Berhasil dikirim'
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'customer' => $customer->name,
                        'phone' => $customer->phone,
                        'status' => 'failed',
                        'invoice_count' => $invoices->count(),
                        'message' => $result['message'] ?? 'Gagal mengirim'
                    ];
                }
                
                // Log progress for debugging
                \Log::info("Bulk WhatsApp Progress: {$processedCount}/{$totalCustomers} customers processed", [
                    'customer' => $customer->name,
                    'status' => $result['success'] ? 'sent' : 'failed'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Pengiriman selesai. Berhasil: {$results['sent']}, Gagal: {$results['failed']}, Tidak ada tagihan: {$results['no_invoice']}",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            \Log::error('WhatsApp Bulk By Region Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate message for bulk invoice notification
     */
    private function generateBulkInvoiceMessage(Customer $customer, $invoices): string
    {
        $message = "📋 *PEMBERITAHUAN TAGIHAN*\n\n";
        $message .= "Yth. {$customer->name}\n\n";
        
        if ($invoices->count() > 1) {
            $message .= "Anda memiliki *{$invoices->count()} tagihan* yang belum dibayar:\n\n";
        } else {
            $message .= "Tagihan Anda:\n\n";
        }

        $totalAmount = 0;
        foreach ($invoices as $invoice) {
            $dueDate = $invoice->due_date->format('d/m/Y');
            $amount = number_format($invoice->total_amount, 0, ',', '.');
            $status = $invoice->status === 'OVERDUE' ? '⚠️ TERLAMBAT' : '';
            
            $monthName = \Carbon\Carbon::create($invoice->year, $invoice->month, 1)->locale('id')->isoFormat('MMMM YYYY');
            
            $message .= "📄 *{$invoice->invoice_number}*\n";
            $message .= "   Periode: {$monthName}\n";
            $message .= "   Jatuh Tempo: {$dueDate} {$status}\n";
            $message .= "   Total: Rp {$amount}\n\n";
            
            $totalAmount += $invoice->total_amount;
        }

        $totalFormatted = number_format($totalAmount, 0, ',', '.');
        $message .= "💰 *TOTAL SEMUA TAGIHAN: Rp {$totalFormatted}*\n\n";
        $message .= "Mohon segera lakukan pembayaran untuk menghindari gangguan layanan.\n\n";
        $message .= "Terima kasih.";

        return $message;
    }
}

