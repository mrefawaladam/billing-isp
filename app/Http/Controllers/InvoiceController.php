<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Invoice::with(['customer', 'generatedBy'])->select('invoices.*');

            // Filter berdasarkan status
            if ($request->filled('status') && $request->status !== null && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan year
            if ($request->filled('year') && $request->year !== null && $request->year !== '') {
                $query->where('year', $request->year);
            }

            // Filter berdasarkan month
            if ($request->filled('month') && $request->month !== null && $request->month !== '') {
                $query->where('month', $request->month);
            }

            // Filter berdasarkan customer
            if ($request->filled('customer_id') && $request->customer_id !== null && $request->customer_id !== '') {
                $query->where('customer_id', $request->customer_id);
            }

            return DataTables::of($query)
                ->addColumn('customer_name', function ($invoice) {
                    return $invoice->customer ? $invoice->customer->name : '-';
                })
                ->addColumn('customer_code', function ($invoice) {
                    return $invoice->customer ? ($invoice->customer->customer_code ?? '-') : '-';
                })
                ->addColumn('period', function ($invoice) {
                    $monthName = Carbon::create()->month($invoice->month)->locale('id')->monthName;
                    return $monthName . ' ' . $invoice->year;
                })
                ->addColumn('status_badge', function ($invoice) {
                    $badges = [
                        'UNPAID' => 'bg-warning',
                        'PAID' => 'bg-success',
                        'OVERDUE' => 'bg-danger',
                    ];
                    $badge = $badges[$invoice->status] ?? 'bg-secondary';
                    $statusText = [
                        'UNPAID' => 'Belum Dibayar',
                        'PAID' => 'Sudah Dibayar',
                        'OVERDUE' => 'Terlambat',
                    ];
                    $text = $statusText[$invoice->status] ?? $invoice->status;
                    
                    $html = '<span class="badge ' . $badge . '">' . $text . '</span>';
                    
                    if ($invoice->months_overdue > 0) {
                        $html .= ' <span class="badge bg-danger ms-1">' . $invoice->months_overdue . ' bulan</span>';
                    }
                    
                    return $html;
                })
                ->addColumn('total_amount_formatted', function ($invoice) {
                    return 'Rp ' . number_format($invoice->total_amount ?? 0, 0, ',', '.');
                })
                ->addColumn('due_date_formatted', function ($invoice) {
                    return $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-';
                })
                ->addColumn('action', function ($invoice) {
                    return view('features.invoices.partials.action-buttons', compact('invoice'))->render();
                })
                ->editColumn('invoice_number', function ($invoice) {
                    return $invoice->invoice_number ?? '-';
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        // Get years for filter
        $years = Invoice::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years = [date('Y')];
        }

        return view('features.invoices.index', compact('years'));
    }

    /**
     * Show the form for generating invoices
     */
    public function create()
    {
        return response()->json([
            'html' => view('features.invoices.partials.generate-form')->render()
        ]);
    }

    /**
     * Generate invoices for a specific month
     */
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate(InvoiceService::getGenerateRules());

            $result = $this->invoiceService->generateInvoicesForMonth(
                $validated['year'],
                $validated['month'],
                auth()->id()
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil generate {$result['generated']} tagihan. " . 
                                 ($result['skipped'] > 0 ? "{$result['skipped']} tagihan dilewati (sudah ada)." : ''),
                    'data' => $result
                ]);
            }

            return redirect()->route('invoices.index')
                ->with('success', "Berhasil generate {$result['generated']} tagihan.");
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
                    'message' => 'Gagal generate tagihan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal generate tagihan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('customer.assignedUser', 'generatedBy', 'payments.receivedBy');

        if (request()->ajax()) {
            // Check if this is for confirm payment modal
            if (request()->get('confirm') === 'payment') {
                return response()->json([
                    'html' => view('features.invoices.partials.confirm-payment', compact('invoice'))->render()
                ]);
            }
            
            return response()->json([
                'html' => view('features.invoices.partials.show', compact('invoice'))->render()
            ]);
        }

        return redirect()->route('invoices.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        try {
            $validated = $request->validate(InvoiceService::getUpdateRules($invoice));

            if (isset($validated['status'])) {
                $invoice->status = $validated['status'];
                
                if ($validated['status'] === 'PAID') {
                    $invoice->paid_at = now();
                    $invoice->months_overdue = 0;
                } else {
                    $invoice->paid_at = null;
                    $this->invoiceService->updateInvoiceStatus($invoice);
                }
                
                $invoice->save();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status tagihan berhasil diperbarui.'
                ]);
            }

            return redirect()->route('invoices.index')
                ->with('success', 'Status tagihan berhasil diperbarui.');
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
        }
    }

    /**
     * Print invoice as PDF
     */
    public function print(Invoice $invoice)
    {
        $invoice->load('customer.assignedUser', 'generatedBy', 'payments.receivedBy');
        
        return view('features.invoices.print', compact('invoice'));
    }

    /**
     * Export invoices to Excel/CSV
     */
    public function export(Request $request)
    {
        // TODO: Implement Excel/CSV export
        return response()->json([
            'success' => false,
            'message' => 'Fitur export sedang dalam pengembangan.'
        ]);
    }
}

