<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label("Customer's Name")->placeholder('Enter Name')
                    ->minLength(2)->maxLength(255)->required(),
                TextInput::make('email')->email()->maxLength(255)
                    ->label("Customer's Email")->placeholder('username@example.com')
                    ->unique(ignoreRecord:true),
                TextInput::make('phone')->tel()->maxLength(14)
                    ->minLength(8)
                    ->label("Phone No.")->placeholder('Phone No.')
                    ->unique(ignoreRecord:true),
                TextInput::make('address')->maxLength(300)
                    ->label("Residential Address")->placeholder('Enter Address'),
                TextInput::make('alias')->maxLength(60)
                    ->label("Alias")->placeholder('Enter Alias')
                    ->helperText('Any info to help identify the customer. E.g. nickname'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('name')->label('Customer Name')->sortable(),
                TextColumn::make('alias')->label('Customer Alias')->sortable(),
                TextColumn::make('phone')->label('Phone No.')->sortable(),
                TextColumn::make('email')->label('Email')->sortable(),
                TextColumn::make('address')->label('Residential Address'),
                TextColumn::make('created_at')->label('Date Added')
                    ->dateTime('d-m-Y')->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
               ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
               ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }
}
