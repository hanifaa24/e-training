<?php

namespace App\Filament\Training\Pages;

use Filament\Pages\Page;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static string $resource = DashboardResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.training.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = -1;

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return true;
    }

    public function getViewData(): array
    {
        $employee = Auth::user()?->employee;

        $upcomingTraining = Training::when($employee, function ($query) use ($employee) {
            $query->where('employee_id', $employee->id);
        })
            // Gabungkan kolom `date` dan `time` lalu bandingkan dengan waktu sekarang
            ->whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') > ?", [now()])
            ->orderByRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s')")
            ->first();

        return [
            'upcomingTraining' => $upcomingTraining,
        ];
    }

}
