<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Stock;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('photo')->directory('uploads/stocks')->maxSize(2048),
                Select::make('category_id')
                    ->relationship('category', 'name')->label('Select Category')
                    ->required()->placeholder('Select Category'),
                TextInput::make('sku')->label('SKU')->default(fn() => Str::lower(Str::random(8)))
                    ->dehydrated(true),
                Select::make('user_id')
                    ->options(User::all()->pluck('name', 'id'))
                    ->label('Added By')->placeholder('Select Staff')
                    ->default(auth()->id())->searchable(),
                TextInput::make('name')->placeholder('Enter Name')
                    ->required()
                    ->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('quantity')->numeric()->placeholder('Quantity')
                    ->required()->minValue(0),
                TextInput::make('price')->numeric()->step('0.01')->placeholder('Price')
                    ->required()->minValue(0),
                MarkdownEditor::make('description')->placeholder('1000 characters max')
                    ->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')->label('Image'),
                TextColumn::make('name'),
                TextColumn::make('sku'),
                TextColumn::make('quantity'),
                TextColumn::make('price'),
                TextColumn::make('category.name'),
                TextColumn::make('added_by.name')->label('Added By'),
                TextColumn::make('description'),
                TextColumn::make('created_at')->label('Date Added')
                    ->dateTime('d-m-Y')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'view' => Pages\ViewStock::route('/{record}'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
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
