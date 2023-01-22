<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label('Order Ref.')->sortable(),
                TextColumn::make('customer.name')->label('Customer')->sortable(),
                TextColumn::make('amount')->label('Amount')->sortable(),
                TextColumn::make('discount')->label('Discount')->sortable(),
                TextColumn::make('subtotal')->label('Subtotal')->sortable(),
                TextColumn::make('amount_paid')->label('Amount Paid')->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'paid',
                        'primary' => 'part paid',
                        'danger' => 'unpaid'
                    ])->extraAttributes(['class' => 'uppercase'])
                    ->sortable(),
                TextColumn::make('staff.name')->label('Entered By')->sortable(),
                TextColumn::make('paid_at')->label('Date Paid')->sortable()->dateTime('d-m-y'),
                TextColumn::make('created_at')->label('Order Date')->sortable()->dateTime()
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
            //
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
