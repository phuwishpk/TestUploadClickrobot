<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classroom;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = $request->user()->classrooms()->withCount('students')->get();
        return view('teacher.classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('teacher.classrooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $classroom = $request->user()->classrooms()->create($validated);

        return redirect()->to(school_route('teacher.classrooms.show', ['classroom' => $classroom->id]))
            ->with('success', 'สร้างห้องเรียนสำเร็จ');
    }

    public function show(Request $request, $classroom)
    {
        $this->authorize('view', $classroom);

        $students = $classroom->students()->with('user')->get();
        $media = $classroom->media()->with('student')->latest()->limit(20)->get();

        return view('teacher.classrooms.show', compact('classroom', 'students', 'media'));
    }

    public function edit(Request $request, $classroom)
    {
        $this->authorize('update', $classroom);
        return view('teacher.classrooms.edit', compact('classroom'));
    }

    public function update(Request $request, $classroom)
    {
        $this->authorize('update', $classroom);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $classroom->update($validated);

        return redirect()->to(school_route('teacher.classrooms.show', ['classroom' => $classroom->id]))
            ->with('success', 'อัปเดตห้องเรียนสำเร็จ');
    }

    public function destroy(Request $request, $classroom)
    {
        $this->authorize('delete', $classroom);
        $classroom->delete();

        return redirect()->to(school_route('teacher.classrooms.index'))
            ->with('success', 'ลบห้องเรียนสำเร็จ');
    }
}
