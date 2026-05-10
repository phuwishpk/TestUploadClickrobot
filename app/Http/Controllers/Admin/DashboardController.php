<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'schools' => School::count(),
            'users' => User::count(),
            'classrooms' => Classroom::count(),
            'students' => Student::count(),
        ];

        $recentSchools = School::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentSchools'));
    }
}
