<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Training\Pages\Dashboard;
use Illuminate\Support\Facades\Auth;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Filament\Navigation\NavigationItem;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Enums\ThemeMode;
use App\Filament\Training\Pages\TeacherCourse;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Blade;

class TrainingPanelProvider extends PanelProvider
{
    public function boot()
    {
        Filament::serving(function () {
            Filament::registerRenderHook(
                'panels::body.end',
                function (): string {
                    if (request()->routeIs('filament.training.auth.login')) {
                        return '<div class="text-center text-white text-sm p-2 bg-indigo-800">© 2025 Stasiun Belajar</div>';
                    }

                    // Halaman lain
                    return '<div class="text-center text-gray-500 text-sm p-3">© 2025 Stasiun Belajar</div>';
                }
            );
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('training')
            ->path('training')
            ->login()
            ->brandName('E-Training Stasiun Belajar')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('40px')
            ->favicon(asset('images/logo_tab.png'))
            ->colors([
                'primary' => '#312e81',
            ])
            ->defaultThemeMode(ThemeMode::Light)
            ->darkMode(false)
            ->sidebarFullyCollapsibleOnDesktop()
            ->profile()
            // ->navigationItems([
            //     NavigationItem::make('Courses')
            //         ->icon('heroicon-o-book-open')
            //         ->url(fn () => TeacherCourse::getUrl())
            //         ->isActiveWhen(
            //             fn() =>
            //             request()->routeIs('filament.training.pages.teacher-course') ||
            //             request()->Is('filament.training.pages.teacher-detail.*') 
            //         ),
            // ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                ActivitylogPlugin::make()
                    ->navigationSort(14)
                    ->navigationIcon('heroicon-o-squares-2x2')
            ])
            ->pages([
                \App\Filament\Training\Pages\TeacherCourse::class,
                \App\Filament\Training\Pages\TeacherCourseDetail::class,
                \App\Filament\Training\Pages\TeacherMaterial::class,
                \App\Filament\Training\Pages\TeacherQuiz::class,
            ]);
    }


}