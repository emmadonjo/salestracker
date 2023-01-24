<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Stock;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    // use HasWizard;

    protected static string $resource = OrderResource::class;

    // protected function beforeCreate(): void
    // {
    //     foreach($this->data['items'] as $item){
    //         $stock = Stock::find($item['stock_id']);

    //         if($stock->quantity < $item['quantity']){
    //             Notification::make()->danger()
    //             ->title('Not Enough Quantity')
    //             ->body($stock->name .' is out of stock.')
    //             ->send();

    //             $this->halt();
    //         }
    //     }
    // }

    // protected function afterCreate(): void
    // {
    //     $items = $this->data['items'];

    //     // deduct items quantity from stock
    //     collect($items)->each(function($item){
    //         $stock = Stock::find($item['stock_id']);
    //         $qty = $stock->quantity - $item['quantity'];
    //         $stock->update(['quantity' => $qty]);
    //     });
    // }

    // protected function handleRecordCreation(array $data): Model
    // {
    //     $amount_paid = $data['amount_paid'] ?? 0;
    //     $amount = $this->calculateAmount($data['items']);
    //     $subtotal = $amount - $data['discount'];

    //     $order = static::getModel()::create([
    //         'user_id' => auth()->id(),
    //         'reference' => Str::lower(Str::random(8)),
    //         'customer_id' => $data['customer_id'],
    //         'amount' => $amount,
    //         'discount' => $data['discount'],
    //         'status' => $this->paymentStatus($amount_paid, $subtotal, $data['status']),
    //         'amount_paid' => $amount_paid,
    //         'paid_at' => $amount_paid > 0 ? now() : null,
    //         'subtotal' => $subtotal,
    //         'balance' => $subtotal - $amount_paid,
    //         'paid_at' => $amount_paid >= 1 ? now() : null
    //     ]);

    //     if(count($data['items']) > 0){
    //         foreach($data['items'] as $item){
    //             $stock = Stock::find($item['stock_id']);
    //             $order->items()->create([
    //                 'stock_id' => $item['stock_id'],
    //                 'quantity' => $item['quantity'],
    //                 'amount' => $stock->price * $item['quantity']
    //             ]);
    //         }
    //     }

    //     return $order;
    // }

    // protected function form(Form $form): Form
    // {
    //     return $form->schema([
    //         Grid::make()
    //             ->schema([
    //                 Repeater::make('items')->label('Add Order Items')
    //                 ->schema([
    //                 Select::make('stock_id')->label('Select Item')
    //                     ->options(Stock::all()->pluck('name', 'id'))
    //                     ->searchable()->placeholder('Select Item'),
    //                 TextInput::make('quantity')->numeric()->label('Quantity')
    //                     ->minValue(1)->default(1)
    //                     ->placeholder('Quantity')
    //             ])->collapsible()
    //             ->createItemButtonLabel('Add Item')
    //             ->columns(2)
    //         ]),
    //         Grid::make()
    //             ->schema([
    //                 Select::make('customer_id')->label('Customer')
    //                     ->placeholder('Select Customer')
    //                     ->relationship('customer', 'name')
    //                     ->preload()
    //                     ->searchable()
    //                     ->createOptionForm([
    //                         TextInput::make('name')->required()
    //                             ->maxLength(255),
    //                         TextInput::make('phone')
    //                             ->unique('customers', 'phone')
    //                     ]),
    //                 TextInput::make('amount_paid')
    //                     ->label('Amount Paid')->numeric()
    //                     ->placeholder('Amount Paid')
    //                     ->helperText('Enter the amount if the customer has paid')
    //                     ->default(0)
    //                     ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
    //                 Select::make('status')->label('Payment Status')->required()
    //                     ->placeholder('Select Status')->default('unpaid')->options([
    //                         'paid' => 'Paid',
    //                         'part paid' => 'Part Payment',
    //                         'unpaid' => 'Yet to Pay'
    //                     ]),
    //                 TextInput::make('discount')->label('Discount')->numeric()
    //                     ->placeholder('Discount')
    //                     ->helperText('Enter discount amount if any.')
    //                     ->default(0)
    //                     ->mask(fn(Mask $mask) => $mask->money(prefix:'₦'))
    //             ])
    //     ]);
    // }

    // protected function calculateAmount(array $data): float
    // {
    //     $amount = 0;
    //     foreach($data as $item){
    //         $amount += Stock::find($item['stock_id'])->price * $item['quantity'];
    //     }

    //     return (float)$amount;
    // }

    // protected function paymentStatus(float $amount_paid, float $subtotal, string $status): string
    // {
    //     if($amount_paid >= 1 && $amount_paid < $subtotal){
    //         return 'part paid';
    //     }

    //     if($amount_paid >= $subtotal){
    //         return 'paid';
    //     }

    //     if($amount_paid < 1){
    //         return 'unpaid';
    //     }

    //     return $status;
    // }
}
