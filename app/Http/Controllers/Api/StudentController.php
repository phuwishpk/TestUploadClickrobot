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
        $search = $request->get('search');

        $teacherClassroomIds = $user->classrooms()->pluck('id');

        $query = Student::whereHas('classrooms', function ($q) use ($teacherClassroomIds) {
            $q->whereIn('classrooms.id', $teacherClassroomIds);
        });

        if ($classroomId) {
            $query->whereHas('classrooms', function ($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $students = $query->with(['classrooms', 'user', 'parents'])->get();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
            'email' => 'nullable|email|unique:users,email',
            'create_account' => 'boolean',
        ]);

        $classroomIds = $validated['classroom_ids'];

        // ตรวจสอบว่าครูมีสิทธิ์ในทุก classroom ที่เลือก
        foreach ($classroomIds as $classroomId) {
            $classroom = Classroom::find($classroomId);
            if (!$classroom || $classroom->teacher_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $student = Student::create([
            'name' => $validated['name'],
            'code' => Student::generateCode($classroomIds[0]),
            'classroom_id' => $classroomIds[0],
        ]);

        $student->classrooms()->attach($classroomIds);

        // สร้างโฟลเดอร์นักเรียนในทุก classroom
        $r2Service = app(\App\Services\R2FolderService::class);
        foreach ($classroomIds as $classroomId) {
            $classroom = Classroom::find($classroomId);
            $r2Service->createStudentFolder($classroom, $student);
        }

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

        return response()->json($student->load(['classrooms', 'user']), 201);
    }

    public function show(Student $student)
    {
        $teacherClassroomIds = auth()->user()->classrooms()->pluck('id')->toArray();
        $studentClassroomIds = $student->classrooms()->pluck('id')->toArray();
        
        if (empty(array_intersect($teacherClassroomIds, $studentClassroomIds))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student->load(['classrooms', 'user', 'parents', 'media']);

        return response()->json($student);
    }

    public function update(Request $request, Student $student)
    {
        $teacherClassroomIds = auth()->user()->classrooms()->pluck('id')->toArray();
        $studentClassroomIds = $student->classrooms()->pluck('id')->toArray();
        
        if (empty(array_intersect($teacherClassroomIds, $studentClassroomIds))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
        ]);

        $student->update([
            'name' => $validated['name'],
            'classroom_id' => $validated['classroom_ids'][0],
        ]);

        $student->classrooms()->sync($validated['classroom_ids']);

        return response()->json($student->load(['classrooms']));
    }

    public function destroy(Student $student)
    {
        $teacherClassroomIds = auth()->user()->classrooms()->pluck('id')->toArray();
        $studentClassroomIds = $student->classrooms()->pluck('id')->toArray();
        
        if (empty(array_intersect($teacherClassroomIds, $studentClassroomIds))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted']);
    }
}
