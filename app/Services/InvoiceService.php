<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(int $year, int $month): string
    {
        $prefix = 'INV-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . '-';
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

    /**
     * Calculate invoice amounts
     */
    private function calculateInvoiceAmounts(Customer $customer): array
    {
        $monthlyFee = $customer->monthly_fee ?? 0;
        $discount = $customer->discount ?? 0;
        $ppnIncluded = $customer->ppn_included ?? false;

        $amountBeforeTax = $monthlyFee - $discount;

        if ($ppnIncluded) {
            // PPN sudah termasuk dalam monthly_fee
            $taxAmount = 0;
            $totalAmount = $amountBeforeTax;
        } else {
            // Tambahkan PPN 10%
            $taxAmount = $amountBeforeTax * 0.1;
            $totalAmount = $amountBeforeTax + $taxAmount;
        }

        return [
            'amount_before_tax' => round($amountBeforeTax, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Calculate months overdue
     */
    public function calculateMonthsOverdue(Invoice $invoice): int
    {
        if ($invoice->status === 'PAID') {
            return 0;
        }

        $dueDate = Carbon::parse($invoice->due_date);
        $now = Carbon::now();

        if ($now->lt($dueDate)) {
            return 0;
        }

        $monthsOverdue = $dueDate->diffInMonths($now);
        
        // Jika sudah lewat tanggal jatuh tempo di bulan yang sama, tambahkan 1
        if ($now->day >= $dueDate->day) {
            $monthsOverdue++;
        }

        return max(0, $monthsOverdue);
    }

    /**
     * Update invoice status based on due date
     */
    public function updateInvoiceStatus(Invoice $invoice): void
    {
        if ($invoice->status === 'PAID') {
            return;
        }

        $dueDate = Carbon::parse($invoice->due_date);
        $now = Carbon::now();

        if ($now->gt($dueDate)) {
            $invoice->status = 'OVERDUE';
        } else {
            $invoice->status = 'UNPAID';
        }

        $invoice->months_overdue = $this->calculateMonthsOverdue($invoice);
        $invoice->save();
    }

    /**
     * Generate invoices for all active customers for a specific month
     */
    public function generateInvoicesForMonth(int $year, int $month, string $generatedBy): array
    {
        $customers = Customer::where('active', true)->get();
        $generated = [];
        $skipped = [];

        DB::beginTransaction();
        try {
            foreach ($customers as $customer) {
                // Check if invoice already exists for this customer, year, and month
                $existingInvoice = Invoice::where('customer_id', $customer->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                if ($existingInvoice) {
                    $skipped[] = [
                        'customer' => $customer->name,
                        'reason' => 'Invoice already exists'
                    ];
                    continue;
                }

                // Calculate due date based on customer's invoice_due_day
                $dueDay = $customer->invoice_due_day ?? 1;
                $dueDate = Carbon::create($year, $month, min($dueDay, Carbon::create($year, $month)->daysInMonth));

                // Calculate amounts
                $amounts = $this->calculateInvoiceAmounts($customer);

                // Generate invoice
                $invoice = Invoice::create([
                    'id' => Str::uuid()->toString(),
                    'customer_id' => $customer->id,
                    'year' => $year,
                    'month' => $month,
                    'invoice_number' => $this->generateInvoiceNumber($year, $month),
                    'due_date' => $dueDate,
                    'amount_before_tax' => $amounts['amount_before_tax'],
                    'discount_amount' => $amounts['discount_amount'],
                    'tax_amount' => $amounts['tax_amount'],
                    'total_amount' => $amounts['total_amount'],
                    'status' => 'UNPAID',
                    'months_overdue' => 0,
                    'generated_at' => now(),
                    'generated_by' => $generatedBy,
                ]);

                // Update status based on due date
                $this->updateInvoiceStatus($invoice);

                $generated[] = $invoice;
            }

            DB::commit();

            return [
                'success' => true,
                'generated' => count($generated),
                'skipped' => count($skipped),
                'invoices' => $generated,
                'skipped_details' => $skipped
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get validation rules for generating invoices
     */
    public static function getGenerateRules(): array
    {
        return [
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ];
    }

    /**
     * Get validation rules for updating invoice
     */
    public static function getUpdateRules(Invoice $invoice): array
    {
        return [
            'status' => 'sometimes|in:UNPAID,PAID,OVERDUE',
        ];
    }
}

