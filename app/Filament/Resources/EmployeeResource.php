<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use App\Forms\Components\YearPicker;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 4;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_employee');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_employee');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->required(),

                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->required()
                    ->maxLength(15)
                    ->numeric(),

                Select::make('status')
                    ->label('Employee Status')
                    ->options([
                        'Active' => 'Active',
                        'Resigned' => 'Resigned',
                    ])
                    ->preload()
                    ->required(),

                DatePicker::make('recruitment_date')
                    ->label('Recruitment Date')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => strtolower($state) === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn(string $state): string => strtolower($state) === 'active' ? 'Active' : 'Resigned'),
                Tables\Columns\TextColumn::make('recruitment_date')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->translatedFormat('d F Y')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Active' => 'Active',
                        'Resigned' => 'Resigned',
                    ]),
                Tables\Filters\SelectFilter::make('recruitement_year') // Beri nama yang jelas, misal 'recruitement_year'
                    ->label('Recruitment Date')
                    ->options(function (): array {
                        // Ambil semua tahun unik dari kolom 'recruitement_date' di database Anda
                        // Urutkan dari tahun terbaru ke terlama
                        return Employee::query() // Ganti YourModel dengan nama model Anda
                            ->selectRaw('DISTINCT YEAR(recruitment_date) as year')
                            ->pluck('year', 'year')
                            ->sortDesc()
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        // Jika ada tahun yang dipilih, tambahkan kondisi WHERE YEAR()
                        if ($data['value'] ?? null) {
                            $query->whereYear('recruitment_date', $data['value']);
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        // Tampilkan indikator filter aktif
                        if ($data['value'] ?? null) {
                            return 'Recruitment Date: ' . $data['value'];
                        }
                        return null;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Employee')
                    ->modalSubheading(function (Model $record) {
                        $userNames = $record->users()->pluck('name')->toArray();
                        $userCount = $record->users()->count();

                        if (count($userNames) === 0) {
                            return 'Are you sure you would like to do this?';
                        }

                        $message = "This employee is related to: ";
                        $parts = [];
                        if ($userCount > 0) {
                            $parts[] = "{$userCount} user(s)";
                        }

                        return $message . implode(' and ', $parts) . ". Are you sure you want to delete it?";
                    })
                    ->modalIcon(function (Model $record) {
                        return $record->users()->count() > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-trash';
                    })
                    ->modalIconColor(function (Model $record) {
                        return $record->users()->count() > 0 ? Color::Amber : Color::Red;
                    })
                    ->modalButton('Confirm')
                    ->before(function (Model $record) {
                        // Custom logic before delete
                    })
                    ->action(function (Model $record) {
                        $record->users()->delete();
                        $record->delete();
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->users()->delete();
                            $record->delete();
                        }

                        Notification::make()
                            ->title('Employees deleted')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Delete selected employees')
                    ->modalSubheading(function ($records) {
                        $hasUser = $records->filter(fn($record) => $record->users()->count() > 0)->isNotEmpty();

                        if ($hasUser) {
                            return 'Some employees are related to users. Are you sure you want to delete them?';
                        }

                        return 'Are you sure you want to delete the selected employees?';
                    })
                    ->modalIcon(function ($records) {
                        $hasUser = $records->filter(fn($record) => $record->users()->count() > 0)->isNotEmpty();
                        return $hasUser ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-trash';
                    })
                    ->modalIconColor(function ($records) {
                        $hasUser = $records->filter(fn($record) => $record->users()->count() > 0)->isNotEmpty();
                        return $hasUser ? Color::Amber : Color::Red;
                    })
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
