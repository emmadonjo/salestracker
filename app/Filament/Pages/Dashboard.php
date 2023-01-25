<?php


namespace App\Filament\Pages;

use App\Filament\Widgets\OrderChart;
use App\Filament\Widgets\OrdersByDayChart;
use App\Filament\Widgets\SalesByMonthChart;
use App\Filament\Widgets\SalesOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = false;
    protected function getWidgets(): array
    {
        return [
            SalesOverview::class,
            SalesByMonthChart::class,
            OrderChart::class,
            OrdersByDayChart::class
        ];
    }
}
