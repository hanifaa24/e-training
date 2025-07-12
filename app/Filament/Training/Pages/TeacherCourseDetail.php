<?php

namespace App\Filament\Training\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject; // Pastikan Subject di-import jika digunakan untuk relasi
use App\Models\Course;
// use App\Models\Material; // Material tidak lagi diperlukan untuk halaman detail course ini
use Illuminate\Support\Facades\Request;

class TeacherCourseDetail extends Page // Nama kelas sudah TeacherCourseDetail, bagus!
{
    // Properti statis ini mendefinisikan icon navigasi di sidebar Filament.
    // protected static ?string $navigationIcon = 'heroicon-o-book-open';

    // Properti ini mendefinisikan view Blade mana yang akan digunakan untuk halaman ini.
    // Pastikan file ini ada di resources/views/filament/pages/teacher-detail.blade.php
    protected static string $view = 'filament.pages.teacher-detail'; // Nama view sudah teacher-detail, bagus!

    protected static ?string $title = '';

    // Properti ini mendefinisikan grup navigasi di sidebar Filament.
    // Jika null, halaman ini tidak akan masuk dalam grup tertentu.
    protected static ?string $navigationGroup = null;

    /**
     * Menentukan apakah halaman ini harus didaftarkan di navigasi sidebar Filament.
     * Kita cek apakah pengguna memiliki izin 'page_TeacherCourseDetail'.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('page_TeacherCourseDetail');
    }

    /**
     * Menentukan apakah pengguna dapat melihat halaman ini sama sekali.
     * Juga menggunakan izin 'page_TeacherCourseDetail'.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('page_TeacherCourseDetail');
    }

    /**
     * Mendefinisikan slug (bagian dari URL) untuk halaman ini.
     * Sekarang hanya menerima {record} yaitu ID Course.
     * Contoh URL: /training/teacher-detail/12
     */
    public static function getSlug(): string
    {
        return 'teacher-detail/{record}'; // Hanya {record} karena ini halaman detail course
    }

    // Properti publik untuk menyimpan instance Course yang sedang dilihat.
    public Course $course;

    // Hapus properti ini karena halaman ini tidak lagi menampilkan materi individual
    // public Material $selectedMaterial;

    // Hapus properti ini karena daftar materi (materials) akan diakses melalui $this->course->materials
    // dan bukan sebagai properti terpisah untuk sidebar navigasi materi di halaman ini.
    // public $materials;

    /**
     * Metode mount() adalah lifecycle hook dari Livewire/Filament yang dipanggil
     * ketika komponen/halaman diinisialisasi. Di sinilah kita mengambil data dari database.
     */
    public function mount(): void
    {
        // 1. Ambil semua parameter yang ada di URL (dari route Laravel)
        // Ini akan mengembalikan array asosiatif, misal: ['record' => '12']
        $routeParameters = Request::route()->parameters();

        // 2. Ambil ID Course dari parameter route.
        $recordId = $routeParameters['record'] ?? null;

        // 3. Lakukan validasi dasar: jika $recordId tidak ada, berarti ada yang salah dengan URL.
        if (is_null($recordId)) {
            abort(404, 'ID Course Not Found.');
        }

        // 4. Ambil data Course dari database.
        //    Kita tambahkan with('subject') untuk memuat relasi subject.
        //    Kita juga tambahkan withCount('materials') untuk mendapatkan jumlah materi.
        $this->course = Course::with('subject') // Muat relasi subject
            ->withCount([
                'materials' => function ($query) {
                    $query->where('publish', true);
                }
            ])
            ->findOrFail($recordId); // findOrFail akan otomatis menampilkan 404 jika course tidak ditemukan

        // Set judul halaman secara dinamis berdasarkan nama course
        $title = $this->course->name;

        // Catatan: Properti $selectedMaterial dan $materials tidak lagi diisi di sini
        // karena halaman ini fokus pada detail Course, bukan navigasi materi individual.
    }

    // public function getLatestProgressAttribute()
    // {
    //     $userId = auth()->id();

    //     $courseProgress = \App\Models\CourseProgress::with('material')
    //         ->where('user_id', $userId)
    //         ->where('course_id', $this->id)
    //         ->first();

    //     return $courseProgress?->material?->chapter_title ?? null;
    // }
}