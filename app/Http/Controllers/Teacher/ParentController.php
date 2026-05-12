<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $parents = User::where('role', 'parent')
            ->with('parentStudents.student.classroom')
            ->get();

        return view('teacher.parents.index', compact('parents'));
    }

    public function create()
    {
        return view('teacher.parents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $parent = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('12345'),
            'role' => 'parent',
        ]);

        return redirect()->route('teacher.parents.show', ['school' => $request->attributes->get('school')->slug, 'parent' => $parent->id])
            ->with('success', 'เพิ่มผู้ปกครองสำเร็จ');
    }

    public function show(Request $request, $parentId)
    {
        $parent = User::findOrFail($parentId);
        if ($parent->role !== 'parent') {
            abort(404);
        }

        $parent->load('parentStudents.student.classroom');

        return view('teacher.parents.show', compact('parent'));
    }

    public function edit(Request $request, $parentId)
    {
        $parent = User::findOrFail($parentId);
        if ($parent->role !== 'parent') {
            abort(404);
        }

        return view('teacher.parents.edit', compact('parent'));
    }

    public function update(Request $request, $parentId)
    {
        $parent = User::findOrFail($parentId);
        if ($parent->role !== 'parent') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $parent->id,
        ]);

        $parent->update($validated);

        return redirect()->route('teacher.parents.show', ['school' => $request->attributes->get('school')->slug, 'parent' => $parent->id])
            ->with('success', 'อัปเดตข้อมูลผู้ปกครองสำเร็จ');
    }

    public function destroy(Request $request, $parentId)
    {
        $parent = User::findOrFail($parentId);
        if ($parent->role !== 'parent') {
            abort(404);
        }

        $parent->parentStudents()->delete();
        $parent->delete();

        return redirect()->route('teacher.parents.index')
            ->with('success', 'ลบผู้ปกครองสำเร็จ');
    }
}
