<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\WaNotification;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendWhatsAppNotification extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:send-notifications
                            {--due-date : Kirim notifikasi untuk invoice yang jatuh tempo hari ini}
                            {--overdue : Kirim notifikasi untuk invoice yang sudah lewat jatuh tempo}
                            {--customer= : Kirim notifikasi untuk customer tertentu (customer_id)}
                            {--invoice= : Kirim notifikasi untuk invoice tertentu (invoice_id)}
                            {--clear-cache : Clear rate limit cache sebelum mengirim}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi WhatsApp via Fonnte (dengan anti ban)';

    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        parent::__construct();
        $this->fonnteService = $fonnteService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Memulai pengiriman notifikasi WhatsApp...');

        // Check API key
        if (!config('services.fonnte.api_key')) {
            $this->error('❌ FONNTE_API_KEY tidak ditemukan di .env');
            return 1;
        }

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $this->info('🧹 Membersihkan rate limit cache...');
            \Illuminate\Support\Facades\Cache::flush();
            $this->info('✅ Cache berhasil dibersihkan');
        }

        $sentCount = 0;
        $failedCount = 0;

        // Handle different options
        if ($this->option('due-date')) {
            $this->info('📅 Mencari invoice yang jatuh tempo hari ini...');
            $result = $this->sendDueDateNotifications();
            $sentCount += $result['sent'];
            $failedCount += $result['failed'];
        } elseif ($this->option('overdue')) {
            $this->info('⏰ Mencari invoice yang sudah lewat jatuh tempo...');
            $result = $this->sendOverdueNotifications();
            $sentCount += $result['sent'];
            $failedCount += $result['failed'];
        } elseif ($this->option('customer')) {
            $this->info('👤 Mengirim notifikasi untuk customer tertentu...');
            $result = $this->sendCustomerNotification($this->option('customer'));
            $sentCount += $result['sent'];
            $failedCount += $result['failed'];
        } elseif ($this->option('invoice')) {
            $this->info('📄 Mengirim notifikasi untuk invoice tertentu...');
            $result = $this->sendInvoiceNotification($this->option('invoice'));
            $sentCount += $result['sent'];
            $failedCount += $result['failed'];
        } else {
            // Default: send due date notifications
            $this->info('📅 Mencari invoice yang jatuh tempo hari ini...');
            $result = $this->sendDueDateNotifications();
            $sentCount += $result['sent'];
            $failedCount += $result['failed'];
        }

        $this->info("✅ Selesai! Berhasil: {$sentCount}, Gagal: {$failedCount}");
        return 0;
    }

    /**
     * Send notifications for invoices due today
     * Cek berdasarkan invoice_due_day customer dan due_date invoice
     */
    protected function sendDueDateNotifications(): array
    {
        $today = Carbon::today();
        $todayDay = $today->day; // Hari dalam bulan (1-31)
        $sent = 0;
        $failed = 0;

        // Get invoices yang jatuh tempo hari ini
        // Logic:
        // 1. Invoice yang due_date = hari ini
        // 2. ATAU Invoice terbaru yang belum dibayar dari customer yang invoice_due_day = hari ini
        $invoices = collect();

        // 1. Invoice yang due_date-nya hari ini
        $invoicesByDueDate = Invoice::with('customer')
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', $today)
            ->whereHas('customer', function($query) {
                $query->where('active', true)
                      ->whereNotNull('phone')
                      ->where('phone', '!=', '');
            })
            ->get();

        $invoices = $invoices->merge($invoicesByDueDate);

        // 2. Invoice terbaru yang belum dibayar dari customer yang invoice_due_day = hari ini
        $customersWithDueDay = Customer::where('invoice_due_day', $todayDay)
            ->where('active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        foreach ($customersWithDueDay as $customer) {
            // Ambil invoice terbaru yang belum dibayar
            $latestInvoice = $customer->invoices()
                ->with('customer')
                ->where('status', '!=', 'PAID')
                ->orderBy('due_date', 'desc')
                ->first();

            if ($latestInvoice && !$invoices->contains('id', $latestInvoice->id)) {
                // Pastikan invoice ini belum ada di list
                // Dan pastikan due_date invoice sudah lewat atau hari ini
                if ($latestInvoice->due_date <= $today) {
                    $invoices->push($latestInvoice);
                }
            }
        }

        $this->info("📊 Ditemukan {$invoices->count()} invoice yang jatuh tempo hari ini");

        // Debug: Show found invoices
        if ($invoices->count() > 0) {
            $this->info("📋 Invoice yang ditemukan:");
            foreach ($invoices as $invoice) {
                $this->line("  - {$invoice->invoice_number} | Customer: {$invoice->customer->name} | Due: {$invoice->due_date->format('d/m/Y')} | Phone: {$invoice->customer->phone}");
            }
        } else {
            // Debug: Check why no invoices found
            $this->warn("🔍 Debug: Mencari invoice...");
            $dueDateCount = Invoice::where('status', '!=', 'PAID')
                ->whereDate('due_date', $today)
                ->count();
            $this->line("  Invoice dengan due_date hari ini: {$dueDateCount}");

            $customerWithDueDay = Customer::where('invoice_due_day', $todayDay)
                ->where('active', true)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->count();
            $this->line("  Customer dengan invoice_due_day = {$todayDay}: {$customerWithDueDay}");

            if ($customerWithDueDay > 0) {
                $customers = Customer::where('invoice_due_day', $todayDay)
                    ->where('active', true)
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get();
                foreach ($customers as $customer) {
                    $invoiceCount = $customer->invoices()
                        ->where('status', '!=', 'PAID')
                        ->where(function($q) use ($today) {
                            $q->where(function($query) use ($today) {
                                $query->where('year', $today->year)
                                      ->where('month', $today->month);
                            })
                            ->orWhere(function($query) use ($today) {
                                $lastMonth = $today->copy()->subMonth();
                                $query->where('year', $lastMonth->year)
                                      ->where('month', $lastMonth->month);
                            });
                        })
                        ->count();
                    $this->line("    - {$customer->name} (Phone: {$customer->phone}): {$invoiceCount} invoice belum dibayar");
                }
            }
        }

        foreach ($invoices as $invoice) {
            // Check if already sent today
            $alreadySent = WaNotification::where('invoice_id', $invoice->id)
                ->where('status', 'sent')
                ->whereDate('sent_at', $today)
                ->exists();

            if ($alreadySent) {
                $this->warn("⏭️  Invoice {$invoice->invoice_number} sudah dikirim hari ini, dilewati");
                continue;
            }

            $result = $this->sendInvoiceNotificationToCustomer($invoice, 'due_date');

            if ($result['success']) {
                $sent++;
                $this->info("✅ Berhasil: {$invoice->customer->name} - {$invoice->invoice_number}");
            } else {
                $failed++;
                $this->error("❌ Gagal: {$invoice->customer->name} - {$result['message']}");
            }

            // Anti ban: delay between messages
            sleep(config('services.fonnte.delay_between_messages', 3));
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Send notifications for overdue invoices
     */
    protected function sendOverdueNotifications(): array
    {
        $today = Carbon::today();
        $sent = 0;
        $failed = 0;

        // Get overdue invoices (due_date < today and not paid)
        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'PAID')
            ->whereDate('due_date', '<', $today)
            ->whereHas('customer', function($query) {
                $query->where('active', true)
                      ->whereNotNull('phone');
            })
            ->get();

        $this->info("📊 Ditemukan {$invoices->count()} invoice yang sudah lewat jatuh tempo");

        foreach ($invoices as $invoice) {
            // Check if already sent today
            $alreadySent = WaNotification::where('invoice_id', $invoice->id)
                ->where('status', 'sent')
                ->where('template_name', 'overdue')
                ->whereDate('sent_at', $today)
                ->exists();

            if ($alreadySent) {
                $this->warn("⏭️  Invoice {$invoice->invoice_number} sudah dikirim hari ini, dilewati");
                continue;
            }

            $result = $this->sendInvoiceNotificationToCustomer($invoice, 'overdue');

            if ($result['success']) {
                $sent++;
                $this->info("✅ Berhasil: {$invoice->customer->name} - {$invoice->invoice_number}");
            } else {
                $failed++;
                $this->error("❌ Gagal: {$invoice->customer->name} - {$result['message']}");
            }

            // Anti ban: delay between messages
            sleep(config('services.fonnte.delay_between_messages', 3));
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Send notification for specific customer
     */
    protected function sendCustomerNotification(string $customerId): array
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            $this->error("❌ Customer tidak ditemukan");
            return ['sent' => 0, 'failed' => 0];
        }

        if (!$customer->phone) {
            $this->error("❌ Customer tidak memiliki nomor telepon");
            return ['sent' => 0, 'failed' => 0];
        }

        // Get latest unpaid invoice
        $invoice = $customer->invoices()
            ->where('status', '!=', 'PAID')
            ->orderBy('due_date', 'asc')
            ->first();

        if (!$invoice) {
            $this->warn("⚠️  Tidak ada invoice yang belum dibayar untuk customer ini");
            return ['sent' => 0, 'failed' => 0];
        }

        $result = $this->sendInvoiceNotificationToCustomer($invoice, 'due_date');

        if ($result['success']) {
            $this->info("✅ Berhasil mengirim notifikasi");
            return ['sent' => 1, 'failed' => 0];
        } else {
            $this->error("❌ Gagal: {$result['message']}");
            return ['sent' => 0, 'failed' => 1];
        }
    }

    /**
     * Send notification for specific invoice
     */
    protected function sendInvoiceNotification(string $invoiceId): array
    {
        $invoice = Invoice::with('customer')->find($invoiceId);

        if (!$invoice) {
            $this->error("❌ Invoice tidak ditemukan");
            return ['sent' => 0, 'failed' => 0];
        }

        if (!$invoice->customer->phone) {
            $this->error("❌ Customer tidak memiliki nomor telepon");
            return ['sent' => 0, 'failed' => 0];
        }

        $template = $invoice->due_date < Carbon::today() ? 'overdue' : 'due_date';
        $result = $this->sendInvoiceNotificationToCustomer($invoice, $template);

        if ($result['success']) {
            $this->info("✅ Berhasil mengirim notifikasi");
            return ['sent' => 1, 'failed' => 0];
        } else {
            $this->error("❌ Gagal: {$result['message']}");
            return ['sent' => 0, 'failed' => 1];
        }
    }

    /**
     * Send notification to customer for specific invoice
     */
    protected function sendInvoiceNotificationToCustomer(Invoice $invoice, string $template = 'due_date'): array
    {
        $customer = $invoice->customer;

        if (!$customer->phone) {
            return [
                'success' => false,
                'message' => 'Customer tidak memiliki nomor telepon'
            ];
        }

        // Generate message based on template
        $message = $this->generateMessage($invoice, $template);

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

        return $result;
    }

    /**
     * Generate message based on template
     */
    protected function generateMessage(Invoice $invoice, string $template): string
    {
        $customer = $invoice->customer;
        $dueDate = $invoice->due_date->format('d/m/Y');
        $amount = number_format($invoice->total_amount, 0, ',', '.');
        $daysOverdue = $invoice->due_date->diffInDays(Carbon::today());

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

