<?php

namespace App\Filament\Resources\QuizScoreResource\Pages;

use App\Filament\Resources\QuizScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuizScores extends ListRecords
{
    protected static string $resource = QuizScoreResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
