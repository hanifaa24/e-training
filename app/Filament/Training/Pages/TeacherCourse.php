<?php

namespace App\Filament\Training\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class TeacherCourse extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.teacher-course';

    protected static string $resource = TeacherCourseResource::class;

    protected static ?string $navigationLabel = 'Courses';
    protected static ?string $title = '';

    protected static ?string $navigationGroup = null;

    public static function getNavigationItemActive(): bool
{
    $routeName = request()->route()?->getName();

    return in_array($routeName, [
        'filament.training.pages.teacher-course',
        'filament.training.pages.teacher-detail.{record}',
        'filament.training.pages.teacher-material.{record}.{material?}',
        'filament.training.pages.teacher-quiz.{material}',
    ]);
}

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('page_TeacherCourse');
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('page_TeacherCourse');
    }
    // public function mount(): void
    // {
    //     // Bisa juga pakai Course::withCount('materials')->get() jika ingin efisien
    //     $this->courses = Course::with('subject')
    //         ->withCount([
    //             'materials' => function ($query) {
    //                 $query->where('publish', true);
    //             }
    //         ])
    //         ->where('publish', true)->get();
    // }

    public function mount(): void
    {
        $search = request('search');
        $filter = request('filter');
        $userId = auth()->id();
        // Tes apakah search berhasil dikirim
        // dd($search);

        $this->courses = Course::query()
            ->with('subject')
            ->withCount([
                'materials' => fn($q) => $q->where('publish', true)
            ])
            ->where('publish', true)
            ->when($search, function ($query, $search) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($search) . '%'])
                    ->orWhereHas('subject', function ($q) use ($search) {
                        $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                    });
            })
            ->when($filter === 'completed', function ($query) use ($userId) {
                $query->whereHas('courseProgress', fn($q) => $q->where('user_id', $userId)->where('progress', 100));
            })
            ->when($filter === 'in_progress', function ($query) use ($userId) {
                $query->whereHas('courseProgress', fn($q) => $q->where('user_id', $userId)->where('progress', '<', 100));
            })
            ->get();
    }

    public static function getSlug(): string
    {
        return 'teacher-course';
    }
    protected function getViewData(): array
    {
        return [
            'courses' => $this->courses,
        ];
    }
}
