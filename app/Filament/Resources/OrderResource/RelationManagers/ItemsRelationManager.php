<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'stock_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('stock_id')
                    ->label('Select Item')
                    ->relationship('stock', 'name')
                    ->preload()
                    ->searchable()
                    ->placeholder('Select Stock')
                    ->required(),
                TextInput::make('qauntity')
                    ->required()
                    ->default(1)
                    ->minValue(1)
                    ->numeric()
                    ->placeholder('Enter Qty.')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stock_id')
                    ->label('Stock Item'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
