<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;

// Public Routes (no school subdomain required)
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/test-url', [App\Http\Controllers\TestController::class, 'test']);

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

$schoolRoutes = function (): void {
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/test-url', [App\Http\Controllers\TestController::class, 'test'])->name('test');

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
        Route::post('/students/{student}/create-account', [App\Http\Controllers\Teacher\StudentController::class, 'createAccount'])->name('students.create-account');

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
        Route::delete('/media/{media}', [App\Http\Controllers\Teacher\MediaController::class, 'destroy'])->name('media.destroy');
    });

    // Student Routes
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/media', [App\Http\Controllers\Student\MediaController::class, 'index'])->name('media.index');
        Route::get('/media/{media}', [App\Http\Controllers\Student\MediaController::class, 'show'])->name('media.show');
    });

    // Parent Routes
    Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/media', [App\Http\Controllers\Parent\MediaController::class, 'index'])->name('media.index');
        Route::get('/media/{media}', [App\Http\Controllers\Parent\MediaController::class, 'show'])->name('media.show');
    });

    // School Admin Routes
    Route::middleware(['role:school_admin'])->prefix('school-admin')->name('school_admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SchoolAdmin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/classrooms', [App\Http\Controllers\SchoolAdmin\ClassroomController::class, 'index'])->name('classrooms.index');
        Route::get('/classrooms/create', [App\Http\Controllers\SchoolAdmin\ClassroomController::class, 'create'])->name('classrooms.create');
        Route::post('/classrooms', [App\Http\Controllers\SchoolAdmin\ClassroomController::class, 'store'])->name('classrooms.store');
        Route::get('/classrooms/{classroom}', [App\Http\Controllers\SchoolAdmin\ClassroomController::class, 'show'])->name('classrooms.show');
        Route::get('/classrooms/{classroom}/edit', [App\Http\Controllers\SchoolAdmin\ClassroomController::class, 'edit'])->name('classrooms.edit');
        Route::put('/classrooms/{classroom}', [App\Http\Controllers\SchoolAdmin\ClassroomController::class, 'update'])->name('classrooms.update');
        Route::delete('/classrooms/{classroom}', [App\Http\Controllers\SchoolAdmin\ClassroomController::class, 'destroy'])->name('classrooms.destroy');
        Route::get('/teachers', [App\Http\Controllers\SchoolAdmin\TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/create', [App\Http\Controllers\SchoolAdmin\TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [App\Http\Controllers\SchoolAdmin\TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{teacher}', [App\Http\Controllers\SchoolAdmin\TeacherController::class, 'show'])->name('teachers.show');
        Route::get('/teachers/{teacher}/edit', [App\Http\Controllers\SchoolAdmin\TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [App\Http\Controllers\SchoolAdmin\TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [App\Http\Controllers\SchoolAdmin\TeacherController::class, 'destroy'])->name('teachers.destroy');
        Route::get('/students', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/students/{student}/create-account', [App\Http\Controllers\SchoolAdmin\StudentController::class, 'createAccount'])->name('students.create-account');
        Route::get('/upload', [App\Http\Controllers\SchoolAdmin\MediaController::class, 'create'])->name('upload.create');
        Route::post('/upload', [App\Http\Controllers\SchoolAdmin\MediaController::class, 'store'])->name('upload.store');
    });
};

// School routes on school subdomains: bangrak.localhost:8080/teacher/dashboard
Route::middleware(['auth', 'school.domain'])->group($schoolRoutes);

// School routes with path-based fallback: localhost:8080/bangrak/teacher/dashboard
Route::middleware(['auth'])
    ->prefix('{schoolSlug}')
    ->where(['schoolSlug' => '[a-z0-9-]+'])
    ->group($schoolRoutes);

// Admin Routes (main domain - no school prefix)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/schools', [App\Http\Controllers\Admin\SchoolController::class, 'index'])->name('schools.index');
    Route::get('/schools/create', [App\Http\Controllers\Admin\SchoolController::class, 'create'])->name('schools.create');
    Route::post('/schools', [App\Http\Controllers\Admin\SchoolController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}', [App\Http\Controllers\Admin\SchoolController::class, 'show'])->name('schools.show');
    Route::get('/schools/{school}/edit', [App\Http\Controllers\Admin\SchoolController::class, 'edit'])->name('schools.edit');
    Route::put('/schools/{school}', [App\Http\Controllers\Admin\SchoolController::class, 'update'])->name('schools.update');
    Route::delete('/schools/{school}', [App\Http\Controllers\Admin\SchoolController::class, 'destroy'])->name('schools.destroy');
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Media
    Route::get('/media', [App\Http\Controllers\Admin\MediaController::class, 'index'])->name('media.index');
    Route::get('/media/{media}', [App\Http\Controllers\Admin\MediaController::class, 'show'])->name('media.show');
});
