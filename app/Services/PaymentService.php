<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Create payment record
     */
    public function createPayment(array $data, ?UploadedFile $proofFile = null, ?UploadedFile $fieldPhoto = null): Payment
    {
        DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($data['invoice_id']);

            // Handle transfer proof upload
            $transferProofUrl = null;
            if ($proofFile) {
                $transferProofUrl = $this->uploadFile($proofFile, 'payments/proofs');
            }

            // Handle field photo upload (optional)
            $fieldPhotoUrl = null;
            if ($fieldPhoto) {
                $fieldPhotoUrl = $this->uploadFile($fieldPhoto, 'payments/field-photos');
            }

            // Create payment record
            $payment = Payment::create([
                'id' => Str::uuid()->toString(),
                'invoice_id' => $data['invoice_id'],
                'customer_id' => $invoice->customer_id,
                'method' => $data['method'],
                'amount' => $data['amount'] ?? $invoice->total_amount,
                'paid_date' => $data['paid_date'] ?? now()->toDateString(),
                'transfer_proof_url' => $transferProofUrl,
                'received_by' => $data['received_by'] ?? auth()->id(),
                'note' => $data['note'] ?? null,
            ]);

            // Update invoice status
            $invoice->status = 'PAID';
            $invoice->paid_at = now();
            $invoice->months_overdue = 0;
            $invoice->save();

            // Store field photo if provided (using File model)
            if ($fieldPhotoUrl) {
                \App\Models\File::create([
                    'id' => Str::uuid()->toString(),
                    'owner_type' => 'payment',
                    'owner_id' => $payment->id,
                    'file_url' => $fieldPhotoUrl,
                    'file_type' => 'field_photo',
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return $payment->load('invoice', 'customer', 'receivedBy');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update payment record
     */
    public function updatePayment(Payment $payment, array $data, ?UploadedFile $proofFile = null, ?UploadedFile $fieldPhoto = null): Payment
    {
        DB::beginTransaction();
        try {
            // Handle transfer proof upload if new file provided
            if ($proofFile) {
                // Delete old file if exists
                if ($payment->transfer_proof_url) {
                    $this->deleteFile($payment->transfer_proof_url);
                }
                $data['transfer_proof_url'] = $this->uploadFile($proofFile, 'payments/proofs');
            }

            // Handle field photo upload if new file provided
            if ($fieldPhoto) {
                $fieldPhotoUrl = $this->uploadFile($fieldPhoto, 'payments/field-photos');

                // Store field photo
                \App\Models\File::create([
                    'id' => Str::uuid()->toString(),
                    'owner_type' => 'payment',
                    'owner_id' => $payment->id,
                    'file_url' => $fieldPhotoUrl,
                    'file_type' => 'field_photo',
                    'created_at' => now(),
                ]);
            }

            $payment->update($data);

            DB::commit();

            return $payment->fresh()->load('invoice', 'customer', 'receivedBy');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark invoice as paid (cash payment - no proof needed)
     */
    public function markAsPaidCash(Invoice $invoice, ?string $note = null, ?UploadedFile $fieldPhoto = null): Payment
    {
        return $this->createPayment([
            'invoice_id' => $invoice->id,
            'method' => 'cash',
            'amount' => $invoice->total_amount,
            'paid_date' => now()->toDateString(),
            'received_by' => auth()->id(),
            'note' => $note,
        ], null, $fieldPhoto);
    }

    /**
     * Mark invoice as paid with transfer proof
     */
    public function markAsPaidTransfer(Invoice $invoice, UploadedFile $proofFile, ?string $note = null, ?UploadedFile $fieldPhoto = null): Payment
    {
        return $this->createPayment([
            'invoice_id' => $invoice->id,
            'method' => 'transfer',
            'amount' => $invoice->total_amount,
            'paid_date' => now()->toDateString(),
            'received_by' => auth()->id(),
            'note' => $note,
        ], $proofFile, $fieldPhoto);
    }

    /**
     * Upload file to storage
     */
    private function uploadFile(UploadedFile $file, string $directory): string
    {
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return Storage::disk('public')->url($path);
    }

    /**
     * Delete file from storage
     */
    private function deleteFile(string $url): bool
    {
        try {
            $path = str_replace(Storage::disk('public')->url(''), '', $url);
            return Storage::disk('public')->delete($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get validation rules for creating payment
     */
    public static function getCreateRules(): array
    {
        return [
            'invoice_id' => 'required|exists:invoices,id',
            'method' => 'required|in:cash,transfer',
            'amount' => 'sometimes|numeric|min:0',
            'paid_date' => 'sometimes|date',
            'note' => 'nullable|string|max:500',
            'transfer_proof' => 'required_if:method,transfer|image|mimes:jpeg,png,jpg,gif|max:5120',
            'field_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    /**
     * Get validation rules for updating payment
     */
    public static function getUpdateRules(): array
    {
        return [
            'method' => 'sometimes|in:cash,transfer',
            'amount' => 'sometimes|numeric|min:0',
            'paid_date' => 'sometimes|date',
            'note' => 'nullable|string|max:500',
            'transfer_proof' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'field_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }
}

