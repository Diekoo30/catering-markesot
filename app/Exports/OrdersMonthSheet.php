<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrdersMonthSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize
{
    private string $month; // Format: YYYY-MM

    public function __construct(string $month)
    {
        $this->month = $month;
    }

    public function query()
    {
        // $this->month misalnya "2026-05"
        $parsed = Carbon::createFromFormat('Y-m', $this->month);
        
        return Order::query()
            ->with(['orderItems', 'payments'])
            ->whereIn('status', ['completed', 'cancelled'])
            ->whereYear('created_at', $parsed->year)
            ->whereMonth('created_at', $parsed->month)
            ->orderBy('created_at', 'desc');
    }

    public function title(): string
    {
        Carbon::setLocale('id'); // Pastikan menggunakan bahasa Indonesia
        return Carbon::createFromFormat('Y-m', $this->month)->translatedFormat('F Y');
    }

    public function headings(): array
    {
        return [
            'No. Pesanan',
            'Tanggal Pesan',
            'Nama Pelanggan',
            'Telepon',
            'Alamat',
            'Tanggal Acara',
            'Waktu Acara',
            'Daftar Menu',
            'Total Item',
            'Total Pembayaran',
            'Status Akhir',
            'Catatan Pelanggan',
            'Alasan Batal (Oleh Pembeli)',
            'Alasan Penolakan (Oleh Admin)',
        ];
    }

    public function map($order): array
    {
        $daftarMenu = $order->orderItems->map(function ($item) {
            return "{$item->menu_name} (x{$item->quantity})";
        })->implode(', ');

        $totalItem = $order->orderItems->sum('quantity');

        // Determine if cancelled by user or admin
        $isAdminRejection = $order->status === 'cancelled' && $order->payments->where('status', 'rejected')->isNotEmpty();
        
        $alasanBatal = ($order->status === 'cancelled' && !$isAdminRejection) ? $order->cancellation_reason : '-';
        $alasanTolak = $isAdminRejection ? $order->cancellation_reason : '-';

        return [
            $order->order_number,
            Carbon::parse($order->created_at)->format('Y-m-d H:i:s'),
            $order->customer_name,
            $order->customer_phone,
            $order->customer_address,
            Carbon::parse($order->event_date)->format('Y-m-d'),
            $order->event_time,
            $daftarMenu,
            $totalItem,
            $order->total_amount,
            $order->status === 'completed' ? 'Selesai' : 'Dibatalkan',
            $order->notes ?? '-',
            $alasanBatal ?? '-',
            $alasanTolak ?? '-',
        ];
    }
}
