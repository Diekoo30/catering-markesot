<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Halaman Utama';
    protected static ?string $title = 'Halaman Utama';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            \App\Filament\Widgets\TopMenuChart::class,
            RecentOrdersWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\OrdersExport(),
                    'riwayat_pesanan_' . date('Y-m-d') . '.xlsx'
                )),
        ];
    }
}
