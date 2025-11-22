<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paidInvoices = Invoice::where('status', 'PAID')->get();
        $penagih1 = User::where('email', 'penagih1@example.com')->first();
        $penagih2 = User::where('email', 'penagih2@example.com')->first();
        $admin = User::where('email', 'admin@example.com')->first();

        if ($paidInvoices->isEmpty()) {
            $this->command->warn('Tidak ada tagihan yang sudah dibayar. Jalankan InvoiceSeeder terlebih dahulu!');
            return;
        }

        $payments = [];

        foreach ($paidInvoices as $invoice) {
            // Load customer relationship
            $invoice->load('customer');

            // Tentukan method pembayaran (random)
            $method = rand(0, 1) ? 'CASH' : 'TRANSFER';
            $receivedBy = $invoice->customer && $invoice->customer->assigned_to ?
                (rand(0, 1) ? $penagih1 : $penagih2) :
                $admin;

            $payment = [
                'id' => Str::uuid()->toString(),
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'method' => $method,
                'amount' => $invoice->total_amount,
                'paid_date' => $invoice->paid_at ? $invoice->paid_at->toDateString() : now()->subDays(rand(1, 30))->toDateString(),
                'transfer_proof_url' => $method === 'TRANSFER' ? '/storage/payments/proof-' . Str::random(10) . '.jpg' : null,
                'received_by' => $receivedBy->id,
                'note' => $method === 'CASH' ? 'Pembayaran tunai di lokasi' : 'Bukti transfer sudah diupload',
            ];

            $payments[] = $payment;
        }

        foreach ($payments as $payment) {
            Payment::create($payment);
        }

        $this->command->info('Berhasil membuat ' . count($payments) . ' pembayaran sample!');
    }
}
