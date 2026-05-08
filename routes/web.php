<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route('login');
});

// Serve uploaded files
Route::get('/uploads/{path}', function ($path) {
    $filePath = storage_path('app/uploads/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
    ]);
})->where('path', '.*');

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Teacher Routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

    // Classroom
    Route::get('/classrooms', [App\Http\Controllers\Teacher\ClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [App\Http\Controllers\Teacher\ClassroomController::class, 'create'])->name('classrooms.create');
    Route::post('/classrooms', [App\Http\Controllers\Teacher\ClassroomController::class, 'store'])->name('classrooms.store');
    Route::get('/classrooms/{classroom}', [App\Http\Controllers\Teacher\ClassroomController::class, 'show'])->name('classrooms.show');
    Route::get('/classrooms/{classroom}/edit', [App\Http\Controllers\Teacher\ClassroomController::class, 'edit'])->name('classrooms.edit');
    Route::put('/classrooms/{classroom}', [App\Http\Controllers\Teacher\ClassroomController::class, 'update'])->name('classrooms.update');
    Route::delete('/classrooms/{classroom}', [App\Http\Controllers\Teacher\ClassroomController::class, 'destroy'])->name('classrooms.destroy');

    // Students
    Route::get('/students', [App\Http\Controllers\Teacher\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [App\Http\Controllers\Teacher\StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [App\Http\Controllers\Teacher\StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [App\Http\Controllers\Teacher\StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [App\Http\Controllers\Teacher\StudentController::class, 'destroy'])->name('students.destroy');

    // Parents
    Route::get('/parents', [App\Http\Controllers\Teacher\ParentController::class, 'index'])->name('parents.index');
    Route::get('/parents/create', [App\Http\Controllers\Teacher\ParentController::class, 'create'])->name('parents.create');
    Route::post('/parents', [App\Http\Controllers\Teacher\ParentController::class, 'store'])->name('parents.store');
    Route::get('/parents/{parent}', [App\Http\Controllers\Teacher\ParentController::class, 'show'])->name('parents.show');
    Route::get('/parents/{parent}/edit', [App\Http\Controllers\Teacher\ParentController::class, 'edit'])->name('parents.edit');
    Route::put('/parents/{parent}', [App\Http\Controllers\Teacher\ParentController::class, 'update'])->name('parents.update');
    Route::delete('/parents/{parent}', [App\Http\Controllers\Teacher\ParentController::class, 'destroy'])->name('parents.destroy');

    // Link Parent-Student
    Route::get('/links', [App\Http\Controllers\Teacher\LinkController::class, 'index'])->name('links.index');
    Route::get('/links/create', [App\Http\Controllers\Teacher\LinkController::class, 'create'])->name('links.create');
    Route::post('/links', [App\Http\Controllers\Teacher\LinkController::class, 'store'])->name('links.store');
    Route::delete('/links/{link}', [App\Http\Controllers\Teacher\LinkController::class, 'destroy'])->name('links.destroy');

    // Media Upload
    Route::get('/upload', [App\Http\Controllers\Teacher\MediaController::class, 'create'])->name('upload.create');
    Route::post('/upload', [App\Http\Controllers\Teacher\MediaController::class, 'store'])->name('upload.store');
});

// Student Routes
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/media', [App\Http\Controllers\Student\MediaController::class, 'index'])->name('media.index');
    Route::get('/media/{media}', [App\Http\Controllers\Student\MediaController::class, 'show'])->name('media.show');
});

// Parent Routes
Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/media', [App\Http\Controllers\Parent\MediaController::class, 'index'])->name('media.index');
    Route::get('/media/{media}', [App\Http\Controllers\Parent\MediaController::class, 'show'])->name('media.show');
});
