<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Validation\Rules\Closure as ClosureValidationRule;
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;
use App\Models\Material;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 9;
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_any_question');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_question');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('material_id')
                    ->label('Material')
                    ->preload()
                    ->searchable()
                    ->relationship('material', 'chapter_title')
                    ->options(function () {
                        return Material::where('is_hidden', false)
                            ->has('quizzes')
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
                Forms\Components\TextInput::make('duration')
                    ->label('Duration (second)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->suffix('second'),
                Forms\Components\Textarea::make('question')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->directory('questions')
                    ->image()
                    ->imageEditor() // aktifkan crop, rotate, zoom
                    ->imagePreviewHeight('150') // pratinjau
                    //->panelAspectRatio('1:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadProgressIndicatorPosition('left')
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        return (string) \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                    })
                    ->default(fn($record) => $record?->image),
                Forms\Components\TextInput::make('answer')
                    ->required(),
                Repeater::make('other_option')
                    ->label('Other Options')
                    ->schema([
                        TextInput::make('value')
                            ->label('Option')
                            ->required(),
                    ])
                    ->addActionLabel('➕ Add Option')
                    ->maxItems(3)
                    ->minItems(1)
                    ->defaultItems(1)
                    ->columns(1)
                    ->required()

                    // Filter dan pastikan hanya array string sebelum disimpan
                    ->beforeStateDehydrated(function ($state) {
                        return collect($state)
                            ->map(function ($item) {
                                if (is_array($item)) {
                                    return $item['value'] ?? null;
                                }
                                return $item; // fallback untuk string
                            })
                            ->filter()
                            ->values()
                            ->toArray(); // hasil: ["Option A", "Option B", "Option C"]
                    })

                    // Saat diedit, ubah string menjadi ['value' => ...] agar cocok schema
                    ->afterStateHydrated(function ($component, $state) {
                        // Jika state adalah array string, ubah ke array of object
                        if (is_array($state) && isset($state[0]) && is_string($state[0])) {
                            $component->state(
                                collect($state)
                                    ->map(fn($item) => ['value' => $item])
                                    ->toArray()
                            );
                        }
                    })

                    // Sembunyikan tombol "Add Option" jika sudah 3 item
                    ->disableItemCreation(fn($get) => count($get('other_option') ?? []) >= 3),
                Forms\Components\Hidden::make('is_hidden')
                    ->default(false),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['other_option']) && is_array($data['other_option'])) {
            $data['other_option'] = collect($data['other_option'])
                ->map(fn($value) => ['value' => $value])
                ->toArray();
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->limit(50) // Batasi tampilan teks hingga 50 karakter
                    ->tooltip(fn($record) => $record->question)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('answer')
                    ->sortable(),
                Tables\Columns\TextColumn::make('material.chapter_title')
                    ->label('Materials')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state . ' seconds'),

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
                            ->title('Question Deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        Notification::make()
                            ->title('Questions Deleted')
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
