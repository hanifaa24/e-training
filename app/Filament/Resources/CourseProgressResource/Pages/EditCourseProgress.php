<?php

namespace App\Filament\Resources\CourseProgressResource\Pages;

use App\Filament\Resources\CourseProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseProgress extends EditRecord
{
    protected static string $resource = CourseProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
