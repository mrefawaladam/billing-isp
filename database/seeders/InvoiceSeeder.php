<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::where('active', true)->where('type', '!=', 'free')->get();
        $admin = User::where('email', 'admin@example.com')->first();

        if ($customers->isEmpty()) {
            $this->command->warn('Tidak ada pelanggan aktif. Jalankan CustomerSeeder terlebih dahulu!');
            return;
        }

        $currentYear = date('Y');
        $currentMonth = date('n');
        $invoices = [];

        foreach ($customers as $customer) {
            // Generate invoice untuk 3 bulan terakhir
            for ($i = 2; $i >= 0; $i--) {
                $month = $currentMonth - $i;
                $year = $currentYear;

                if ($month <= 0) {
                    $month += 12;
                    $year -= 1;
                }

                // Hitung total amount
                $amountBeforeTax = $customer->monthly_fee - $customer->discount;
                $taxAmount = $customer->ppn_included ? 0 : ($amountBeforeTax * 0.1);
                $totalAmount = $amountBeforeTax + $taxAmount;

                // Tentukan status berdasarkan bulan
                $status = 'UNPAID';
                $monthsOverdue = 0;
                $paidAt = null;

                if ($i === 0) {
                    // Bulan ini - belum jatuh tempo
                    $status = 'UNPAID';
                } elseif ($i === 1) {
                    // Bulan lalu - mungkin sudah dibayar atau telat
                    if (rand(0, 1)) {
                        $status = 'PAID';
                        $paidAt = now()->subDays(rand(1, 15));
                    } else {
                        $status = 'LATE';
                        $monthsOverdue = 1;
                    }
                } else {
                    // 2 bulan lalu - kemungkinan besar telat atau sudah dibayar
                    if (rand(0, 2) === 0) {
                        $status = 'PAID';
                        $paidAt = now()->subDays(rand(30, 60));
                    } else {
                        $status = 'LATE';
                        $monthsOverdue = 2;
                    }
                }

                // Generate invoice number
                $invoiceNumber = 'INV/' . $year . '/' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . str_pad($customer->customer_code, 6, '0', STR_PAD_LEFT);

                // Calculate due date
                $dueDate = \Carbon\Carbon::create($year, $month, $customer->invoice_due_day);

                $invoices[] = [
                    'id' => Str::uuid()->toString(),
                    'customer_id' => $customer->id,
                    'year' => $year,
                    'month' => $month,
                    'invoice_number' => $invoiceNumber,
                    'due_date' => $dueDate,
                    'amount_before_tax' => $amountBeforeTax,
                    'discount_amount' => $customer->discount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'months_overdue' => $monthsOverdue,
                    'generated_at' => now()->subMonths($i),
                    'generated_by' => $admin->id,
                    'paid_at' => $paidAt,
                ];
            }
        }

        foreach ($invoices as $invoice) {
            Invoice::create($invoice);
        }

        $this->command->info('Berhasil membuat ' . count($invoices) . ' tagihan sample!');
    }
}
