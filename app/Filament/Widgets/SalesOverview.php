<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SalesOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total', Order::where('status', 'paid')->sum('subtotal'))
                ->description('Total sales')
                ->color('success'),
            Card::make('Part Payments', Order::where('status', 'part paid')->sum('subtotal'))
                ->description('Total part payments')
                ->color('primary'),
            Card::make('Bal. Payments', Order::where('status', 'part paid')->sum('balance') + Order::where('status', 'unpaid')->sum('subtotal'))
                ->description('Total balance payments')
                ->color('danger')
        ];
    }
}
