<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingResource\Pages;
use App\Filament\Resources\TrainingResource\RelationManagers;
use App\Models\Training;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static ?string $navigationLabel = 'Training';

    protected static ?string $navigationIcon = 'heroicon-s-video-camera';

    protected static ?int $navigationSort = 13;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_training');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('file')
                    ->disk('public')
                    ->directory('trainings')
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state instanceof UploadedFile) {
                            $set('original_filename', $state->getClientOriginalName());
                        }
                    }),

                Forms\Components\Hidden::make('original_filename'),

                Forms\Components\TextInput::make('link')
                    ->url()
                    ->required(),

                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->preload()
                    ->searchable()
                    ->relationship('course', 'name') // sesuaikan dengan nama kolom di tabel course
                    ->required(),

                Forms\Components\TimePicker::make('time')
                    ->label('Time (WIB)')
                    ->required()
                    ->withoutSeconds(),

                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\Select::make('employee_id')
                    ->label('Assigned Teacher')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return Employee::whereHas('userAcc') // hanya employee yang punya user
                            ->pluck('name', 'id');
                    })
                    ->getOptionLabelsUsing(function (array $values): array {
                        return Employee::whereIn('id', $values)->pluck('name', 'id')->toArray();
                    })
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Scheduled',
                        2 => 'Completed',
                        3 => 'Canceled',
                    ])
                    ->required(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->translatedFormat('d F Y')),
                Tables\Columns\TextColumn::make('time')
                    ->time()
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::createFromFormat('H:i:s', $state)->format('H.i') . ' WIB';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->limit(30)
                    ->getStateUsing(fn($record) => $record->course?->name)
                    ->color(fn($record) => $record->course?->is_hidden ? 'danger' : 'success')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->course?->is_hidden ? $state : $state;
                    })
                    ->tooltip(fn($record) => $record->course?->name)
                    ->sortable(),
                Tables\Columns\TextColumn::make('link')
                    ->label('Link')
                    ->limit(20)
                    ->tooltip(fn($record) => $record->link)
                    ->color('info')
                    ->url(fn($record) => $record->link)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('file')
                    ->label('File')
                    ->url(fn($record) => $record->file_url)
                    ->openUrlInNewTab()
                    ->color('info')
                    ->formatStateUsing(fn() => 'Download'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 1,
                        'success' => 2,
                        'danger' => 3,
                    ])
                    ->formatStateUsing(fn($record) => match ($record->status) {
                        1 => 'Scheduled',
                        2 => 'Completed',
                        3 => 'Canceled',
                        default => '',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Scheduled',
                        2 => 'Completed',
                        3 => 'Canceled',
                    ]),
                    
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($records) {
                        Notification::make()
                            ->title('Training Deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        Notification::make()
                            ->title('Trainings Deleted')
                            ->success()
                            ->send();
                    }),
            ]);
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DeleteBulkAction::make(),
        //     ]),
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
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
        ];
    }
}
