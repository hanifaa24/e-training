<?php

namespace App\Filament\Training\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Material;
use App\Models\QuizScore;
use App\Models\Quiz;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeacherMaterial extends Page
{
    protected static string $view = 'filament.pages.teacher-material';
    protected static ?string $title = ''; // Tidak ada judul di header Filament
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    public static function getSlug(): string
    {
        return 'teacher-material/{record}/{material?}';
    }

    public Course $course;
    public ?Material $selectedMaterial = null; // Inisialisasi dengan null untuk keamanan
    public $materials; // Koleksi semua materi untuk sidebar

    public ?QuizScore $latestScore = null; // Gunakan float karena kolom score adalah decimal
    public ?bool $hasPassedQuiz = null;
    public ?int $quizPassScore = null;

    public function mount(): void
    {
        try {
            $routeParameters = Request::route()->parameters();
            $recordId = $routeParameters['record'] ?? null;
            $materialId = $routeParameters['material'] ?? null;

            if (is_null($recordId)) {
                abort(404, 'Course ID not found in URL.');
            }

            // Muat course dan materi-materinya yang dipublikasi, diurutkan
            $this->course = Course::with([
                'materials' => function ($query) {
                    $query->where('publish', true)->orderBy('order'); // Asumsi ada kolom 'order'
                }
            ])->findOrFail($recordId);

            // Simpan daftar materi yang dipublikasi dari course ini
            $this->materials = $this->course->materials;

            // Tentukan materi mana yang akan ditampilkan sebagai 'selectedMaterial'.
            if ($materialId) {
                $this->selectedMaterial = Material::where('id', $materialId)
                    ->where('course_id', $this->course->id)
                    ->where('publish', true)
                    ->first(); // Gunakan first() agar tidak 404 jika materialId tidak valid
            }

            // Jika $selectedMaterial masih null (baik karena materialId tidak ada/tidak valid,
            // atau tidak ada materi sama sekali), ambil materi pertama
            if (is_null($this->selectedMaterial)) {
                $firstPublishedMaterial = $this->course->materials->first();

                if ($firstPublishedMaterial) {
                    $this->selectedMaterial = $firstPublishedMaterial;
                } else {
                    // Jika tidak ada materi yang dipublikasi sama sekali
                    $this->selectedMaterial = new Material([
                        'title' => 'No Materials Available',
                        'content' => 'This course currently has no published materials.',
                        'image' => null,
                        'id' => 0 // Dummy ID
                    ]);
                }
            }

            $this->loadQuizData();

        } catch (ModelNotFoundException $e) {
            abort(404, 'Course or Material not found: ' . $e->getMessage());
        } catch (\Exception $e) {
            abort(500, 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    // Metode untuk mengubah materi yang dipilih ketika item di sidebar diklik
    public function selectMaterial(Material $material)
    {
        // Pastikan material yang dipilih adalah bagian dari course yang sedang dibuka
        if ($material->course_id !== $this->course->id || !$material->publish) {
            abort(403, 'Unauthorized material access or material not published.');
        }

        $this->selectedMaterial = $material;

        // Perbarui URL agar mencerminkan materi yang dipilih (untuk bookmarking/refresh)
        $this->redirect(route('filament.training.pages.teacher-material.{record}.{material?}', [
            'record' => $this->course->id,
            'material' => $material->id,
        ]), navigate: true);
    }
    protected function loadQuizData() // <--- Ubah nama metode menjadi lebih umum
    {
        // Reset nilai sebelum memuat yang baru
        $this->latestScore = null;
        $this->hasPassedQuiz = null;
        $this->quizPassScore = null; // Reset pass_score juga

        if ($this->selectedMaterial) {
            // 1. Ambil pass_score dari tabel 'quizzes'
            $quiz = Quiz::where('material_id', $this->selectedMaterial->id)
                ->first(); // Asumsi 1 material = 1 quiz

            if ($quiz) {
                $this->quizPassScore = $quiz->pass_score;
            }

            // 2. Ambil skor terbaru user
            $scoreRecord = QuizScore::where('user_id', auth()->id())
                ->where('material_id', $this->selectedMaterial->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($scoreRecord) {
                $this->latestScore = $scoreRecord;

                // Tentukan status kelulusan berdasarkan pass_score yang diambil dari tabel 'quizzes'
                if ($this->quizPassScore !== null) {
                    $this->hasPassedQuiz = $scoreRecord->score >= $this->quizPassScore;
                } else {
                    // Jika pass_score tidak ditemukan, anggap saja belum lulus atau kondisi tidak jelas
                    $this->hasPassedQuiz = false; // Atau biarkan null
                }
            }
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Contoh, hanya register jika user memiliki permission
        return Auth::user()?->can('page_TeacherMaterial');
    }

    public static function canViewAny(): bool
    {
        // Contoh, hanya dapat melihat jika user memiliki permission
        return Auth::user()?->can('page_TeacherMaterial');
    }
}