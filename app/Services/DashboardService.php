<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get dashboard statistics
     */
    public function getStatistics(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Total pelanggan aktif
        $totalActiveCustomers = Customer::where('active', true)->count();

        // Total tagihan bulan ini
        $totalInvoicesThisMonth = Invoice::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->sum('total_amount');

        // Total tagihan belum dibayar
        $totalUnpaidInvoices = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])
            ->sum('total_amount');

        // Jumlah pelanggan terlambat bayar
        $customersWithOverdue = Customer::whereHas('invoices', function($query) {
            $query->where('status', 'OVERDUE');
        })->count();

        // Total pendapatan bulan ini (tagihan yang sudah dibayar)
        $totalRevenueThisMonth = Invoice::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->where('status', 'PAID')
            ->sum('total_amount');

        // Total tagihan bulan ini (count)
        $totalInvoicesCountThisMonth = Invoice::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->count();

        // Tagihan belum dibayar bulan ini
        $unpaidInvoicesThisMonth = Invoice::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->whereIn('status', ['UNPAID', 'OVERDUE'])
            ->sum('total_amount');

        return [
            'total_active_customers' => $totalActiveCustomers,
            'total_invoices_this_month' => $totalInvoicesThisMonth,
            'total_unpaid_invoices' => $totalUnpaidInvoices,
            'customers_with_overdue' => $customersWithOverdue,
            'total_revenue_this_month' => $totalRevenueThisMonth,
            'total_invoices_count_this_month' => $totalInvoicesCountThisMonth,
            'unpaid_invoices_this_month' => $unpaidInvoicesThisMonth,
        ];
    }

    /**
     * Get monthly revenue data for chart (last 12 months)
     */
    public function getMonthlyRevenueData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $revenue = Invoice::where('year', $year)
                ->where('month', $month)
                ->where('status', 'PAID')
                ->sum('total_amount');

            $monthName = $date->locale('id')->monthName;
            $labels[] = $monthName . ' ' . $year;
            $data[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get invoice status distribution
     */
    public function getInvoiceStatusDistribution(): array
    {
        $paid = Invoice::where('status', 'PAID')->count();
        $unpaid = Invoice::where('status', 'UNPAID')->count();
        $overdue = Invoice::where('status', 'OVERDUE')->count();

        return [
            'paid' => $paid,
            'unpaid' => $unpaid,
            'overdue' => $overdue,
        ];
    }

    /**
     * Get recent activities (recent payments)
     */
    public function getRecentPayments(int $limit = 10)
    {
        return \App\Models\Payment::with(['invoice', 'customer', 'receivedBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

