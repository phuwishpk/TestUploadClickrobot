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
        $classrooms = $user->classrooms;

        return view('teacher.students.index', compact('students', 'classrooms', 'classroomId', 'search'));
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
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
            'email' => 'nullable|email|unique:users,email',
            'create_account' => 'boolean',
        ]);

        $classroomIds = $validated['classroom_ids'];
        $primaryClassroomId = $classroomIds[0];

        // ตรวจสอบว่าครูมีสิทธิ์ในทุก classroom ที่เลือก
        $userClassroomIds = $request->user()->classrooms()->pluck('id')->toArray();
        foreach ($classroomIds as $classroomId) {
            if (!in_array($classroomId, $userClassroomIds)) {
                abort(403);
            }
        }

        $student = Student::create([
            'name' => $validated['name'],
            'code' => Student::generateCode($primaryClassroomId),
            'classroom_id' => $primaryClassroomId, // เก็บ classroom แรกเป็น primary
        ]);

        // เพิ่มความสัมพันธ์หลาย classroom
        $student->classrooms()->attach($classroomIds);

        // สร้างโฟลเดอร์นักเรียนในทุก classroom ที่เลือก
        $r2Service = app(\App\Services\R2FolderService::class);
        foreach ($classroomIds as $classroomId) {
            $classroom = \App\Models\Classroom::find($classroomId);
            $r2Service->createStudentFolder($classroom, $student);
        }

        $message = 'เพิ่มนักเรียนสำเร็จ รหัส: ' . $student->code;

        if (!empty($validated['create_account']) && !empty($validated['email'])) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('12345'),
                'role' => 'student',
                'student_code' => $student->code,
                'school_id' => $request->user()->school_id,
            ]);
            $student->update(['user_id' => $user->id]);
            $message .= ' และสร้างบัญชี: ' . $validated['email'];
        }

        return redirect()->route('teacher.students.show', ['school' => $request->attributes->get('school')->slug, 'student' => $student->id])
            ->with('success', $message);
    }

    public function createAccount(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $userClassroomIds = $request->user()->classrooms()->pluck('id')->toArray();
        $hasAccess = $student->classrooms()->whereIn('classrooms.id', $userClassroomIds)->exists();

        if (!$hasAccess) {
            abort(403);
        }

        if ($student->user_id) {
            return back()->with('error', 'นักเรียนนี้มีบัญชีอยู่แล้ว');
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        // DEBUG: Log student_code info
        \Log::debug('[DEBUG] createAccount student_code', [
            'student_code' => $student->code,
            'school_id' => $request->user()->school_id,
            'existing_users_with_same_code' => User::where('student_code', $student->code)->get(['id', 'email', 'student_code', 'school_id'])->toArray(),
        ]);

        $user = User::create([
            'name' => $student->name,
            'email' => $validated['email'],
            'password' => Hash::make('12345'),
            'role' => 'student',
            'student_code' => $student->code,
            'school_id' => $request->user()->school_id,
        ]);

        $student->update(['user_id' => $user->id]);

        return back()->with('success', 'สร้างบัญชีสำเร็จ: ' . $validated['email'] . ' (รหัสผ่าน: 12345)');
    }

    public function show(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorize('view', $student);
        $student->load(['classrooms', 'user', 'parents', 'media' => function($q) {
            $q->latest()->limit(20);
        }]);
        return view('teacher.students.show', compact('student'));
    }

    public function edit(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorize('update', $student);
        $classrooms = auth()->user()->classrooms;
        $selectedClassrooms = $student->classrooms()->pluck('id')->toArray();
        return view('teacher.students.edit', compact('student', 'classrooms', 'selectedClassrooms'));
    }

    public function update(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorize('update', $student);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
        ]);

        // อัปเดต primary classroom
        $student->update([
            'name' => $validated['name'],
            'classroom_id' => $validated['classroom_ids'][0],
        ]);

        // อัปเดตความสัมพันธ์หลาย classroom
        $student->classrooms()->sync($validated['classroom_ids']);

        return redirect()->route('teacher.students.show', ['school' => $request->attributes->get('school')->slug, 'student' => $student->id])
            ->with('success', 'อัปเดตข้อมูลนักเรียนสำเร็จ');
    }

    public function destroy(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorize('delete', $student);
        
        if ($student->user) {
            $student->user->update(['user_id' => null]);
        }
        
        $student->delete();

        return redirect()->route('teacher.students.index')
            ->with('success', 'ลบนักเรียนสำเร็จ');
    }
}
