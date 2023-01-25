<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\LineChartWidget;

class OrderChart extends LineChartWidget
{
    protected static ?string $heading = 'Tot. Orders';
    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => true
            ]
        ]
    ];


    protected function getData(): array
    {
        $data = Trend::model(Order::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear()
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Orders by month',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate)
                ]
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date)
        ];
    }
}
