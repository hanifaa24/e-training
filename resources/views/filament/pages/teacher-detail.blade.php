<head>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
@php
    $firstMaterial = $course->materials->where('publish', true)->sortBy('order')->first();
    $firstMaterialId = $firstMaterial->id ?? null;

    $courseProgress = \App\Models\CourseProgress::with('material')
        ->where('user_id', auth()->id())
        ->where('course_id', $course->id) // jika sedang dalam view course
        ->first();

    

@endphp
<x-filament::page>
    <div class="p-6"> {{-- Tambahkan padding di sini --}}
        {{-- Card Course Detail --}}
        <div class="bg-white shadow border flex flex-col rounded-xl p-6 ">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="md:w-2/3 flex flex-col">
                    <h3 class="text-3xl font-bold text-gray-800 mb-4">
                        {{ $course->name }} {{-- Nama Course --}}
                    </h3>

                    {{-- Tombol Start Learning --}}
                    <div class="mb-12 mt-8">
                        {{-- Ini seharusnya link ke halaman materi pertama dari course ini --}}
                        {{-- Anda perlu menentukan halaman materi pertama. Contoh: --}}
                        @if ($firstMaterialId)
                            <button>
                                <a href="{{ route('filament.training.pages.teacher-material.{record}.{material?}', ['record' => $course->id, 'material' => $firstMaterialId]) }}"
                                    class="bg-lime-500 hover:bg-lime-600 active:bg-lime-700 text-white font-semibold py-1 px-3 rounded-lg flex items-center gap-2">
                                    Start Learning
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                                    </svg>
                                </a>
                            </button>
                        @else
                            <button disabled
                                class="bg-gray-400 text-white font-semibold py-1 px-3 rounded-lg flex items-center gap-2 cursor-not-allowed">
                                No Materials Yet
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    {{-- Detail Course --}}
                    <ul class="space-y-3 text-gray-700 text-sm flex-grow">
                        {{-- Subject --}}
                        <li class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5 h-5 text-blue-600"
                                viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M6 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3H6Zm1.5 1.5a.75.75 0 0 0-.75.75V16.5a.75.75 0 0 0 1.085.67L12 15.089l4.165 2.083a.75.75 0 0 0 1.085-.671V5.25a.75.75 0 0 0-.75-.75h-9Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-base text-blue-600">
                                Subjects > {{ $course->subject?->name ?? 'Unknown' }}
                            </span>
                        </li>

                        {{-- Jumlah Materials --}}
                        <li class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-5 h-5 text-blue-600"
                                viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M2.625 6.75a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Zm4.875 0A.75.75 0 0 1 8.25 6h12a.75.75 0 0 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM2.625 12a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0ZM7.5 12a.75.75 0 0 1 .75-.75h12a.75.75 0 0 1 0 1.5h-12A.75.75 0 0 1 7.5 12Zm-4.875 5.25a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Zm4.875 0a.75.75 0 0 1 .75-.75h12a.75.75 0 0 1 0 1.5h-12a.75.75 0 0 1-.75-.75Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-blue-600 text-base">
                                {{ $course->materials_count }} Materials
                            </span>
                        </li>

                        {{-- Latest Progress --}}
                        <li class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-5 h-5 text-blue-600">
                                <path fill-rule="evenodd"
                                    d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-blue-600 text-base">
                                Latest Progress : {{ $courseProgress?->material?->chapter_title ?? 'Not Started' }}
                            </span>
                        </li>

                        {{-- End Session --}}
                        <li class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="w-5 h-5 text-blue-600">
                                <path fill-rule="evenodd"
                                    d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Zm4.502 8.9a2.25 2.25 0 1 0 4.496 0 25.057 25.057 0 0 1-4.496 0Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-blue-600 text-base">
                                End Session :
                                {{ $course->end_date ? \Carbon\Carbon::parse($course->end_date)->format('d F Y') : 'N/A' }}
                                {{-- Format tanggal jika ada --}}
                            </span>
                        </li>
                    </ul>
                </div>

                {{-- Gambar Course --}}
                <div class="md:w-1/3 flex-shrink-0 flex items-start justify-end sm:justify-center">
                    <img src="{{ $course->image ? asset('storage/' . $course->image) : 'https://placehold.co/600x400?text=Course+Image' }}"
                        alt="Course Image" class="w-80 h-auto object-cover rounded-md" />
                </div>
            </div>
            <div>
                <h4 class="text-lg font-bold text-gray-800 mt-8 mb-2">About Course</h4>
                <p class="text-gray-600 text-base">
                    {{ $course->description ?? 'No description available.' }}
                </p>
            </div>
        </div>
    </div>
</x-filament::page>
<script>
    const chapterTitle = @json($course->latest_progress);
    console.log("Latest chapter title:", chapterTitle);
</script>