<x-filament::page>
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        $user = auth()->user();
        ;
        $materialId = $selectedMaterial->id ?? null;

        $hasAttemptedQuiz = App\Models\QuizScore::where('user_id', $user->id)
            ->where('material_id', $materialId)
            ->exists();

        $latestScore = $hasAttemptedQuiz
           ? App\Models\QuizScore::where('user_id', $user->id)
                ->where('material_id', $materialId)
                ->latest()
                ->first()
            : null;

        $hasPassedQuiz = $latestScore?->status == 1;

        $canAccessMaterial = true;

        if (isset($selectedMaterial) && $selectedMaterial->order > 1) {
            $previousMaterial = $materials->firstWhere('order', $selectedMaterial->order - 1);

            if ($previousMaterial) {
                $canAccessMaterial = \App\Models\QuizScore::where('user_id', $user->id)
                    ->where('material_id', $previousMaterial->id)
                    ->where('status', 1) // Passed
                    ->exists();
            } else {
                $canAccessMaterial = false;
            }
        }
    @endphp

    <div class="flex flex-col lg:flex-row items-start min-h-screen p-1 gap-4">

        {{-- Sidebar Kiri: Daftar Materi --}}
        <div class="w-full lg:w-1/4 bg-white rounded-xl shadow border p-4 flex flex-col overflow-y-auto flex-shrink-0">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                {{ $course->name }}
            </h3>
            <ul class="space-y-2">
                @forelse ($materials as $material)
                    <li
                        class="relative 
                                                                                                                @if (isset($selectedMaterial) && $selectedMaterial->id === $material->id)
                                                                                                                    bg-blue-600 text-white shadow
                                                                                                                @else
                                                                                                                    bg-gray-50 hover:bg-gray-100 text-blue-600
                                                                                                                @endif
                                                                                                                rounded-lg">
                        <a href="#" wire:click.prevent="selectMaterial({{ $material->id }})"
                            class="group flex items-center gap-3 p-3 transition duration-150 ease-in-out">
                            <x-heroicon-o-list-bullet class="w-5 h-5" />
                            <span class="font-medium text-sm">
                                Chapter {{ $material->order ?? '' }}. {{ $material->chapter_title }}
                            </span>
                        </a>
                    </li>
                @empty
                    <p class="text-gray-500">No materials published for this course yet.</p>
                @endforelse
            </ul>
        </div>

        {{-- Konten Utama Kanan: Detail Materi yang Dipilih --}}
        <div class="w-full lg:w-3/4 bg-white rounded-xl shadow border p-6 flex flex-col lg:ml-6 max-h-[90vh] overflow-y-auto">

            <div class="flex-grow overflow-y-auto">
                <div class="flex items-center gap-4 mb-6">
                    <p class="text-8xl">📖</p>
                    <h2 class="text-2xl font-bold text-gray-800">
                        @if (isset($selectedMaterial))
                            {{ $selectedMaterial->title }}
                        @else
                            No Material Selected
                        @endif
                    </h2>
                </div>

                <div class="prose prose-indigo max-w-none mb-8 text-gray-700 leading-relaxed">
                    @if (isset($selectedMaterial))
                        <h3 class="text-2xl font-bold text-gray-800 mb-8 mt-8">
                            Chapter {{ $selectedMaterial->order ?? '' }}. {{ $selectedMaterial->chapter_title ?? '' }}
                        </h3>

                        @if ($canAccessMaterial)
                            {!! $selectedMaterial->content ?? 'No content available for this material.' !!}
                        @else
                            <div class="flex flex-col items-center justify-center mt-12 text-center text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                <p class="text-lg font-semibold">Pass previous chapter quiz</p>
                                <p class="text-sm">You need to complete and pass the quiz in the previous chapter to unlock this
                                    content.</p>
                            </div>
                        @endif
                    @else
                        <p>Please select a material from the left sidebar.</p>
                    @endif
                </div>

                {{-- Bagian Quiz --}}
                @if (isset($selectedMaterial))
                    @if ($canAccessMaterial)
                        <div class="flex justify-center mt-8">
                            <div class="w-1/2 justify-items-end">
                                <img src="{{ asset('images/quiz.png') }}" class="w-64">
                            </div>
                            <div class="w-1/2 flex flex-col justify-items-start text-base font-bold text-gray-800">
                                <div>
                                    <div class="ml-4">
                                        Quiz Chapter {{ $selectedMaterial->order ?? '' }}.
                                        {{ $selectedMaterial->chapter_title ?? '' }}
                                    </div>
                                </div>
                                <div class="flex flex-row ml-4">
                                    <div>
                                        <button class="mt-5">
                                            {{-- Sesuaikan ROUTE KE HALAMAN QUIZ Anda --}}
                                            <a href="{{ url('/training/teacher-quiz/' . $selectedMaterial->id) }}"
                                                class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white text-base font-semibold py-1 px-3 rounded-lg flex items-center gap-2">
                                                Start Quiz
                                            </a>
                                        </button>
                                    </div>
                                    <div class="flex flex-col items-stretch ml-5">
                                        {{-- Menampilkan Latest Score --}}
                                        @if ($latestScore)
                                            <div class="
                                                                                        @if($hasPassedQuiz)
                                                                                            text-green-500
                                                                                        @else
                                                                                            text-red-500
                                                                                        @endif
                                                                                        text-lg font-normal self-center mt-2">
                                                Latest Score: <span class="font-bold text-xl">{{ $latestScore->score }}</span>
                                            </div>

                                            <div class="@if($hasPassedQuiz)
                                                 text-green-500 
                                            @else
                                                             text-red-500 
                                                        @endif 
                                                                                    text-base font-bold">
                                                {{ $hasPassedQuiz ? 'PASSED' : 'FAILED' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    @else

                    @endif

                @else
                    <p>.</p>
                @endif
            </div>
        </div>
</x-filament::page>