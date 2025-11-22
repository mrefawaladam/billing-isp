<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentReportService
{
    /**
     * Get payment reports with filters
     */
    public function getReports(array $filters = []): array
    {
        $query = Payment::with(['customer', 'invoice', 'receivedBy']);

        // Filter by date range
        if (isset($filters['start_date']) && $filters['start_date']) {
            $query->whereDate('paid_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date']) {
            $query->whereDate('paid_date', '<=', $filters['end_date']);
        }

        // Filter by customer
        if (isset($filters['customer_id']) && $filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Filter by payment method
        if (isset($filters['method']) && $filters['method']) {
            $query->where('method', $filters['method']);
        }

        // Filter by received_by (staff/user)
        if (isset($filters['received_by']) && $filters['received_by']) {
            $query->where('received_by', $filters['received_by']);
        }

        // Get payments
        $payments = $query->orderBy('paid_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $totalAmount = $payments->sum('amount');
        $totalCount = $payments->count();

        $byMethod = $payments->groupBy('method')->map(function($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount')
            ];
        });

        $byCustomer = $payments->groupBy('customer_id')->map(function($group) {
            return [
                'customer' => $group->first()->customer,
                'count' => $group->count(),
                'total' => $group->sum('amount')
            ];
        });

        // Daily revenue (for chart)
        $dailyRevenue = $payments->groupBy(function($payment) {
            return $payment->paid_date->format('Y-m-d');
        })->map(function($group) {
            return $group->sum('amount');
        })->sortKeys();

        return [
            'payments' => $payments,
            'statistics' => [
                'total_amount' => $totalAmount,
                'total_count' => $totalCount,
                'by_method' => $byMethod,
                'by_customer' => $byCustomer,
            ],
            'daily_revenue' => [
                'labels' => $dailyRevenue->keys()->toArray(),
                'data' => $dailyRevenue->values()->toArray(),
            ],
        ];
    }

    /**
     * Get summary statistics
     */
    public function getSummary(array $filters = []): array
    {
        $query = Payment::query();

        // Apply same filters
        if (isset($filters['start_date']) && $filters['start_date']) {
            $query->whereDate('paid_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date']) {
            $query->whereDate('paid_date', '<=', $filters['end_date']);
        }

        if (isset($filters['customer_id']) && $filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['method']) && $filters['method']) {
            $query->where('method', $filters['method']);
        }

        $totalAmount = $query->sum('amount');
        $totalCount = $query->count();

        $cashAmount = (clone $query)->where('method', 'cash')->sum('amount');
        $transferAmount = (clone $query)->where('method', 'transfer')->sum('amount');

        return [
            'total_amount' => $totalAmount,
            'total_count' => $totalCount,
            'cash_amount' => $cashAmount,
            'transfer_amount' => $transferAmount,
            'cash_count' => (clone $query)->where('method', 'cash')->count(),
            'transfer_count' => (clone $query)->where('method', 'transfer')->count(),
        ];
    }
}

