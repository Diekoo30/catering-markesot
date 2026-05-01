<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Illuminate\Support\Facades\DB;

class TopMenuChart extends ChartWidget
{
    use HasFiltersSchema;

    protected ?string $heading = 'Menu Paling Laris (Per Bulan)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '400px';

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('bulan')
                ->label('Bulan')
                ->options([
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                ])
                ->default(now()->month)
                ->selectablePlaceholder(false),
            \Filament\Forms\Components\TextInput::make('tahun')
                ->label('Tahun')
                ->numeric()
                ->default(now()->year)
                ->extraInputAttributes([
                    'readonly' => true,
                    'style' => 'text-align: center; pointer-events: none;',
                ])
                ->prefix(new \Illuminate\Support\HtmlString('
                    <div style="cursor:pointer; padding: 0 1rem; font-weight: bold; font-size: 1.25rem; user-select: none;" 
                         x-on:click="$wire.set(\'filters.tahun\', Number($wire.get(\'filters.tahun\')) - 1)">
                        -
                    </div>
                '))
                ->suffix(new \Illuminate\Support\HtmlString('
                    <div style="cursor:pointer; padding: 0 1rem; font-weight: bold; font-size: 1.25rem; user-select: none;" 
                         x-on:click="$wire.set(\'filters.tahun\', Number($wire.get(\'filters.tahun\')) + 1)">
                        +
                    </div>
                ')),
        ]);
    }

    protected function getData(): array
    {
        $month = !empty($this->filters['bulan']) ? (int) $this->filters['bulan'] : now()->month;
        $year  = !empty($this->filters['tahun']) ? (int) $this->filters['tahun'] : now()->year;

        $date = Carbon::create($year, $month, 1);
        $this->heading = 'Menu Paling Laris — ' . $date->translatedFormat('F Y');

        $topMenus = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereYear('orders.created_at', $year)
            ->whereMonth('orders.created_at', $month)
            ->whereIn('orders.status', ['completed', 'confirmed', 'dp_paid', 'pending'])
            ->select('order_items.menu_name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->groupBy('order_items.menu_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Porsi Terjual',
                    'data' => $topMenus->pluck('total_quantity')->map(fn($val) => (int) $val)->toArray(),
                    'backgroundColor' => '#8B2535',
                    'borderColor' => '#6B1C2A',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $topMenus->pluck('menu_name')->toArray(),
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                    'grid' => [
                        'display' => true,
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'font' => [
                            'size' => 12,
                        ],
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
