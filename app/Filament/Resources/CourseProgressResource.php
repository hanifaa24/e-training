<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseProgressResource\Pages;
use App\Filament\Resources\CourseProgressResource\RelationManagers;
use App\Models\CourseProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Progress;
use Illuminate\Support\Facades\Auth;
use RyanChandler\FilamentProgressColumn\ProgressColumn;
use App\Models\Course;
use App\Models\QuizScore;

class CourseProgressResource extends Resource
{
    protected static ?string $model = CourseProgress::class;

    protected static ?string $navigationLabel = 'Course';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    //protected static ?int $navigationSort = 10;

    protected static ?string $cluster = Progress::class;
    protected static ?string $pluralModelLabel = 'Course';
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_course::progress');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_course::progress');
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

                ProgressColumn::make('progress')
                    ->label('Progress')
                    ->color(fn(int $state) => match (true) {
                        $state < 40 => 'danger',
                        $state < 70 => 'warning',
                        default => 'success',
                    })
                    ->sortable()
                    ->extraAttributes(['style' => 'min-width:100px']),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(function ($state) {
                        return "{$state} Days";
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.name')
                    ->label('Course')
                    ->limit(20) // Batasi tampilan teks hingga 50 karakter
                    ->tooltip(fn($record) => $record->course->name)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('material.chapter_title')
                    ->label('Material')
                    ->limit(20) // Batasi tampilan teks hingga 50 karakter
                    ->tooltip(fn($record) => $record->material->chapter_title)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    function updateCourseProgress($userId, $courseId)
    {
        $totalMaterials = Course::findOrFail($courseId)->materials()->where('publish', true)->count();

        if ($totalMaterials === 0) {
            $progress = 0;
        } else {
            $passedMaterials = QuizScore::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('status', 1) // Hanya yang lulus
                ->distinct('material_id')
                ->count('material_id');

            $progress = ($passedMaterials / $totalMaterials) * 100;
        }

        CourseProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['progress_percentage' => round($progress, 2)]
        );
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
            'index' => Pages\ListCourseProgress::route('/'),
            'create' => Pages\CreateCourseProgress::route('/create'),
            'edit' => Pages\EditCourseProgress::route('/{record}/edit'),
        ];
    }
}
