<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-user';

    protected static ?int $navigationSort = 3;
    // public static function shouldRegisterNavigation(): bool
    // {
    //     return Auth::user()?->can('view_any_user');
    // }
    // public static function canViewAny(): bool
    // {
    //     return auth()->user()?->can('view_any_user');
    // }
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->with(['employee', 'roles']);
    // }
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->label('Email')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->dehydrateStateUsing(function ($state, $context) {
                        // Jika sedang create dan input password kosong, pakai default password
                        if ($context === 'create' && empty($state)) {
                            return Hash::make('training2025');
                        }

                        // Jika input password ada, hash dan simpan
                        if (!empty($state)) {
                            return Hash::make($state);
                        }

                        // Jika update dan kosong, jangan ubah password
                        return null;
                    })
                    ->dehydrated(fn($state, $context) => $context === 'create' || filled($state)),
                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    //->multiple()
                    ->preload()
                    ->searchable()
                    ->required(),

                Select::make('employee_id')
                    ->label('Employee')
                    ->preload()
                    ->searchable()
                    ->relationship('employee', 'name') // sesuaikan dengan nama kolom di tabel employees
                    ->required(),

                Select::make('status')
                    ->label('Account Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->preload()
                    ->required(),

                // Toggle::make('status')
                //     ->label('Active')
                //     ->inline(false)
                //     ->onColor('success')
                //     ->offColor('danger'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable()
                    ->default('No Employee'),

                TextColumn::make('roles')
                    ->label('Roles')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->roles
                            ->pluck('name')
                            ->map(function ($name) {
                                return ucwords(str_replace('_', ' ', $name));
                            })
                            ->join(', ');
                    })
                    ->searchable()
                    ->sortable()
                    ->default('No Role'),

                ToggleColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger'),
                // ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),   // tombol edit
                DeleteAction::make()
                    ->action(function ($records) {
                        Notification::make()
                            ->title('User Deleted')
                            ->success()
                            ->send();
                    }),                // tombol delete
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        Notification::make()
                            ->title('Users Deleted')
                            ->success()
                            ->send();
                    }),
            ]);
        // ->bulkActions([
        //     Tables\Actions\DeleteBulkAction::make(), // tombol delete massal (bulk)
        // ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
