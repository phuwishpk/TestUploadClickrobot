<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use App\Services\R2FolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $classroomId = $request->get('classroom_id');

        $query = Student::whereHas('classrooms', function ($q) {
            $q->where('classrooms.school_id', auth()->user()->school_id);
        })->with(['classrooms', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($classroomId) {
            $query->whereHas('classrooms', function ($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            });
        }

        $students = $query->latest()->paginate(15);
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)->get();

        return view('school_admin.students.index', compact('students', 'classrooms', 'search', 'classroomId'));
    }

    public function create(Request $request)
    {
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)->get();
        $selectedClassroomId = $request->get('classroom_id');
        return view('school_admin.students.create', compact('classrooms', 'selectedClassroomId'));
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

        $schoolId = auth()->user()->school_id;

        // Verify all classrooms belong to this school
        $schoolClassroomIds = Classroom::where('school_id', $schoolId)->pluck('id')->toArray();
        foreach ($validated['classroom_ids'] as $classroomId) {
            if (!in_array($classroomId, $schoolClassroomIds)) {
                abort(403);
            }
        }

        $primaryClassroomId = $validated['classroom_ids'][0];

        $student = Student::create([
            'name' => $validated['name'],
            'code' => Student::generateCode($primaryClassroomId),
            'classroom_id' => $primaryClassroomId,
        ]);

        $student->classrooms()->attach($validated['classroom_ids']);

        // Create R2 folders for student in each classroom
        $r2Service = app(R2FolderService::class);
        foreach ($validated['classroom_ids'] as $classroomId) {
            $classroom = Classroom::find($classroomId);
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
                'school_id' => $schoolId,
            ]);
            $student->update(['user_id' => $user->id]);
            $message .= ' และสร้างบัญชี: ' . $validated['email'];
        }

        return redirect()->route('school_admin.students.show', ['school' => auth()->user()->school->slug, 'student' => $student->id])
            ->with('success', $message);
    }

    public function show(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorizeSchoolAccess($student);
        $student->load(['classrooms', 'user', 'parents', 'media' => function($q) {
            $q->latest()->limit(20);
        }]);
        return view('school_admin.students.show', compact('student'));
    }

    public function edit(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorizeSchoolAccess($student);
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)->get();
        $selectedClassrooms = $student->classrooms()->pluck('id')->toArray();
        return view('school_admin.students.edit', compact('student', 'classrooms', 'selectedClassrooms'));
    }

    public function update(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorizeSchoolAccess($student);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'classroom_ids' => 'required|array|min:1',
            'classroom_ids.*' => 'exists:classrooms,id',
        ]);

        $schoolId = auth()->user()->school_id;

        // Verify all classrooms belong to this school
        $schoolClassroomIds = Classroom::where('school_id', $schoolId)->pluck('id')->toArray();
        foreach ($validated['classroom_ids'] as $classroomId) {
            if (!in_array($classroomId, $schoolClassroomIds)) {
                abort(403);
            }
        }

        $student->update([
            'name' => $validated['name'],
            'classroom_id' => $validated['classroom_ids'][0],
        ]);

        $student->classrooms()->sync($validated['classroom_ids']);

        return redirect()->route('school_admin.students.show', ['school' => auth()->user()->school->slug, 'student' => $student->id])
            ->with('success', 'อัปเดตข้อมูลนักเรียนสำเร็จ');
    }

    public function destroy(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorizeSchoolAccess($student);

        if ($student->user) {
            $student->user->update(['student_code' => null]);
        }

        $student->delete();

        return redirect()->route('school_admin.students.index')
            ->with('success', 'ลบนักเรียนสำเร็จ');
    }

    public function createAccount(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorizeSchoolAccess($student);

        if ($student->user_id) {
            return back()->with('error', 'นักเรียนนี้มีบัญชีอยู่แล้ว');
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $student->name,
            'email' => $validated['email'],
            'password' => Hash::make('12345'),
            'role' => 'student',
            'student_code' => $student->code,
            'school_id' => auth()->user()->school_id,
        ]);

        $student->update(['user_id' => $user->id]);

        return back()->with('success', 'สร้างบัญชีสำเร็จ: ' . $validated['email'] . ' (รหัสผ่าน: 12345)');
    }

    protected function authorizeSchoolAccess(Student $student)
    {
        $schoolId = auth()->user()->school_id;
        $hasAccess = $student->classrooms()->where('classrooms.school_id', $schoolId)->exists();

        if (!$hasAccess) {
            abort(403);
        }
    }
}
