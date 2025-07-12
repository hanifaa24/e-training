<script src="https://cdn.tailwindcss.com"></script>
<x-filament::page>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Courses</h2>
        <div class="flex items-center gap-2">

            <form method="GET" class="flex items-center gap-2">
                <div class="relative w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search"
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring focus:ring-indigo-200 shadow-sm transition duration-200 ease-in-out" />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <!-- Search icon -->
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1116.65 6.65a7.5 7.5 0 010 10.6z" />
                        </svg>
                    </span>
                </div>

                <!-- Filter dropdown -->
                <div x-data="{ showFilter: false }" class="relative">
                    <button type="button" @click="showFilter = !showFilter" class="p-2 rounded hover:bg-gray-100">
                        <!-- Filter icon -->
                         <img src="{{ asset('images/filter.png') }}"
                    alt="Filter Image" class="w-7 h-7 text-gray-600 hover:fill-gray-800" />
                    </button>
                   

                    <!-- Dropdown muncul saat tombol diklik -->
                    <div x-show="showFilter" @click.away="showFilter = false"
                        class="absolute right-0 mt-2 z-10 bg-white border rounded-xl shadow-lg w-48">
                        <form method="GET" class="flex flex-col">
                            <select name="filter" onchange="this.form.submit()"
                                class="py-2 px-3 border-b w-full text-left  cursor-pointer focus:outline-none rounded-xl">
                                <option value="">All</option>
                                <option value="completed" {{ request('filter') === 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                                <option value="in_progress" {{ request('filter') === 'in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                            </select>
                        </form>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @foreach ($courses as $course)
            <a href="{{ route('filament.training.pages.teacher-detail.{record}', ['record' => $course->id]) }}"
                class="group bg-white rounded-xl hover:bg-indigo-200 active:bg-blue-500 shadow border overflow-hidden hover:scale-105">
                <img src="{{ $course->image ? asset('storage/' . $course->image) : 'https://placehold.co/600x300?text=Course+Image' }}"
                    alt="Course Image" class="w-full h-40 object-cover rounded-md mb-2" />
                <div class="p-4">
                    <h3 class="text-lg font-semibold pb-2 group-active:text-white">
                        {{ $course->name }}
                    </h3>

                    <p class="text-md text-gray-500 flex items-center gap-1 mt-2 group-active:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            class="w-5 h-5 mr-1 fill-blue-500 group-active:fill-white" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M6 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3H6Zm1.5 1.5a.75.75 0 0 0-.75.75V16.5a.75.75 0 0 0 1.085.67L12 15.089l4.165 2.083a.75.75 0 0 0 1.085-.671V5.25a.75.75 0 0 0-.75-.75h-9Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-blue-500 group-active:text-white">
                            Subjects &gt; {{ $course->subject?->name ?? 'Unknown' }}
                        </span>
                    </p>

                    <p class="text-md text-gray-500 flex items-center gap-1 mt-1 group-active:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            class="w-5 h-5 mr-1 fill-blue-500 group-active:fill-white" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M2.625 6.75a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Zm4.875 0A.75.75 0 0 1 8.25 6h12a.75.75 0 0 1 0 1.5h-12a.75.75 0 0 1-.75-.75ZM2.625 12a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0ZM7.5 12a.75.75 0 0 1 .75-.75h12a.75.75 0 0 1 0 1.5h-12A.75.75 0 0 1 7.5 12Zm-4.875 5.25a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Zm4.875 0a.75.75 0 0 1 .75-.75h12a.75.75 0 0 1 0 1.5h-12a.75.75 0 0 1-.75-.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-blue-500 group-active:text-white">
                            {{ $course->materials_count }} Materials
                        </span>
                    </p>
                </div>
            </a>
        @endforeach
    </div>
</x-filament::page>