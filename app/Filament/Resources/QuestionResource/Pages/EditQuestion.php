<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\Material;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    protected function mutateFormDataBeforeSave(array $data): array
{
    $data['updated_by'] = auth()->id();
    return $data;
}
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function beforeSave(): void
    {
        $materialId = $this->form->getState()['material_id'] ?? null;

        if ($materialId) {
            $material = Material::withCount('quizzes')->find($materialId);

            if (!$material || $material->quizzes_count === 0) {
                Notification::make()
                    ->title('You have to add a quiz for this material first.')
                    ->danger()
                    ->send();

                $this->halt(); // menghentikan proses edit
            }
        }
    }
}
