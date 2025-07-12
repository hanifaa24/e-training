<?php

namespace App\Filament\Resources\TeacherCourseResource\Pages;

use App\Filament\Resources\TeacherCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherCourses extends ListRecords
{
    protected static string $resource = TeacherCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
