<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\LineChartWidget;

class OrdersByDayChart extends LineChartWidget
{
    protected static ?string $heading = 'Orders by  Week day';
    // public ?string $filter = 'today';

    // protected function getFilters(): ?array
    // {
    //     return [
    //         'today' => 'Today',
    //         'week' => 'Week',
    //         'month' => 'Month',
    //         'year' => 'This year'
    //     ];
    // }

    protected function getData(): array
    {
        $interval = $this->filter;

        $data = Trend::model(Order::class)
            ->between(
                start: now()->startOfWeek(),
                end: now()->endOfWeek()
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Orders this week.',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate)
                ]
            ],
            'fill' => true,
            'borderColor' => 'green',
            'labels' => $data->map(fn(TrendValue $value) => $value->date)
        ];
    }
}
