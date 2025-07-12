<?php

namespace Rmsramos\Activitylog\Resources\ActivitylogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Rmsramos\Activitylog\Resources\ActivitylogResource;
use Illuminate\Auth\Access\AuthorizationException;

class ListActivitylog extends ListRecords
{
    protected static string $resource = ActivitylogResource::class;
    public function mount(): void
    {
        parent::mount();

        if (!auth()->user()?->can('view_any_activitylog')) {
            throw new AuthorizationException();
        }
    }
}
