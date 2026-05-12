<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentStudent;

class LinkController extends Controller
{
    public function index()
    {
        $links = ParentStudent::with(['parent', 'student.classroom'])->get();
        $parents = User::where('role', 'parent')->get();
        $students = Student::all();

        return view('teacher.links.index', compact('links', 'parents', 'students'));
    }

    public function create()
    {
        $parents = User::where('role', 'parent')->get();
        $students = Student::all();

        return view('teacher.links.create', compact('parents', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $parent = User::find($validated['parent_id']);
        if ($parent->role !== 'parent') {
            return back()->withErrors(['parent_id' => 'ผู้ใช้นี้ไม่ใช่ผู้ปกครอง']);
        }

        $exists = ParentStudent::where('parent_id', $validated['parent_id'])
            ->where('student_id', $validated['student_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['student_id' => 'ผู้ปกครองนี้เชื่อมโยงกับนักเรียนคนนี้แล้ว']);
        }

        ParentStudent::create($validated);

        return redirect()->to(school_route('teacher.links.index'))
            ->with('success', 'เชื่อมโยงสำเร็จ');
    }

    public function destroy(ParentStudent $link)
    {
        $link->delete();

        return redirect()->to(school_route('teacher.links.index'))
            ->with('success', 'ยกเลิกการเชื่อมโยงสำเร็จ');
    }
}
