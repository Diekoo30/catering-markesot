<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrdersExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $sheets = [];

        // Mengambil semua bulan yang unik dari pesanan yang selesai atau dibatalkan
        // Menggunakan get dan groupBy PHP untuk menghindari masalah dialek SQL (MySQL vs SQLite)
        $months = Order::select('created_at')
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m');
            })
            ->keys();

        if ($months->isEmpty()) {
            // Jika tidak ada data, buat setidaknya satu sheet bulan ini
            $sheets[] = new OrdersMonthSheet(Carbon::now()->format('Y-m'));
            return $sheets;
        }

        foreach ($months as $month) {
            $sheets[] = new OrdersMonthSheet($month);
        }

        return $sheets;
    }
}
