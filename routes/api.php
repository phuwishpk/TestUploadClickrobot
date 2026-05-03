<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\LinkController;

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Teacher API
Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    // Classroom
    Route::get('/classrooms', [ClassroomController::class, 'index']);
    Route::post('/classrooms', [ClassroomController::class, 'store']);
    Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show']);
    Route::put('/classrooms/{classroom}', [ClassroomController::class, 'update']);
    Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy']);

    // Students
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/{student}', [StudentController::class, 'show']);
    Route::put('/students/{student}', [StudentController::class, 'update']);
    Route::delete('/students/{student}', [StudentController::class, 'destroy']);

    // Parents
    Route::get('/parents', [ParentController::class, 'index']);
    Route::post('/parents', [ParentController::class, 'store']);
    Route::get('/parents/{parent}', [ParentController::class, 'show']);
    Route::put('/parents/{parent}', [ParentController::class, 'update']);
    Route::delete('/parents/{parent}', [ParentController::class, 'destroy']);

    // Link Parent-Student
    Route::get('/links', [LinkController::class, 'index']);
    Route::post('/links', [LinkController::class, 'store']);
    Route::delete('/links/{link}', [LinkController::class, 'destroy']);

    // Media Upload
    Route::post('/media/upload', [MediaController::class, 'upload']);
});

// Student API
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
});

// Parent API
Route::middleware(['auth:sanctum', 'role:parent'])->group(function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
});
