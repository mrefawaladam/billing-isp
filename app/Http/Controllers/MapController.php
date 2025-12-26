<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Display the map page
     */
    public function index()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager']);
        })->orWhere('email', 'like', '%penagih%')->get();

        return view('features.map.index', compact('users'));
    }

    /**
     * Get customers data for map with invoice status
     */
    public function getCustomers(Request $request)
    {
        $query = Customer::where('active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['assignedUsers', 'invoices' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }]);

        // Filter berdasarkan penanggung jawab
        if ($request->filled('assigned_to') && $request->assigned_to !== null && $request->assigned_to !== '') {
            $query->whereHas('assignedUsers', function($q) use ($request) {
                $q->where('users.id', $request->assigned_to);
            });
        }

        // Filter berdasarkan jenis pelanggan
        if ($request->filled('type') && $request->type !== null && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $customers = $query->get();
        $invoiceStatusFilter = $request->filled('invoice_status') && $request->invoice_status !== null && $request->invoice_status !== '' ? $request->invoice_status : null;

        $data = $customers->map(function ($customer) use ($invoiceStatusFilter) {
            // Get latest invoice
            $latestInvoice = $customer->invoices->first();

            // Determine status
            $status = 'NO_INVOICE'; // No invoice yet
            $statusText = 'Belum Ada Tagihan';
            $markerColor = 'gray';

            if ($latestInvoice) {
                // Update invoice status if needed
                $dueDate = Carbon::parse($latestInvoice->due_date);
                $now = Carbon::now();

                if ($latestInvoice->status === 'PAID') {
                    $status = 'PAID';
                    $statusText = 'Sudah Dibayar';
                    $markerColor = 'green';
                } elseif ($latestInvoice->status === 'OVERDUE') {
                    $status = 'OVERDUE';
                    $statusText = 'Terlambat';
                    $markerColor = 'red';
                } elseif ($now->isSameDay($dueDate) || ($now->gt($dueDate) && $now->diffInDays($dueDate) <= 3)) {
                    // Jatuh tempo hari ini atau dalam 3 hari terakhir (masih kuning)
                    $status = 'DUE';
                    $statusText = 'Jatuh Tempo';
                    $markerColor = 'yellow';
                } elseif ($now->gt($dueDate)) {
                    // Sudah lewat jatuh tempo lebih dari 3 hari
                    $status = 'OVERDUE';
                    $statusText = 'Terlambat';
                    $markerColor = 'red';
                } else {
                    $status = 'UNPAID';
                    $statusText = 'Belum Dibayar';
                    $markerColor = 'red';
                }
            }

            // Filter berdasarkan status tagihan
            if ($invoiceStatusFilter) {
                if ($invoiceStatusFilter === 'PAID' && $status !== 'PAID') {
                    return null;
                }
                if ($invoiceStatusFilter === 'DUE' && $status !== 'DUE') {
                    return null;
                }
                if ($invoiceStatusFilter === 'UNPAID' && !in_array($status, ['UNPAID', 'OVERDUE'])) {
                    return null;
                }
            }

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'code' => $customer->customer_code,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'lat' => (float) $customer->lat,
                'lng' => (float) $customer->lng,
                'type' => $customer->type,
                'total_fee' => $customer->total_fee,
                'assigned_user' => $customer->assignedUsers->count() > 0 ? $customer->assignedUsers->pluck('name')->implode(', ') : null,
                'invoice_status' => $status,
                'invoice_status_text' => $statusText,
                'marker_color' => $markerColor,
                'latest_invoice' => $latestInvoice ? [
                    'id' => $latestInvoice->id,
                    'invoice_number' => $latestInvoice->invoice_number,
                    'total_amount' => $latestInvoice->total_amount,
                    'due_date' => $latestInvoice->due_date ? $latestInvoice->due_date->format('d/m/Y') : null,
                    'status' => $latestInvoice->status,
                    'months_overdue' => $latestInvoice->months_overdue,
                ] : null,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}

