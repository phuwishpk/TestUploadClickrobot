<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = User::where('school_id', auth()->user()->school_id)
            ->where('role', 'teacher')
            ->latest()
            ->paginate(10);
        return view('school_admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('school_admin.teachers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $validated['role'] = 'teacher';
        $validated['school_id'] = auth()->user()->school_id;
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->to(school_route('school_admin.teachers.index'))->with('success', 'Teacher created successfully');
    }

    public function show(Request $request, $teacher)
    {
        abort_unless($teacher->school_id === auth()->user()->school_id && $teacher->role === 'teacher', 403);
        $teacher->load('classrooms');
        return view('school_admin.teachers.show', compact('teacher'));
    }

    public function edit(Request $request, $teacher)
    {
        abort_unless($teacher->school_id === auth()->user()->school_id && $teacher->role === 'teacher', 403);
        return view('school_admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, $teacher)
    {
        abort_unless($teacher->school_id === auth()->user()->school_id && $teacher->role === 'teacher', 403);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->id,
        ];

        if ($request->password) {
            $rules['password'] = 'min:6';
            $teacher->password = Hash::make($request->password);
        }

        $validated = $request->validate($rules);
        $teacher->update($validated);

        return redirect()->to(school_route('school_admin.teachers.index'))->with('success', 'Teacher updated successfully');
    }

    public function destroy(Request $request, $teacher)
    {
        abort_unless($teacher->school_id === auth()->user()->school_id && $teacher->role === 'teacher', 403);
        $teacher->delete();
        return redirect()->to(school_route('school_admin.teachers.index'))->with('success', 'Teacher deleted successfully');
    }
}
