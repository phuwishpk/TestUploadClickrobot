<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;

        $stats = [
            'classrooms' => $school->classrooms()->count(),
            'teachers' => $school->users()->where('role', 'teacher')->count(),
            'students' => Student::whereIn('classroom_id', $school->classrooms()->pluck('id'))->count(),
        ];

        $classrooms = $school->classrooms()->with('teacher')->get();

        return view('school_admin.dashboard', compact('stats', 'classrooms', 'school'));
    }
}
