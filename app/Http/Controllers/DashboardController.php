<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        $statistics = $this->dashboardService->getStatistics();
        $monthlyRevenue = $this->dashboardService->getMonthlyRevenueData();
        $statusDistribution = $this->dashboardService->getInvoiceStatusDistribution();
        $recentPayments = $this->dashboardService->getRecentPayments(10);

        return view('pages.dashboard', compact(
            'statistics',
            'monthlyRevenue',
            'statusDistribution',
            'recentPayments'
        ));
    }

    /**
     * Export dashboard report to Excel/CSV
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel'); // excel or csv
        $statistics = $this->dashboardService->getStatistics();
        $monthlyRevenue = $this->dashboardService->getMonthlyRevenueData();
        
        // Get detailed invoice data
        $invoices = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'invoices.generated_by', '=', 'users.id')
            ->select(
                'invoices.invoice_number',
                'invoices.year',
                'invoices.month',
                'invoices.due_date',
                'invoices.total_amount',
                'invoices.status',
                'customers.name as customer_name',
                'customers.customer_code',
                'users.name as generated_by_name',
                'invoices.created_at'
            )
            ->orderBy('invoices.created_at', 'desc')
            ->get();

        if ($format === 'csv') {
            return $this->exportToCsv($statistics, $monthlyRevenue, $invoices);
        }

        return $this->exportToExcel($statistics, $monthlyRevenue, $invoices);
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($statistics, $monthlyRevenue, $invoices)
    {
        $filename = 'laporan_dashboard_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($statistics, $monthlyRevenue, $invoices) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Statistics Section
            fputcsv($file, ['LAPORAN DASHBOARD - ' . date('d/m/Y H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['STATISTIK']);
            fputcsv($file, ['Total Pelanggan Aktif', $statistics['total_active_customers']]);
            fputcsv($file, ['Total Tagihan Bulan Ini', 'Rp ' . number_format($statistics['total_invoices_this_month'], 0, ',', '.')]);
            fputcsv($file, ['Total Tagihan Belum Dibayar', 'Rp ' . number_format($statistics['total_unpaid_invoices'], 0, ',', '.')]);
            fputcsv($file, ['Pelanggan Terlambat Bayar', $statistics['customers_with_overdue']]);
            fputcsv($file, ['Total Pendapatan Bulan Ini', 'Rp ' . number_format($statistics['total_revenue_this_month'], 0, ',', '.')]);
            fputcsv($file, []);
            
            // Monthly Revenue Section
            fputcsv($file, ['PENDAPATAN BULANAN (12 BULAN TERAKHIR)']);
            fputcsv($file, ['Bulan', 'Pendapatan']);
            foreach ($monthlyRevenue['labels'] as $index => $label) {
                fputcsv($file, [$label, 'Rp ' . number_format($monthlyRevenue['data'][$index], 0, ',', '.')]);
            }
            fputcsv($file, []);
            
            // Invoices Detail Section
            fputcsv($file, ['DETAIL TAGIHAN']);
            fputcsv($file, [
                'No. Tagihan',
                'Pelanggan',
                'Kode Pelanggan',
                'Periode',
                'Jatuh Tempo',
                'Total',
                'Status',
                'Dibuat Oleh',
                'Tanggal Dibuat'
            ]);
            
            foreach ($invoices as $invoice) {
                $monthName = Carbon::create()->month($invoice->month)->locale('id')->monthName;
                $period = $monthName . ' ' . $invoice->year;
                $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d/m/Y') : '-';
                $status = $invoice->status === 'PAID' ? 'Sudah Dibayar' : 
                         ($invoice->status === 'OVERDUE' ? 'Terlambat' : 'Belum Dibayar');
                
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->customer_name,
                    $invoice->customer_code ?? '-',
                    $period,
                    $dueDate,
                    'Rp ' . number_format($invoice->total_amount, 0, ',', '.'),
                    $status,
                    $invoice->generated_by_name ?? '-',
                    Carbon::parse($invoice->created_at)->format('d/m/Y H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (using simple HTML table as Excel)
     */
    private function exportToExcel($statistics, $monthlyRevenue, $invoices)
    {
        $filename = 'laporan_dashboard_' . date('Y-m-d_His') . '.xls';
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Laporan Dashboard</title></head><body>';
        $html .= '<h2>LAPORAN DASHBOARD - ' . date('d/m/Y H:i:s') . '</h2>';
        
        // Statistics
        $html .= '<h3>STATISTIK</h3>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><td><strong>Total Pelanggan Aktif</strong></td><td>' . $statistics['total_active_customers'] . '</td></tr>';
        $html .= '<tr><td><strong>Total Tagihan Bulan Ini</strong></td><td>Rp ' . number_format($statistics['total_invoices_this_month'], 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td><strong>Total Tagihan Belum Dibayar</strong></td><td>Rp ' . number_format($statistics['total_unpaid_invoices'], 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td><strong>Pelanggan Terlambat Bayar</strong></td><td>' . $statistics['customers_with_overdue'] . '</td></tr>';
        $html .= '<tr><td><strong>Total Pendapatan Bulan Ini</strong></td><td>Rp ' . number_format($statistics['total_revenue_this_month'], 0, ',', '.') . '</td></tr>';
        $html .= '</table><br>';
        
        // Monthly Revenue
        $html .= '<h3>PENDAPATAN BULANAN (12 BULAN TERAKHIR)</h3>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><th>Bulan</th><th>Pendapatan</th></tr>';
        foreach ($monthlyRevenue['labels'] as $index => $label) {
            $html .= '<tr><td>' . $label . '</td><td>Rp ' . number_format($monthlyRevenue['data'][$index], 0, ',', '.') . '</td></tr>';
        }
        $html .= '</table><br>';
        
        // Invoices Detail
        $html .= '<h3>DETAIL TAGIHAN</h3>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr><th>No. Tagihan</th><th>Pelanggan</th><th>Kode Pelanggan</th><th>Periode</th><th>Jatuh Tempo</th><th>Total</th><th>Status</th><th>Dibuat Oleh</th><th>Tanggal Dibuat</th></tr>';
        
        foreach ($invoices as $invoice) {
            $monthName = Carbon::create()->month($invoice->month)->locale('id')->monthName;
            $period = $monthName . ' ' . $invoice->year;
            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->format('d/m/Y') : '-';
            $status = $invoice->status === 'PAID' ? 'Sudah Dibayar' : 
                     ($invoice->status === 'OVERDUE' ? 'Terlambat' : 'Belum Dibayar');
            
            $html .= '<tr>';
            $html .= '<td>' . $invoice->invoice_number . '</td>';
            $html .= '<td>' . $invoice->customer_name . '</td>';
            $html .= '<td>' . ($invoice->customer_code ?? '-') . '</td>';
            $html .= '<td>' . $period . '</td>';
            $html .= '<td>' . $dueDate . '</td>';
            $html .= '<td>Rp ' . number_format($invoice->total_amount, 0, ',', '.') . '</td>';
            $html .= '<td>' . $status . '</td>';
            $html .= '<td>' . ($invoice->generated_by_name ?? '-') . '</td>';
            $html .= '<td>' . Carbon::parse($invoice->created_at)->format('d/m/Y H:i:s') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table></body></html>';
        
        return response($html, 200)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

