<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;
use App\Models\User;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = auth()->user()->school->classrooms()->with('teacher')->latest()->paginate(10);
        return view('school_admin.classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        $teachers = User::where('school_id', auth()->user()->school_id)
            ->where('role', 'teacher')
            ->get();
        return view('school_admin.classrooms.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $validated['school_id'] = auth()->user()->school_id;

        Classroom::create($validated);

        return redirect()->route('school_admin.classrooms.index')->with('success', 'Classroom created successfully');
    }

    public function show(Classroom $classroom)
    {
        abort_unless($classroom->school_id === auth()->user()->school_id, 403);
        $classroom->load('students.parents', 'teacher');
        return view('school_admin.classrooms.show', compact('classroom'));
    }

    public function edit(Classroom $classroom)
    {
        abort_unless($classroom->school_id === auth()->user()->school_id, 403);
        $teachers = User::where('school_id', auth()->user()->school_id)
            ->where('role', 'teacher')
            ->get();
        return view('school_admin.classrooms.edit', compact('classroom', 'teachers'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        abort_unless($classroom->school_id === auth()->user()->school_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $classroom->update($validated);

        return redirect()->route('school_admin.classrooms.index')->with('success', 'Classroom updated successfully');
    }

    public function destroy(Classroom $classroom)
    {
        abort_unless($classroom->school_id === auth()->user()->school_id, 403);
        $classroom->delete();
        return redirect()->route('school_admin.classrooms.index')->with('success', 'Classroom deleted successfully');
    }
}
