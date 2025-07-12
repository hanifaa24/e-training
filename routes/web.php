<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Training\Pages\TeacherQuiz;

Route::get('/', function () {return view('welcome');});
Route::post('/teacher-quiz/{material}/submit', [TeacherQuiz::class, 'submit'])->name('filament.training.pages.teacher-quiz.submit');



// Route::get('/training/login', function () {
//     return view('login');
// });

