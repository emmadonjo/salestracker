<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\DepartmentsRelationManager;
use App\Models\Department;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Component as Livewire;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Staff Mgt';
    protected static ?string $label = 'Staff';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('profile_photo')
                    ->directory('uploads/users/profile-photos')
                    ->maxSize(2048)->label('Profile Photo')
                    ->image()
                    ->imagePreviewHeight('250')
                    ->loadingIndicatorPosition('left')
                    ->panelAspectRatio('2:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->uploadProgressIndicatorPosition('left'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter Name'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('username@example.com'),
                TextInput::make('password')
                    ->password()
                    ->required(function(Livewire $livewire){
                        return $livewire instanceof CreateRecord;
                    })
                    ->minLength(8)
                    ->maxLength(255)
                    ->placeholder('********')
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->password()
                    ->required(function(Livewire $livewire){
                        return $livewire instanceof CreateRecord;
                    })
                    ->minLength(8)
                    ->maxLength(255)
                    ->placeholder('********')
                    ->label('Re-enter Password'),
                Toggle::make('status')
                    ->required(),
                Select::make('role')->label('Select Role')
                    ->placeholder('Select Role')
                    ->required()
                    ->options([
                        'admin' => 'Admin',
                        'cashier' => 'Cashier',
                        'receptionist' => 'Receptionist',
                        'driver' => 'Driver',
                        'waiter' => 'Waiter'
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sn')->rowIndex(),
                ImageColumn::make('profile_photo'),
                TextColumn::make('name'),
                TextColumn::make('email'),
                IconColumn::make('status')
                     ->boolean(),
                TextColumn::make('role'),
                // TextColumn::make('email_verified_at')
                //     ->label('Verified At')
                //     ->dateTime('d-m-Y'),
                TextColumn::make('created_at')->label('Date Added')
                    ->dateTime('d-m-Y'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
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
            DepartmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
