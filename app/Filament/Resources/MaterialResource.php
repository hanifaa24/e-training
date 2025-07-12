<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 7;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_material');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_material');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('chapter_title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('order')
                    ->label('Order')
                    ->required()
                    ->numeric(),
                Select::make('course_id')
                    ->label('Course')
                    ->preload()
                    ->options(function () {
                        return Course::where('is_hidden', false) // hanya material yang tidak disembunyikan
                            ->whereDoesntHave('materials')          // hanya material yang belum punya quiz
                            ->pluck('name', 'id');       // ambil label dan id
                    })
                    ->searchable()
                    ->relationship('course', 'name') // sesuaikan dengan nama kolom di tabel course
                    ->required(),
                RichEditor::make('content')
                    ->label('Content')
                    ->required()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('materials')
                    ->fileAttachmentsVisibility('public')
                    ->columnSpanFull(),
                Select::make('publish')
                    ->label('Publish')
                    ->options([
                        1 => 'Published',
                        0 => 'Unpublish',
                    ])
                    ->required(),
                Forms\Components\Hidden::make('is_hidden')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chapter_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(30) // Batasi tampilan teks hingga 50 karakter
                    ->tooltip(fn($record) => $record->description)
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('publish')
                    ->label('Publish')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('publish')
                    ->label('Publish')
                    ->options([
                        1 => 'Published',
                        0 => 'Unpublish',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Material')
                    ->modalSubheading(function (Model $record) {
                        $questionCount = $record->questions()->count();
                        $quizCount = $record->quizzes()->count();

                        if ($questionCount === 0 && $quizCount === 0) {
                            return 'Are you sure you would like to delete this material?';
                        }

                        $message = "This material is linked to: ";
                        $parts = [];
                        if ($questionCount > 0) {
                            $parts[] = "$questionCount question(s)";
                        }
                        if ($quizCount > 0) {
                            $parts[] = "$quizCount quiz(zes)";
                        }

                        return $message . implode(' and ', $parts) . '. Are you sure you want to delete it?';
                    })
                    ->modalIcon(fn(Model $record) => $record->questions()->exists() || $record->quizzes()->exists()
                        ? 'heroicon-o-exclamation-triangle'
                        : 'heroicon-o-trash')
                    ->modalIconColor(fn(Model $record) => $record->questions()->exists() || $record->quizzes()->exists()
                        ? Color::Amber
                        : Color::Red)
                    ->modalButton('Confirm')
                    ->action(function (Model $record) {
                        $user = auth()->user();

                        // Disable log otomatis
                        Material::disableActivityLog();
                        \App\Models\Quiz::disableActivityLog();
                        \App\Models\Question::disableActivityLog();

                        // Update data
                        foreach ($record->questions as $question) {
                            $question->update(['is_hidden' => true]);
                        }

                        foreach ($record->quizzes as $quiz) {
                            $quiz->update(['is_hidden' => true]);
                        }

                        $record->update(['is_hidden' => true]);

                        // Enable log otomatis kembali
                        Material::enableActivityLog();
                        \App\Models\Quiz::enableActivityLog();
                        \App\Models\Question::enableActivityLog();

                        // Buat log manual
                        foreach ($record->questions as $question) {
                            activity('Resource')
                                ->performedOn($question)
                                ->causedBy($user)
                                ->event('deleted')
                                ->log('Deleted');
                        }

                        foreach ($record->quizzes as $quiz) {
                            activity('Resource')
                                ->performedOn($quiz)
                                ->causedBy($user)
                                ->event('deleted')
                                ->log('Deleted');
                        }

                        activity('Resource')
                            ->performedOn($record)
                            ->causedBy($user)
                            ->event('deleted')
                            ->log('Deleted');

                        Notification::make()
                            ->title('Material Deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        $user = auth()->user();

                        // Disable log otomatis di semua model
                        Material::disableActivityLog();
                        \App\Models\Quiz::disableActivityLog();
                        \App\Models\Question::disableActivityLog();

                        foreach ($records as $material) {
                            // Hide questions
                            foreach ($material->questions as $question) {
                                $question->update(['is_hidden' => true]);
                            }

                            // Hide quizzes
                            foreach ($material->quizzes as $quiz) {
                                $quiz->update(['is_hidden' => true]);
                            }

                            // Hide material
                            $material->update(['is_hidden' => true]);
                        }

                        // Enable log otomatis lagi
                        Material::enableActivityLog();
                        \App\Models\Quiz::enableActivityLog();
                        \App\Models\Question::enableActivityLog();

                        // Log manual
                        foreach ($records as $material) {
                            foreach ($material->questions as $question) {
                                activity('Resource')
                                    ->performedOn($question)
                                    ->causedBy($user)
                                    ->event('deleted')
                                    ->log('Deleted');
                            }

                            foreach ($material->quizzes as $quiz) {
                                activity('Resource')
                                    ->performedOn($quiz)
                                    ->causedBy($user)
                                    ->event('deleted')
                                    ->log('Deleted');
                            }

                            activity('Resource')
                                ->performedOn($material)
                                ->causedBy($user)
                                ->event('deleted')
                                ->log('Deleted');
                        }

                        Notification::make()
                            ->title('Materials Deleted')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Delete Selected Materials')
                    ->modalSubheading(function ($records) {
                        $hasLinkedItems = $records->filter(function ($material) {
                            return $material->questions()->exists() || $material->quizzes()->exists();
                        })->isNotEmpty();

                        return $hasLinkedItems
                            ? 'Some materials are linked to questions or quizzes. Are you sure you want to delete them?'
                            : 'Are you sure you want to delete the selected materials?';
                    })
                    ->modalIcon(function ($records) {
                        $hasLinkedItems = $records->filter(function ($material) {
                            return $material->questions()->exists() || $material->quizzes()->exists();
                        })->isNotEmpty();

                        return $hasLinkedItems ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-trash';
                    })
                    ->modalIconColor(function ($records) {
                        $hasLinkedItems = $records->filter(function ($material) {
                            return $material->questions()->exists() || $material->quizzes()->exists();
                        })->isNotEmpty();

                        return $hasLinkedItems ? Color::Amber : Color::Red;
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
