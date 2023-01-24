<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Stock;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;
    protected $previousRecordItems = [];


    protected function beforeSave(): void
    {
        foreach($this->data['items'] as $item){
            $record = $this->record;

            if($existing = $record->items->where('stock_id', $item['stock_id'])->first()){
                $increment = $item['quantity'] - $existing->quantity;

                if($increment > $existing->stock->quantity){
                    Notification::make()->danger()
                        ->title('Not Enough Quantity')
                        ->body($existing->stock->name .' is out of stock.')
                        ->send();

                        $this->halt();
                }

            }
            else{
                $stock = Stock::find($item['stock_id']);
                if($item['quantity'] > $stock->quantity){
                    Notification::make()->danger()
                    ->title('Not Enough Quantity')
                    ->body($stock->name .' is out of stock.')
                    ->send();

                    $this->halt();
                }
            }
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $amount_paid = $data['amount_paid'] ?? $record->amount_paid;
        $amount = $this->calculateAmount($this->data['items']);
        $subtotal = $amount - $data['discount'];
        $balance = $subtotal - $amount_paid;

        $record->update([
            'customer_id' => $data['customer_id'],
            'amount' => $amount,
            'discount' => $data['discount'],
            'status' => $amount_paid >= $balance ? 'paid' : ($data['status'] ?? 'unpaid'),
            'amount_paid' => $amount_paid,
            'paid_at' => $amount_paid > 0 && $amount_paid > $record->amount_paid ? now() : null,
            'subtotal' => $subtotal,
            'balance' => $balance
        ]);

        if(count($this->data['items']) > 0){
            foreach($this->data['items'] as $item){
                $existing = $item['id'] ?  $record->items()->find($item['id']) : null;

                logger($this->previousRecordItems);
                // logger($this->data);
                break;
                if($existing){
                    // $difference =  $item['quantity'] - $existing->quantity;

                    $existing->update([
                        'quantity' => $item['quantity'],
                        'amount' => $item['quantity'] * $existing->stock->price
                    ]);

                    // if($difference > 0){
                    //     $this->decrementStock($existing->stock, $difference);
                    // }

                    // if($difference < 0){
                    //     $this->incrementStock($existing->stock, abs($difference));
                    // }
                }
                else{
                    $stock = Stock::find($item['stock_id']);
                    $item['amount'] = $stock->price * $data['quantity'];
                    $record->items()->create($item);

                    $this->decrementStock($stock, $item['quantity']);
                }
            }
        }

        return $record->refresh();
    }

    protected function calculateAmount(array $data): float
    {
        $amount = 0;
        foreach($data as $item){
            $amount += Stock::find($item['stock_id'])->price * $item['quantity'];
        }

        return (float)$amount;
    }

    protected function incrementStock(Stock $stock, int $quantity): void
    {
        $quantity = $stock->quantity + $quantity;

        $stock->update(['quantity' => $quantity]);
    }

    protected function decrementStock(Stock $stock, int $quantity): void
    {
        $quantity = $stock->quantity - $quantity;
        $stock->update(['quantity' => $quantity]);
    }


    // protected function getSteps(): array
    // {
    //     $record = $this->getRecord();


    //     return [
    //         Step::make('Order Items')
    //             ->description('Add items to this order')
    //             ->schema([
    //                 Repeater::make('items')->label('Add Order Items')
    //                     ->relationship()
    //                     ->schema([
    //                     Select::make('stock_id')->label('Select Item')
    //                         ->options(Stock::all()->pluck('name', 'id'))
    //                         ->searchable()->placeholder('Select Item'),
    //                     TextInput::make('quantity')->numeric()->label('Quantity')
    //                         ->placeholder('Quantity')->minValue(1),
    //                     TextInput::make('amount')->numeric()->label('Amount')
    //                         ->placeholder('Amount')->disabled()
    //                         ->dehydrated()
    //                         ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
    //                     TextInput::make('id')->numeric()->hidden()
    //                 ])->collapsible()
    //                     ->createItemButtonLabel('Add Item')
    //             ]),
    //         Step::make('Order Details')
    //             ->description('Provide the order details')
    //             ->schema([
    //                 Select::make('customer_id')->label('Customer')
    //                     ->placeholder('Select Customer')
    //                     ->options(Customer::all()->pluck('name', 'id'))
    //                     ->searchable(),
    //                 TextInput::make('amount')->label('Amount')->numeric()
    //                     ->placeholder('Amount')->default($record->amount)
    //                     ->disabled()->dehydrated()
    //                     ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
    //                 TextInput::make('discount')->label('Discount')->numeric()
    //                     ->placeholder('Discount')->helperText('Enter discount amount if any.')->default($record->discount)
    //                     ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
    //                 TextInput::make('subtotal')->label('Subtotal')->numeric()
    //                     ->placeholder('Subtotal')->default($record->subtotal)
    //                     ->disabled()->dehydrated()
    //                     ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
    //                 TextInput::make('amount_paid')->label('Amount Paid')->numeric()
    //                     ->placeholder('Amount Paid')->helperText('Enter the amount if the customer has paid')->default($record->amount_paid)
    //                     ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
    //                 Select::make('status')->label('Payment Status')->required()
    //                     ->placeholder('Select Status')->default($record->status)->options([
    //                         'paid' => 'Paid',
    //                         'part paid' => 'Part Payment',
    //                         'unpaid' => 'Yet to Pay'
    //                     ]),
    //                 DateTimePicker::make('paid_at')->label('Payment Date')
    //                     ->placeholder('Enter Date')->default($record->paid_at)
    //             ])
    //     ];
    // }

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
