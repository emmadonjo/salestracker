<?php
namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\ButtonAction;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Order;
use Filament\Forms;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return array_merge(parent::getActions(), [
            ButtonAction::make('export')->action('export'),
        ]);
    }

    public function export()
    {
        return Excel::download(new OrdersExport, 'orders.xlsx');
    }
}
