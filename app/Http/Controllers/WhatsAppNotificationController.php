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
}

