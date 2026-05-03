<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $classroomId = $request->get('classroom_id');

        $query = Student::whereIn('classroom_id', $user->classrooms()->pluck('id'));

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        $students = $query->with(['classroom', 'user', 'parents'])->get();

        return response()->json($students);
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
        
        if ($classroom->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
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

        return response()->json($student->load(['classroom', 'user']), 201);
    }

    public function show(Student $student)
    {
        if (!$student->classroom || $student->classroom->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student->load(['classroom', 'user', 'parents', 'media']);

        return response()->json($student);
    }

    public function update(Request $request, Student $student)
    {
        if (!$student->classroom || $student->classroom->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);

        $student->update($validated);

        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        if (!$student->classroom || $student->classroom->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted']);
    }
}
