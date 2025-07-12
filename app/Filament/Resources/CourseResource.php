<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_course');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_course');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('subject_id')
                    ->label('Subject')
                    ->preload()
                    ->searchable()
                    ->relationship('subject', 'name') // sesuaikan dengan nama kolom di tabel subjects
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->directory('courses')
                    ->image()
                    ->imageEditor() // aktifkan crop, rotate, zoom
                    ->imagePreviewHeight('150') // pratinjau
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadProgressIndicatorPosition('left')
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        return (string) \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                    })
                    ->default(fn($record) => $record?->image) // <<< Tambahkan ini
                    ->required(),
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
            ->query(Course::query()->visible())
            ->columns([
                Tables\Columns\ImageColumn::make('imageUrl')
                    ->label('Image')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->name)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->description)
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->subject->name)
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('end_date')
                //     ->label('End Date')
                //     ->date()
                //     ->formatStateUsing(fn($state) => Carbon::parse($state)->translatedFormat('d F Y'))
                //     ->searchable()
                //     ->sortable(),
                Tables\Columns\ToggleColumn::make('publish')
                    ->label('Publish')
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
                    ->modalHeading('Delete Course')
                    ->modalSubheading(function (Model $record) {
                        $materialNames = $record->materials()->pluck('chapter_title')->toArray();
                        $materialCount = $record->materials()->count();

                        if (count($materialNames) === 0) {
                            return 'Are you sure you would like to do this?';
                        }

                        $message = "This course is related to: ";
                        $parts = [];
                        if ($materialCount > 0) {
                            $parts[] = "{$materialCount} material(s)";
                        }

                        return $message . implode(' and ', $parts) . ". Are you sure you want to delete it?";
                    })
                    ->modalIcon(function (Model $record) {
                        return $record->materials()->count() > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-trash';
                    })
                    ->modalIconColor(function (Model $record) {
                        return $record->materials()->count() > 0 ? Color::Amber : Color::Red;
                    })
                    ->modalButton('Confirm')
                    ->before(function (Model $record) {
                        // Custom logic before delete
                    })
                    //->action(fn (Model $record) => $record->delete()),
                    ->action(function (Model $record) {
                        $user = auth()->user();

                        // Disable log otomatis
                        \App\Models\Material::disableActivityLog();
                        \App\Models\Quiz::disableActivityLog();
                        \App\Models\Question::disableActivityLog();
                        Course::disableActivityLog();

                        // Loop materials
                        foreach ($record->materials as $material) {
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

                        // Hide course
                        $record->update(['is_hidden' => true]);

                        // Enable log lagi
                        \App\Models\Material::enableActivityLog();
                        \App\Models\Quiz::enableActivityLog();
                        \App\Models\Question::enableActivityLog();
                        Course::enableActivityLog();

                        // Log manual
                        foreach ($record->materials as $material) {
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
                            ->performedOn($record)
                            ->causedBy($user)
                            ->event('deleted')
                            ->log('Deleted');

                        Notification::make()
                            ->title('Course Deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        $user = auth()->user();

                        // Disable log otomatis
                        \App\Models\Course::disableActivityLog();
                        \App\Models\Material::disableActivityLog();
                        \App\Models\Quiz::disableActivityLog();
                        \App\Models\Question::disableActivityLog();

                        // Update hide
                        foreach ($records as $record) {
                            foreach ($record->materials as $material) {
                                foreach ($material->questions as $question) {
                                    $question->update(['is_hidden' => true]);
                                }

                                foreach ($material->quizzes as $quiz) {
                                    $quiz->update(['is_hidden' => true]);
                                }

                                $material->update(['is_hidden' => true]);
                            }

                            $record->update(['is_hidden' => true]);
                        }

                        // Enable log lagi
                        \App\Models\Course::enableActivityLog();
                        \App\Models\Material::enableActivityLog();
                        \App\Models\Quiz::enableActivityLog();
                        \App\Models\Question::enableActivityLog();

                        // Log manual
                        foreach ($records as $record) {
                            foreach ($record->materials as $material) {
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
                                ->performedOn($record)
                                ->causedBy($user)
                                ->event('deleted')
                                ->log('Deleted');
                        }

                        Notification::make()
                            ->title('Courses Deleted')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Delete selected courses')
                    ->modalSubheading(function ($records) {
                        $hasMaterial = $records->filter(fn($record) => $record->materials()->count() > 0)->isNotEmpty();

                        if ($hasMaterial) {
                            return 'Some courses are related to materials. Are you sure you want to delete them?';
                        }

                        return 'Are you sure you want to delete the selected courses?';
                    })
                    ->modalIcon(function ($records) {
                        $hasMaterial = $records->filter(fn($record) => $record->materials()->count() > 0)->isNotEmpty();
                        return $hasMaterial ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-trash';
                    })
                    ->modalIconColor(function ($records) {
                        $hasMaterial = $records->filter(fn($record) => $record->materials()->count() > 0)->isNotEmpty();
                        return $hasMaterial ? Color::Amber : Color::Red;
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
