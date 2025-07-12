<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<x-filament::page>
    @php
        $user = auth()->user();
        $employee = \App\Models\Employee::find($user->employee_id);

        $topCourse = null;

        $employee = Auth::user()?->employee;

        $upcomingTraining = null;

        $now = now();
        $start = $now;
        $end = now()->addWeek();

        $upcomingTraining = \App\Models\Training::whereJsonContains('employee_id', (string) $employee->id)
            ->whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') >= ?", [$start])
            ->whereRaw("STR_TO_DATE(CONCAT(`date`, ' ', `time`), '%Y-%m-%d %H:%i:%s') <= ?", [$end])
            ->orderBy('date')
            ->orderBy('time')
            ->first();

        $topCourse = null;

        if ($employee && $upcomingTraining?->course_id) {
            $nearestTrainingCourseId = $upcomingTraining->course_id;

            $topCourse = \App\Models\CourseProgress::with(['course', 'material'])
                ->where('user_id', $employee->id)
                ->where('course_id', $nearestTrainingCourseId)
                ->whereHas('course', fn($q) => $q->where('publish', 1))
                ->first();

            // Jika belum ada progress, buat object dummy dengan progress 0
            if (!$topCourse) {
                $dummyCourse = \App\Models\Course::where('id', $nearestTrainingCourseId)
                    ->where('publish', 1)
                    ->first();

                if ($dummyCourse) {
                    $topCourse = new \App\Models\CourseProgress([
                        'progress' => 0,
                        'material' => null,
                    ]);
                    $topCourse->course = $dummyCourse;
                }
            }
        }

        $totalFinishedCourses = \App\Models\CourseProgress::where('user_id', $user->id)
            ->where('progress', 100)
            ->count();

        $passedQuizCount = \App\Models\QuizScore::where('user_id', $user->id)
            ->where('status', 1)
            ->distinct('material_id')
            ->count('material_id');

    @endphp

    @php
        $roleName = auth()->user()?->roles->first()?->name ?? 'User';
        $displayRole = Str::of($roleName)->replace('_', ' ')->title();
    @endphp

    @if ($roleName === 'teacher')
        {{-- Halaman dashboard khusus teacher --}}
        <div class="space-y-6">
            {{-- Welcome Section --}}
            <div class="bg-white shadow rounded-xl p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold">Welcome to Stasiun Belajar E-Training System, Teacher</h2>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ now()->format('l, d M Y') }}
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <div class="flex items-center gap-3 p-1 rounded-lg w-full">
                        <div class="bg-red-400 text-white p-2 rounded">
                            <x-heroicon-o-book-open class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold">{{ $totalFinishedCourses }}</div>
                            <div class="text-sm text-gray-900">Total Finished Courses</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 rounded-lg w-full">
                        <div class="bg-yellow-400 text-white p-2 rounded">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold">{{ $passedQuizCount }}</div>
                            <div class="text-sm text-gray-900">Total Passed Quizzes</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Course Progress --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Course Card --}}
                <div class="bg-white shadow rounded-xl p-4 flex flex-col font-semibold content-between">
                    Course with the Closest Training Date
                    @if ($topCourse && $topCourse->course)
                        <div class="bg-white rounded-xl flex items-center mt-4 gap-4">
                            <div class="bg-pink-500 text-white p-3 rounded-xl">
                                <x-heroicon-o-book-open class="w-6 h-6" />
                            </div>
                            <div class="flex-1 pl-3">
                                <div class="font-semibold text-gray-900">{{ $topCourse->course->name }}</div>
                                <div class="text-sm text-gray-500">
                                    @if ($topCourse->material)
                                        Chapter {{ $topCourse->material->order }}.
                                        {{ $topCourse->material->chapter_title ?? 'No Chapter Info' }}
                                    @else

                                    @endif
                                </div>

                                <div class="mt-2 w-full flex items-center gap-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2 relative">
                                        <div class="h-2 rounded-full
                                                            @if($topCourse->progress == 0)
                                                                bg-gray-500
                                                            @elseif($topCourse->progress < 40)
                                                                bg-red-500
                                                            @elseif($topCourse->progress < 70)
                                                                bg-amber-500
                                                            @else
                                                                bg-green-500
                                                            @endif
                                                        " style="width: {{ $topCourse->progress }}%"></div>
                                    </div>
                                    <div class="text-sm font-medium text-gray-700 min-w-[50px] text-right">
                                        {{ $topCourse->progress }}%
                                        @if ($topCourse->progress == 100)
                                            <!-- <span class="ml-1 text-green-600 font-semibold text-xs">(Done)</span> -->
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="flex justify-end items-end mt-3">
                            @if ($topCourse->progress < 100)
                                <a href="{{ \App\Filament\Training\Pages\TeacherCourseDetail::getUrl(['record' => $topCourse->course->id]) }}"
                                    class="ml-2 mt-3 bg-pink-500 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-pink-700 transition">
                                    Continue →
                                </a>
                            @else
                                <a href="/training/teacher-course"
                                    class="ml-2 bg-pink-500 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-pink-700 transition">
                                    Courses →
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-xl p-4 flex items-center gap-4">
                            <div class="bg-pink-500 text-white p-3 rounded-xl">
                                <x-heroicon-o-book-open class="w-6 h-6" />
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">No course with upcoming training today</div>
                                <div class="text-sm text-gray-900">Let’s continue learning and exploring courses!</div>
                            </div>
                        </div>
                        <div class="flex justify-end items-end">
                            <a href="/training/teacher-course"
                                class="ml-2 bg-pink-500 text-white text-sm px-4 py-1.5 rounded-lg hover:bg-pink-700 transition">
                                Courses →
                            </a>
                        </div>
                    @endif

                </div>

                {{-- Zoom Meeting Card --}}
                <div class="bg-white shadow rounded-xl p-4 flex flex-col font-semibold content-between">
                    Nearest Training Schedule
                    <div class="flex items-center mt-4 gap-4">
                        <div class="bg-sky-400 text-white p-3 mr-3 rounded-xl">
                            <x-heroicon-o-video-camera class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            @if ($upcomingTraining)
                                <div class="font-semibold text-gray-800">{{ $upcomingTraining->title }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $upcomingTraining->course->name }} <br>
                                    {{ \Carbon\Carbon::parse($upcomingTraining->date)->format('d F Y') }}<br>
                                    {{ \Carbon\Carbon::parse($upcomingTraining->time)->format('H:i') }}
                                </div>
                            @else
                                <div class="font-semibold text-gray-800">No upcoming training</div>
                                <div class="text-sm text-gray-500">You have no scheduled training sessions yet.</div>
                            @endif
                        </div>
                        <div class="text-white p-3 rounded-xl">
                            <x-heroicon-o-video-camera class="w-6 h-6" />
                        </div>
                    </div>
                    @if ($upcomingTraining)
                        <div class="flex justify-end items-end">
                            <a href="{{ $upcomingTraining->link }}" target="_blank" rel="noopener noreferrer"
                                class="ml-2 bg-sky-400 hover:bg-sky-600 text-white text-sm px-4 py-1.5 rounded-lg">Link
                                →</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- Halaman dashboard untuk role lain --}}
        <div class="rounded-xl shadow p-6 bg-white space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">
                    Welcome to Stasiun Belajar E-Training System, {{ $displayRole }}
                </h2>
                <div class="text-sm text-gray-500">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->isoFormat('dddd, D MMM YYYY') }}
                </div>
            </div>

            @php
                $user = auth()->user();
                $role = $user->roles->first()->name ?? 'user';

                $totalRoles = \Spatie\Permission\Models\Role::count();
                $totalUsers = \App\Models\User::count();
                $totalEmployees = \App\Models\Employee::count();
                $totalCourses = \App\Models\Course::count();
                $totalQuizzes = \App\Models\Quiz::count();
                $totalQuestions = \App\Models\Question::count();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                @if ($role === 'super_admin')
                    <div class="flex items-center gap-3 p-1 rounded-lg w-full">
                        <div class="bg-red-400 text-white p-2 rounded">
                            <x-heroicon-o-shield-check class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $totalRoles }}</div>
                            <div class="text-sm text-gray-900">Total Roles</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 rounded-lg w-full">
                        <div class="bg-yellow-400 text-white p-2 rounded">
                            <x-heroicon-o-academic-cap class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $totalEmployees }}</div>
                            <div class="text-sm text-gray-900">Total Employees</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-1 rounded-lg w-full">
                        <div class="bg-lime-300 text-white p-2 rounded">
                            <x-heroicon-s-user class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $totalUsers }}</div>
                            <div class="text-sm text-gray-900">Total Users</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-1 rounded-lg w-full">
                        <div class="bg-sky-400 text-white p-2 rounded">
                            <x-heroicon-o-book-open class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $totalCourses }}</div>
                            <div class="text-sm text-gray-900">Total Courses</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 rounded-lg w-full">
                        <div class="bg-pink-500 text-white p-2 rounded">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900"> {{ $totalQuestions }}</div>
                            <div class="text-sm text-gray-900">Total Questions</div>
                        </div>
                    </div>
                @elseif ($role === 'curriculum_admin')
                    <div class="flex items-center gap-3 p-2 rounded-lg w-full">
                        <div class="bg-red-400 text-white p-2 rounded">
                            <x-heroicon-o-academic-cap class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $totalEmployees }}</div>
                            <div class="text-sm text-gray-900">Total Employees</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 rounded-lg w-full">
                        <div class="bg-yellow-400 text-white p-2 rounded">
                            <x-heroicon-s-user class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $totalUsers }}</div>
                            <div class="text-sm text-gray-900">Total Users</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-1 rounded-lg w-full">
                        <div class="bg-lime-300 text-white p-2 rounded">
                            <x-heroicon-o-book-open class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $totalCourses }}</div>
                            <div class="text-sm text-gray-900">Total Courses</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-2 rounded-lg w-full">
                        <div class="bg-sky-400 text-white p-2 rounded">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900"> {{ $totalQuestions }}</div>
                            <div class="text-sm text-gray-900">Total Questions</div>
                        </div>
                    </div>
                    <!-- <x-stat-box title="Total Employees" :value="$totalEmployees" color="bg-green-100" />
                                                                                                                                                                                                                                            <x-stat-box title="Total Users" :value="$totalUsers" color="bg-yellow-100" />
                                                                                                                                                                                                                                            <x-stat-box title="Total Courses" :value="$totalCourses" color="bg-indigo-100" />
                                                                                                                                                                                                                                            <x-stat-box title="Total Quizzes" :value="$totalQuizzes" color="bg-pink-100" /> -->
                @endif
            </div>
        </div>
    @endif
</x-filament::page>