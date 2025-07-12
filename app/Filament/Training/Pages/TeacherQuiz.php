<?php

namespace App\Filament\Training\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizScore;
use App\Models\CourseProgress;
use Illuminate\Http\Request as HttpRequest;

class TeacherQuiz extends Page
{
    protected static string $view = 'filament.pages.teacher-quiz';
    protected static ?string $title = '';
    protected static ?string $navigationIcon = '';

    public ?Material $material = null;
    public ?Course $course = null;
    public ?Question $question = null;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('page_TeacherQuiz');
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('page_TeacherQuiz');
    }

    public function mount(Material $material)
    {
        $this->material = $material;
        $this->course = $material->course;

        $quiz = Quiz::where('material_id', $material->id)->first();
        if (!$quiz) {
            return redirect()->route('filament.training.pages.teacher-material.{record}.{material?}', [
                'record' => $this->course->id,
                'material' => $material->id,
            ]);
        }

        $availableQuestionsCount = Question::where('material_id', $material->id)->count();
        if ($availableQuestionsCount === 0) {
            return redirect()->route('filament.training.pages.teacher-material.{record}.{material?}', [
                'record' => $this->course->id,
                'material' => $material->id,
            ]);
        }

        $maxQuestions = min($quiz->questions, $availableQuestionsCount);
        $shownKey = "shown_question_ids_{$material->id}";
        $shownQuestionIds = session()->get($shownKey, []);

        // ✅ Tambah: simpan start time jika belum ada
        $startKey = "quiz_start_time_{$material->id}";
        if (!session()->has($startKey)) {
            session()->put($startKey, now());
        }

        if (count($shownQuestionIds) >= $maxQuestions) {
            return redirect()->route('filament.training.pages.teacher-material.{record}.{material?}', [
                'record' => $this->course->id,
                'material' => $material->id,
            ])->with('success', 'Quiz completed.');
        }

        $question = Question::where('material_id', $material->id)
            ->whereNotIn('id', $shownQuestionIds)
            ->inRandomOrder()
            ->first();

        if (!$question) {
            return redirect()->route('filament.training.pages.teacher-material.{record}.{material?}', [
                'record' => $this->course->id,
                'material' => $material->id,
            ]);
        }

        $this->question = $question;
    }


    public function submit(HttpRequest $request, Material $material)
    {
        $user = Auth::user();
        $questionId = $request->input('question_id');
        $selectedAnswer = $request->input('selected_answer');

        $question = Question::find($questionId);
        $quiz = Quiz::where('material_id', $material->id)->firstOrFail();
        $course = $material->course;

        $scoreKey = "quiz_scores_{$material->id}";
        $shownKey = "shown_question_ids_{$material->id}";
        $startKey = "quiz_start_time_{$material->id}";

        $scores = session()->get($scoreKey, []);

        if (!$question) {
            return redirect()->route('filament.training.pages.teacher-quiz.{material}', ['material' => $material->id]);
        }

        $isCorrect = trim($selectedAnswer) === trim($question->answer);
        $score = $isCorrect ? 1 : 0;

        $scores[$question->id] = $score;
        session()->put($scoreKey, $scores);

        $shown = session()->get($shownKey, []);
        if (!in_array($question->id, $shown)) {
            $shown[] = $question->id;
            session()->put($shownKey, $shown);
        }

        $maxQuestions = min($quiz->questions, Question::where('material_id', $material->id)->count());

        if (count($shown) >= $maxQuestions) {
            $totalScore = array_sum($scores);
            $finalScore = $totalScore * (100 / $maxQuestions);
            $status = $finalScore >= $quiz->pass_score ? 1 : 0;

            // ✅ Hitung durasi
            $startTime = session()->get($startKey);

            if ($startTime) {
                $parsedStart = \Carbon\Carbon::parse($startTime);
                $durationSeconds = $parsedStart->diffInSeconds(now());
            } else {
                // Default kalau tidak ketemu, kasih 0 atau asumsi default durasi misalnya 0
                $durationSeconds = 0;
            }

            QuizScore::create([
                'user_id' => $user->id,
                'material_id' => $material->id,
                'course_id' => $course->id,
                'score' => round($finalScore),
                'status' => $status,
                'duration' => $durationSeconds, // ✅ simpan ke kolom duration
                'created_at' => now(),
                'created_by' => $user->id,
            ]);

            // Hapus session
            session()->forget($scoreKey);
            session()->forget($shownKey);
            session()->forget($startKey);

            if ($status === 1) {
                $this->updateCourseProgress($user->id, $course->id);
            }

            return redirect()->route('filament.training.pages.teacher-material.{record}.{material?}', [
                'record' => $course->id,
                'material' => $material->id,
            ])->with('success', 'Quiz completed.');
        }

        return redirect()->route('filament.training.pages.teacher-quiz.{material}', ['material' => $material->id]);
    }



    public function updateCourseProgress($userId, $courseId)
    {
        $courseProgress = \App\Models\CourseProgress::firstOrNew([
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);

        // Total materi yang publish
        $totalMaterials = Course::findOrFail($courseId)
            ->materials()
            ->where('publish', true)
            ->count();

        // Materi yang lulus
        $passedMaterials = QuizScore::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 1)
            ->distinct('material_id')
            ->count('material_id');

        // Persentase progress
        $progress = $totalMaterials === 0 ? 0 : ($passedMaterials / $totalMaterials) * 100;

        // Hitung duration (dari date_start hingga hari ini)
        $startDate = $courseProgress->date_start ?? now(); // kalau belum ada, gunakan hari ini
        $durationDays = \Carbon\Carbon::parse($startDate)->diffInDays(now());

        // Simpan atau update
        $courseProgress->progress = round($progress, 2);
        $courseProgress->duration = $durationDays;

        // Set tanggal mulai jika belum ada
        if (!$courseProgress->date_start) {
            $courseProgress->date_start = now();
        }

        $latestPassedMaterial = QuizScore::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 1)
            ->orderByDesc('created_at') // ambil yang terbaru
            ->first();

        if ($latestPassedMaterial) {
            $courseProgress->material_id = $latestPassedMaterial->material_id;
        }

        $courseProgress->save();
    }

    public static function getSlug(): string
    {
        return 'teacher-quiz/{material}';
    }

    public function getViewData(): array
    {
        $options = collect($this->question->other_option ?? [])
            ->push($this->question->answer)
            ->shuffle()
            ->values()
            ->all();

        return [
            'material' => $this->material,
            'question' => $this->question,
            'options' => $options,
        ];
    }
}