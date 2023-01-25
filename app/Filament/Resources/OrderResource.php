<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Illuminate\Support\Str;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput\Mask;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('items')
                    ->label('Add Order Items')
                    ->relationship('items')
                    ->schema([
                        Select::make('stock_id')
                            ->relationship('stock', 'name')
                            ->preload()
                            ->label('Select Item')
                            ->placeholder('Select Item'),
                        TextInput::make('quantity')
                            ->numeric()
                            ->label('Quantity')
                            ->minValue(1)->default(1)
                            ->placeholder('Quantity')
                    ])->collapsible()
                    ->createItemButtonLabel('Add Item')
                    ->columns(2),
                TextInput::make('reference')
                    ->label('Order Ref.')
                    ->default(Str::lower(Str::random()))
                    ->disabled(),
                TextInput::make('amount')
                    ->label('Order Amount')
                    ->numeric()
                    ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                TextInput::make('discount')
                    ->label('Order Discount')
                    ->numeric()
                    ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                TextInput::make('subtotal')
                    ->label('Subtotal')->numeric()
                    ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                TextInput::make('amount_paid')
                    ->label('Amount Paid')->numeric()
                    ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                TextInput::make('balance')->label('Payment Balance')
                    ->numeric()
                    ->mask(fn(Mask $mask) => $mask->money(prefix:'₦')),
                TextInput::make('status')
                    ->label('Payment Status'),
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name'),
                DateTimePicker::make('paid_at')
                    ->label('Payment Date'),
                Select::make('user_id')
                    ->label('Added By')
                    ->relationship('staff', 'name'),
                DateTimePicker::make('created_at')
                    ->label('Order Date'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('Sn')
                    ->rowIndex(isFromZero:false),
                TextColumn::make('reference')
                    ->label('Order Ref.')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')->sortable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Total')
                    ->sortable()
                    ->money('ngn', true),
                TextColumn::make('discount')
                    ->label('Discount')
                    ->sortable()
                    ->money('ngn', true),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->sortable()
                    ->money('ngn', true),
                TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->sortable()
                    ->money('ngn', true),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->sortable()
                    ->money('ngn', true),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'paid',
                        'primary' => 'part paid',
                        'danger' => 'unpaid'
                    ])
                    ->extraAttributes(['class' => 'uppercase'])
                    ->sortable(),
                TextColumn::make('staff.name')
                    ->label('Entered By')->sortable(),
                TextColumn::make('paid_at')
                    ->label('Date Paid')
                    ->sortable()->dateTime('d-m-y'),
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->sortable()
                    ->dateTime()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('status')->options([
                    'paid' => 'Paid',
                    'part paid' => 'Part Payment',
                    'unpaid' => 'Yet to Pay'
                ]),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('From:'),
                        DatePicker::make('to')->label('To:')->default(now()),
                    ])
                    ->query(function(Builder $builder, array $data): Builder {
                        return $builder->when(
                            $data['from'], fn(Builder $builder, $date): Builder => $builder->whereDate('created_at', '>=', $date)
                        );
                    })
                    ->query(function(Builder $builder, array $data): Builder {
                        return $builder->when(
                            $data['to'], fn(Builder $builder, $date): Builder => $builder->whereDate('created_at', '<=', $date)
                        );
                    })
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
