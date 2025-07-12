<?php

namespace App\Filament\Resources\DashboardResource\Pages;

use App\Filament\Resources\DashboardResource;
use Filament\Resources\Pages\Page;

class Dashboard extends Page
{

    protected static string $resource = DashboardResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.training.pages.test';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = -1;

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return true;
    }
}