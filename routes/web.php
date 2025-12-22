<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamGradingController;
use App\Http\Controllers\StudentExamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::patch('/{user}', [UserController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
});

Route::prefix('students')->name('students.')->group(function () {
    Route::get('/', [StudentController::class, 'index'])->name('index');
    Route::post('/', [StudentController::class, 'store'])->name('store');
    Route::patch('/{student}', [StudentController::class, 'update'])->name('update');
    Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
});

Route::prefix('questions')->name('questions.')->group(function () {
    Route::get('/', [QuestionController::class, 'index'])->name('index');
    Route::post('/', [QuestionController::class, 'store'])->name('store');
    Route::patch('/{question}', [QuestionController::class, 'update'])->name('update');
    Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');
});

Route::prefix('exams')->name('exams.')->group(function () {
    Route::get('/', [ExamController::class, 'index'])->name('index');
    Route::post('/', [ExamController::class, 'store'])->name('store');
    Route::patch('/{exam}', [ExamController::class, 'update'])->name('update');
    Route::delete('/{exam}', [ExamController::class, 'destroy'])->name('destroy');

    Route::get('/join', [StudentExamController::class, 'showAccessForm'])->name('join.form');
    Route::post('/join', [StudentExamController::class, 'start'])->name('join');

    Route::get('/my/attempts', [StudentExamController::class, 'myAttempts'])->name('attempts.mine');
    Route::get('/attempts/{attempt}', [StudentExamController::class, 'showAttempt'])->name('attempt.show');
    Route::post('/attempts/{attempt}/submit', [StudentExamController::class, 'submitAttempt'])->name('attempt.submit');

    Route::get('/grading', [ExamGradingController::class, 'index'])->name('grade.index');
    Route::get('/attempts/{attempt}/grade', [ExamGradingController::class, 'show'])->name('grade.show');
    Route::post('/attempts/{attempt}/grade', [ExamGradingController::class, 'update'])->name('grade.update');
});

require __DIR__.'/auth.php';
