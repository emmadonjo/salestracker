<?php
namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\ButtonAction;
use App\Exports\StocksExport;
use App\Filament\Resources\StockResource\Widgets\Stocks\StockStats;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Stock;
use Filament\Forms;
use Filament\Pages\Actions\Action;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getActions(): array
    {
        return array_merge(parent::getActions(), [
            Action::make('export')->action('export'),
        ]);
    }

    public function export()
    {
        return Excel::download(new StocksExport, 'stocks.xlsx');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockStats::class,
        ];
    }
}
