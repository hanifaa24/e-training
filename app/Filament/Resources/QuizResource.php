<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers;
use App\Models\Quiz;
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
use App\Models\Material;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?int $navigationSort = 8;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_quiz');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_quiz');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('material_id')
                    ->label('Material')
                    ->preload()
                    ->searchable()
                    ->relationship('material', 'chapter_title') // sesuaikan dengan nama kolom di tabel 
                    // ->options(function () {
                    //     return Material::where('is_hidden', false) // hanya material yang tidak disembunyikan
                    //         ->whereDoesntHave('quizzes')          // hanya material yang belum punya quiz
                    //         ->pluck('chapter_title', 'id');       // ambil label dan id
                    // })
                    ->options(function () {
                        return Material::where('is_hidden', false)
                            ->whereDoesntHave('quizzes')
                            ->with('course')
                            ->get()
                            ->sortBy(function ($material) {
                                return strtolower($material->course->name . ' ' . $material->order);
                            })
                            ->mapWithKeys(function ($material) {
                                $label = 'Chapter ' . $material->order . '. ' . $material->chapter_title . ' (' . $material->course->name . ')';
                                return [$material->id => $label];
                            });
                    })
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('questions')
                    ->label('Number of Question')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('pass_score')
                    ->label('Pass Score')
                    ->required()
                    ->numeric(),
                Forms\Components\Hidden::make('is_hidden')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('material.chapter_title')
                    ->label('Materials')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50) // Batasi tampilan teks hingga 50 karakter
                    ->tooltip(fn($record) => $record->description),
                Tables\Columns\TextColumn::make('pass_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('questions')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($records) {
                        Notification::make()
                            ->title('Quiz Deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        Notification::make()
                            ->title('Quizzes Deleted')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
