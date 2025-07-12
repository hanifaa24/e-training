<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_subject');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_subject');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('is_hidden')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50) // Batasi tampilan teks hingga 50 karakter
                    ->tooltip(fn($record) => $record->description)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Course')
                    ->modalSubheading(function (Model $record) {
                        $courseNames = $record->courses()->pluck('name')->toArray();
                        $courseCount = $record->courses()->count();

                        if (count($courseNames) === 0) {
                            return 'Are you sure you would like to do this?';
                        }

                        $message = "This subject is related to: ";
                        $parts = [];
                        if ($courseCount > 0) {
                            $parts[] = "{$courseCount} course(s)";
                        }

                        return $message . implode(' and ', $parts) . ". Are you sure you want to delete it?";
                    })
                    ->modalIcon(function (Model $record) {
                        return $record->courses()->count() > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-trash';
                    })
                    ->modalIconColor(function (Model $record) {
                        return $record->courses()->count() > 0 ? Color::Amber : Color::Red;
                    })
                    ->modalButton('Confirm')
                    ->before(function (Model $record) {
                        // Custom logic before delete
                    })
                    ->action(function (Model $record) {
                        $user = auth()->user();

                        // Disable log otomatis
                        Subject::disableActivityLog();
                        \App\Models\Course::disableActivityLog();
                        \App\Models\Material::disableActivityLog();
                        \App\Models\Quiz::disableActivityLog();
                        \App\Models\Question::disableActivityLog();

                        // Hide data
                        foreach ($record->courses as $course) {
                            foreach ($course->materials as $material) {
                                foreach ($material->questions as $question) {
                                    $question->update(['is_hidden' => true]);
                                }

                                foreach ($material->quizzes as $quiz) {
                                    $quiz->update(['is_hidden' => true]);
                                }

                                $material->update(['is_hidden' => true]);
                            }

                            $course->update(['is_hidden' => true]);
                        }

                        $record->update(['is_hidden' => true]);

                        // Enable log lagi
                        Subject::enableActivityLog();
                        \App\Models\Course::enableActivityLog();
                        \App\Models\Material::enableActivityLog();
                        \App\Models\Quiz::enableActivityLog();
                        \App\Models\Question::enableActivityLog();

                        // Log manual (event deleted)
                        foreach ($record->courses as $course) {
                            foreach ($course->materials as $material) {
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

                            activity('Resource')
                                ->performedOn($course)
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
                            ->title('Subject Deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        $user = auth()->user();

                        // Disable log otomatis
                        Subject::disableActivityLog();
                        \App\Models\Course::disableActivityLog();
                        \App\Models\Material::disableActivityLog();
                        \App\Models\Quiz::disableActivityLog();
                        \App\Models\Question::disableActivityLog();

                        foreach ($records as $record) {
                            foreach ($record->courses as $course) {
                                foreach ($course->materials as $material) {
                                    foreach ($material->questions as $question) {
                                        $question->update(['is_hidden' => true]);
                                    }

                                    foreach ($material->quizzes as $quiz) {
                                        $quiz->update(['is_hidden' => true]);
                                    }

                                    $material->update(['is_hidden' => true]);
                                }

                                $course->update(['is_hidden' => true]);
                            }

                            $record->update(['is_hidden' => true]);
                        }

                        // Enable log lagi
                        Subject::enableActivityLog();
                        \App\Models\Course::enableActivityLog();
                        \App\Models\Material::enableActivityLog();
                        \App\Models\Quiz::enableActivityLog();
                        \App\Models\Question::enableActivityLog();

                        // Log manual sebagai Deleted
                        foreach ($records as $record) {
                            foreach ($record->courses as $course) {
                                foreach ($course->materials as $material) {
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

                                activity('Resource')
                                    ->performedOn($course)
                                    ->causedBy($user)
                                    ->event('deleted')
                                    ->log('Deleted');
                            }

                            activity('Resource')
                                ->performedOn($record)
                                ->causedBy($user)
                                ->event('deleted')
                                ->log('Deleted');
                        }

                        Notification::make()
                            ->title('Subjects Deleted')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Delete selected subjects')
                    ->modalSubheading(function ($records) {
                        $hasCourse = $records->filter(fn($record) => $record->courses()->count() > 0)->isNotEmpty();

                        if ($hasCourse) {
                            return 'Some subjects are related to courses. Are you sure you want to delete them?';
                        }

                        return 'Are you sure you want to delete the selected subjects?';
                    })
                    ->modalIcon(function ($records) {
                        $hasCourse = $records->filter(fn($record) => $record->courses()->count() > 0)->isNotEmpty();
                        return $hasCourse ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-trash';
                    })
                    ->modalIconColor(function ($records) {
                        $hasCourse = $records->filter(fn($record) => $record->courses()->count() > 0)->isNotEmpty();
                        return $hasCourse ? Color::Amber : Color::Red;
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->where('is_hidden', false);
    // }
}
