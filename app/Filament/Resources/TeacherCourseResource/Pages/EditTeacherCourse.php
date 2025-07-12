<?php

namespace App\Filament\Resources\TeacherCourseResource\Pages;

use App\Filament\Resources\TeacherCourseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherCourse extends EditRecord
{
    protected static string $resource = TeacherCourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
