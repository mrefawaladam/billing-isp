<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\WaNotification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WaNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil invoice yang belum dibayar atau telat
        $invoices = Invoice::whereIn('status', ['UNPAID', 'LATE'])->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('Tidak ada tagihan yang perlu notifikasi. Jalankan InvoiceSeeder terlebih dahulu!');
            return;
        }

        $notifications = [];

        foreach ($invoices as $invoice) {
            // Load customer relationship
            $invoice->load('customer');
            $customer = $invoice->customer;
            
            // Generate message text
            $messageText = "Halo {$customer->name}, tagihan internet Anda sebesar " . 
                          number_format($invoice->total_amount, 0, ',', '.') . 
                          " jatuh tempo pada " . $invoice->due_date->format('d/m/Y') . 
                          ". Mohon segera lakukan pembayaran. Terima kasih.";

            // Tentukan status notifikasi
            $status = 'SENT';
            $sentAt = now()->subDays(rand(1, 5));
            $scheduledAt = $sentAt->subDays(rand(1, 3));

            // Beberapa notifikasi mungkin gagal
            if (rand(0, 10) === 0) {
                $status = 'FAILED';
                $sentAt = null;
                $scheduledAt = now()->subDays(rand(1, 3));
            }

            $notifications[] = [
                'id' => Str::uuid()->toString(),
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'template_name' => 'reminder_tagihan',
                'message_text' => $messageText,
                'scheduled_at' => $scheduledAt,
                'sent_at' => $sentAt,
                'status' => $status,
                'provider_response' => $status === 'SENT' ? 
                    '{"status": "success", "message_id": "' . Str::random(20) . '"}' : 
                    '{"status": "failed", "error": "Nomor tidak valid atau tidak aktif"}',
            ];
        }

        foreach ($notifications as $notification) {
            WaNotification::create($notification);
        }

        $this->command->info('Berhasil membuat ' . count($notifications) . ' notifikasi WhatsApp sample!');
    }
}
