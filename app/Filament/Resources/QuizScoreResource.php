<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizScoreResource\Pages;
use App\Filament\Resources\QuizScoreResource\RelationManagers;
use App\Models\QuizScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Progress;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Facades\Auth;
class QuizScoreResource extends Resource
{
    protected static ?string $model = QuizScore::class;

    protected static ?string $navigationLabel = 'Quiz';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $pluralModelLabel = 'Quiz';
    // protected static ?int $navigationSort = 11;


    protected static ?string $cluster = Progress::class;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_quiz::score');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_quiz::score');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.employee.name')
                    ->label('Teacher')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(function ($state) {
                        return rtrim(rtrim(number_format($state, 2, '.', ''), '0'), '.');
                    }),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(function ($state) {
                        return "{$state} Sec";
                    })
                    ->sortable(),
                //Tables\Columns\TextColumn::make('duration')->label('Duration'),
                BadgeColumn::make('status')
                    ->label('Status')->colors([
                            'success' => 1,
                            'danger' => 0,
                        ])
                    ->formatStateUsing(fn($state) => $state == 1 ? 'Passed' : 'Failed'),
                Tables\Columns\TextColumn::make('material.chapter_title')
                    ->label('Material')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->sortable(),
                //->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Passed',
                        0 => 'Failed',
                    ]),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListQuizScores::route('/'),
            'create' => Pages\CreateQuizScore::route('/create'),
            'edit' => Pages\EditQuizScore::route('/{record}/edit'),
        ];
    }
}
