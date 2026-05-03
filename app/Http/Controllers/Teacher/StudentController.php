<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $classroomId = $request->get('classroom_id');

        $query = Student::whereIn('classroom_id', $user->classrooms()->pluck('id'));

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        $students = $query->with(['classroom', 'user', 'parents'])->get();
        $classrooms = $user->classrooms;

        return view('teacher.students.index', compact('students', 'classrooms', 'classroomId'));
    }

    public function create(Request $request)
    {
        $classrooms = $request->user()->classrooms;
        return view('teacher.students.create', compact('classrooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
            'email' => 'nullable|email|unique:users,email',
            'create_account' => 'boolean',
        ]);

        $classroom = Classroom::findOrFail($validated['classroom_id']);
        
        if (!$request->user()->classrooms()->where('id', $classroom->id)->exists()) {
            abort(403);
        }

        $student = Student::create([
            'name' => $validated['name'],
            'code' => Student::generateCode(),
            'classroom_id' => $validated['classroom_id'],
        ]);

        if (!empty($validated['create_account']) && !empty($validated['email'])) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('12345'),
                'role' => 'student',
                'student_code' => $student->code,
            ]);
            $student->update(['user_id' => $user->id]);
        }

        return redirect()->route('teacher.students.show', $student)
            ->with('success', 'เพิ่มนักเรียนสำเร็จ รหัส: ' . $student->code);
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);
        $student->load(['classroom', 'user', 'parents', 'media' => function($q) {
            $q->latest()->limit(20);
        }]);
        return view('teacher.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $this->authorize('update', $student);
        $classrooms = auth()->user()->classrooms;
        return view('teacher.students.edit', compact('student', 'classrooms'));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $student->update($validated);

        return redirect()->route('teacher.students.show', $student)
            ->with('success', 'อัปเดตข้อมูลนักเรียนสำเร็จ');
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);
        
        if ($student->user) {
            $student->user->update(['user_id' => null]);
        }
        
        $student->delete();

        return redirect()->route('teacher.students.index')
            ->with('success', 'ลบนักเรียนสำเร็จ');
    }
}
