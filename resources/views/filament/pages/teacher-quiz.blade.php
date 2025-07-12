<script src="https://cdn.tailwindcss.com"></script>

<x-filament::page>
    <div class="flex items-start justify-center min-h-screen bg-gray-50 py-4">
        <div class="bg-white shadow-md rounded-lg p-10 w-full max-w-4xl border border-gray-200">
            <div class="mb-8 flex justify-between items-start" x-data="{
                    time: {{ $question->duration }},
                    disabled: false,
                    selected: '',
                    startCountdown() {
                        let interval = setInterval(() => {
                            if (this.time > 0) {
                                this.time--;
                            } else {
                                clearInterval(interval);
                                this.disabled = true;
                                document.getElementById('auto-submit-form')?.submit();
                            }
                        }, 1000);
                    },
                    choose(answer) {
    if (this.disabled) return;
    this.disabled = true;
    this.selected = answer;
    this.$refs.answer.value = answer; // Set langsung ke input hidden
    document.getElementById('auto-submit-form')?.submit();
}
                }" x-init="startCountdown">

                <!-- Kiri: Emoji + Judul -->
                <div class="flex flex-col items-start space-y-2">
                    <p class="text-8xl mb-4">📝</p>
                    <h2 class="font-semibold text-gray-800 text-lg">Chapter {{ $material->order }}.
                        {{ $material->chapter_title }}
                    </h2>
                    <h1 class="text-2xl font-bold">Quiz</h1>
                </div>

                <!-- Kanan: Remaining Time -->
                <div class="text-3xl text-gray-600">
                    Remaining Time: <span class="text-blue-600" x-text="time + ' sec'"></span>
                </div>
            </div>

            <!-- Soal -->
            @if ($question)
                <p class="text-gray-700 leading-relaxed mb-8 text-lg">
                    {{ $question->question }}
                </p>

                @if ($question->image)
                    <div class="flex-shrink-0 flex items-center justify-center mb-7">
                        <img src="{{ asset('storage/' . $question->image) }}" alt="Question Image"
                            class="w-80 h-auto object-cover rounded-md" />
                    </div>
                @endif

                <!-- Jawaban -->
                <form id="auto-submit-form" method="POST"
                    action="{{ route('filament.training.pages.teacher-quiz.submit', ['material' => $material->id]) }}">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <input type="hidden" name="selected_answer" x-ref="answer">

                    <div class="flex flex-wrap gap-4">
                        @foreach($options as $option)
                            <button type="submit" name="selected_answer"
                                value="{{ is_array($option) ? ($option['value'] ?? '') : $option }}" class="flex-1 min-w-[40%] py-4 bg-blue-200 text-lg font-medium rounded-lg hover:bg-blue-300 transition active:bg-blue-400 disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ is_array($option) ? ($option['value'] ?? '') : $option }}
                            </button>
                        @endforeach
                    </div>
                </form>
            @else
                <div class="text-red-500 font-semibold text-lg">
                    No question found for this material.
                </div>
            @endif
        </div>
    </div>
</x-filament::page>