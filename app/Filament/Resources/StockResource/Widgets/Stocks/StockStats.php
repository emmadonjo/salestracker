<?php

namespace App\Filament\Resources\StockResource\Widgets\Stocks;

use App\Models\Stock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StockStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Tot. items', Stock::count())->description('All items Count.')
                ->color('primary'),
            Card::make('In stock', Stock::where('quantity', '>=', 1)->count())->description('Items in stock')
                ->color('success'),
            Card::make('Out of stock', Stock::where('quantity', 0)->count())->description('Items out of stock')
                ->color('danger')
        ];
    }
}
