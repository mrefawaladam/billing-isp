<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PaymentReportController extends Controller
{
    protected $reportService;

    public function __construct(PaymentReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display payment reports page
     */
    public function index(Request $request)
    {
        // Default filters: current month
        $filters = [
            'start_date' => $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'end_date' => $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d')),
            'customer_id' => $request->get('customer_id'),
            'method' => $request->get('method'),
            'received_by' => $request->get('received_by'),
        ];

        $report = $this->reportService->getReports($filters);
        $summary = $this->reportService->getSummary($filters);

        // Get filter options
        $customers = Customer::where('active', true)->orderBy('name')->get();
        
        // Get staff/users who have received payments
        $staffIds = Payment::whereNotNull('received_by')
            ->distinct()
            ->pluck('received_by');
        $staff = User::whereIn('id', $staffIds)->orderBy('name')->get();

        return view('features.payments.report', [
            'payments' => $report['payments'],
            'statistics' => $report['statistics'],
            'daily_revenue' => $report['daily_revenue'],
            'summary' => $summary,
            'filters' => $filters,
            'customers' => $customers,
            'staff' => $staff,
        ]);
    }

    /**
     * Export payment reports to CSV
     */
    public function exportCsv(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'end_date' => $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d')),
            'customer_id' => $request->get('customer_id'),
            'method' => $request->get('method'),
            'received_by' => $request->get('received_by'),
        ];

        $report = $this->reportService->getReports($filters);
        $payments = $report['payments'];

        $filename = 'laporan_pembayaran_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($payments, $report) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['LAPORAN PEMBAYARAN - ' . date('d/m/Y H:i:s')]);
            fputcsv($file, []);
            
            // Summary
            fputcsv($file, ['RINGKASAN']);
            fputcsv($file, ['Total Pembayaran', 'Rp ' . number_format($report['statistics']['total_amount'], 0, ',', '.')]);
            fputcsv($file, ['Jumlah Transaksi', $report['statistics']['total_count']]);
            fputcsv($file, []);
            
            // By Method
            fputcsv($file, ['BERDASARKAN METODE PEMBAYARAN']);
            foreach ($report['statistics']['by_method'] as $method => $data) {
                fputcsv($file, [
                    ucfirst($method),
                    $data['count'] . ' transaksi',
                    'Rp ' . number_format($data['total'], 0, ',', '.')
                ]);
            }
            fputcsv($file, []);
            
            // Detail Payments
            fputcsv($file, ['DETAIL PEMBAYARAN']);
            fputcsv($file, [
                'Tanggal',
                'No. Invoice',
                'Customer',
                'Metode',
                'Jumlah',
                'Diterima Oleh',
                'Catatan'
            ]);
            
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->paid_date->format('d/m/Y'),
                    $payment->invoice->invoice_number ?? '-',
                    $payment->customer->name ?? '-',
                    ucfirst($payment->method),
                    'Rp ' . number_format($payment->amount, 0, ',', '.'),
                    $payment->receivedBy->name ?? '-',
                    $payment->note ?? '-'
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export payment reports to Excel (using CSV format for now)
     */
    public function exportExcel(Request $request)
    {
        // For now, use CSV format
        // Can be enhanced with PhpSpreadsheet later
        return $this->exportCsv($request);
    }
}
