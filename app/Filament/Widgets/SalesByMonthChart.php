<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\BarChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;


class SalesByMonthChart extends BarChartWidget
{
    protected static ?string $heading = 'Sales by month';

    protected function getData(): array
    {
        $data = Trend::model(Order::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear()
            )
            ->perMonth()
            ->sum('amount_paid');

        return [
            'datasets' => [
                [
                    'label' => 'Sales by month.',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate)
                ]
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date)
        ];
    }
}
