<?php

namespace App\Filament\Resources\CourseProgressResource\Pages;

use App\Filament\Resources\CourseProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseProgress extends ListRecords
{
    protected static string $resource = CourseProgressResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
