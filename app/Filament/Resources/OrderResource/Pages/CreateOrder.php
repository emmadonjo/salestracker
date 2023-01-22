<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Stock;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    protected function beforeCreate(): void
    {
        foreach($this->data['items'] as $item){
            $stock = Stock::find($item['stock_id']);

            if($stock->quantity < $item['quantity']){
                Notification::make()->danger()
                ->title('Not Enough Quantity')
                ->body($stock->name .' is out of stock.')
                ->send();

                $this->halt();
            }
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $amount_paid = $data['amount_paid'] ?? 0;
        $amount = $this->calculateAmount($data['items']);

        $order = static::getModel()::create([
            'user_id' => auth()->id(),
            'reference' => Str::lower(Str::random(8)),
            'customer_id' => $data['customer_id'],
            'amount' => $amount,
            'discount' => $data['discount'],
            'status' => $data['status'] ?? 'unpaid',
            'amount_paid' => $amount_paid,
            'paid_at' => (int) $amount_paid > 0 ? now() : null,
            'subtotal' => $amount - $data['discount']
        ]);

        if(count($data['items']) > 0){
            foreach($data['items'] as $item){
                $stock = Stock::find($item['stock_id']);
                $order->items()->create([
                    'stock_id' => $item['stock_id'],
                    'quantity' => $item['quantity'],
                    'amount' => $stock->price
                ]);
            }
        }

        return $order;
    }

    protected function getSteps(): array
    {
        return [
            Step::make('items')
            ->description('Add items to this order')
            ->schema([
                Repeater::make('items')->label('Add Order Items')
                    ->schema([
                    Select::make('stock_id')->label('Select Item')
                        ->options(Stock::all()->pluck('name', 'id'))
                        ->searchable()->placeholder('Select Item'),
                    TextInput::make('quantity')->numeric()->label('Quantity')
                        ->placeholder('Quantity')->default(0),
                    // TextInput::make('amount')->numeric()->label('Amount')
                    //     ->placeholder('Quantity')
                    //     ->disabled()->dehydrated()
                ])
            ]),
            Step::make('Order Details')
                ->description('Provide the order details')
                ->schema([
                    Select::make('customer_id')->label('Customer')
                        ->placeholder('Select Customer')
                        ->options(Customer::all()->pluck('name', 'id'))
                        ->searchable(),
                    TextInput::make('amount_paid')->label('Amount Paid')->numeric()
                        ->placeholder('Amount Paid')->helperText('Enter the amount if the customer has paid')->default(0),
                    Select::make('status')->label('Payment Status')->required()
                        ->placeholder('Select Status')->default('unpaid')->options([
                            'paid' => 'Paid',
                            'part paid' => 'Part Payment',
                            'unpaid' => 'Yet to Pay'
                        ]),
                    TextInput::make('discount')->label('Discount')->numeric()
                        ->placeholder('Discount')->helperText('Enter discount amount if any.')->default(0),
                    DateTimePicker::make('paid_at')->label('Payment Date')
                        ->placeholder('Enter Date')
                ])
        ];
    }

    protected function calculateAmount(array $data): float
    {
        $amount = 0;
        foreach($data as $item){
            $amount += Stock::find($item['stock_id'])->price;
        }

        return (float)$amount;
    }
}
