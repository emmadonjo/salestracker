<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Stock;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component as Livewire;

class EditOrder extends EditRecord
{

    protected static string $resource = OrderResource::class;

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
        logger($this->fo);
        logger($this->data);
        $amount_paid = $data['amount_paid'] ?? $record->amount_paid;
        $amount = $this->calculateAmount($this->data['items']);
        $subtotal = $amount - $data['discount'];
        $balance = $subtotal - $amount_paid;

        $record->update([
            'customer_id' => $data['customer_id'],
            'amount' => $amount,
            'discount' => $data['discount'],
            'status' => $this->paymentStatus($amount_paid, $subtotal, $data['status']),
            'amount_paid' => $amount_paid,
            'paid_at' => $data['paid_at'] ?? $this->record->paid_at,
            'subtotal' => $subtotal,
            'balance' => $balance,
        ]);

        if(count($this->data['items']) > 0){
            foreach($this->data['items'] as $item){
                $existing = $item['id'] ?  $record->items()->find($item['id']) : null;
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

    public function getTotal(): void
    {
        $items = $this->data['items'] ?? [];

        if(count($items) < 1){
            return;
        }

        $total = 0;

        foreach($items as $item){
            $stock = Stock::find($item['stock_id']);

            $total += $item['quantity'] * $stock->price;
        }

        $this->data['amount'] = $total;
    }

    public function getSubtotal(): float
    {

    }

    public function getBalance(): float
    {
        return (float)$this->data['subtotal'] - (float)$this->data['amount_paid'];
    }


    protected function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()
                ->schema([
                    Repeater::make('items')
                        ->label('Add Order Items')
                        ->relationship('items')
                        ->schema([
                        Select::make('stock_id')
                            ->label('Select Item')
                            ->options(Stock::all()->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Select Item'),
                        TextInput::make('quantity')
                            ->numeric()
                            ->label('Quantity')
                            ->minValue(1)->default(1)
                            ->placeholder('Quantity')
                ])->collapsible()
                ->createItemButtonLabel('Add Item')
                ->columns(2)
            ]),
            Grid::make()
                ->schema([
                    Select::make('customer_id')->label('Customer')
                        ->placeholder('Select Customer')
                        ->relationship('customer', 'name')
                        ->preload()
                        ->searchable(),
                    TextInput::make('amount')->label('Total')->numeric()
                        ->placeholder('Total Amount')
                        ->disabled()
                        ->helperText("Updated after submission if item(s) had changeds.")
                        ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                    TextInput::make('discount')->label('Discount')->numeric()
                        ->placeholder('Discount')
                        ->helperText('Enter discount amount if any.')
                        ->default(0)
                        ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                    TextInput::make('subtotal')->label('Subtotal')->numeric()
                        ->placeholder('Subtotal')
                        ->default(0)
                        ->disabled()
                        ->helperText("Updated after submission if item(s) had changeds.")
                        ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                    TextInput::make('amount_paid')
                        ->label('Amount Paid')->numeric()
                        ->placeholder('Amount Paid')
                        ->helperText('Enter the amount if the customer has paid')
                        ->default(0)
                        ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                    TextInput::make('balance')
                        ->label('Balance')->numeric()
                        ->placeholder('Balance')
                        ->default(0)
                        ->disabled()
                        ->helperText("Updated after submission if item(s) had changeds.")
                        ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                    Select::make('status')->label('Payment Status')->required()
                        ->placeholder('Select Status')->default('unpaid')->options([
                            'paid' => 'Paid',
                            'part paid' => 'Part Payment',
                            'unpaid' => 'Yet to Pay'
                        ])->disabled()
                        ->helperText("Updated after submission"),
                    DateTimePicker::make('paid_at')
                        ->label('Payment Date')
                        ->placeholder('Enter Date'),
                    Select::make('user_id')->label('Entered By')
                        ->placeholder('Select Staff')
                        ->relationship('staff', 'name')
                        ->preload()
                        ->searchable()
                        ->hidden(auth()->user()->role != 'admin')
                ])
        ]);
    }

    protected function paymentStatus(float $amount_paid, float $subtotal, string $status): string
    {
        if($amount_paid >= 1 && $amount_paid < $subtotal){
            return 'part paid';
        }

        if($amount_paid >= $subtotal){
            return 'paid';
        }

        if($amount_paid < 1){
            return 'unpaid';
        }

        return $status;
    }


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
