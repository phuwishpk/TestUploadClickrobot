<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::withCount(['classrooms', 'users'])->latest()->paginate(10);
        return view('admin.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('admin.schools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:schools,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = School::generateSlug($request->name);
        $validated['is_active'] = $request->boolean('is_active', true);

        School::create($validated);

        return redirect()->route('admin.schools.index')->with('success', 'School created successfully');
    }

    public function show(School $school)
    {
        $school->load(['classrooms.teacher', 'users']);
        return view('admin.schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:schools,code,' . $school->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $school->update($validated);

        return redirect()->route('admin.schools.index')->with('success', 'School updated successfully');
    }

    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->route('admin.schools.index')->with('success', 'School deleted successfully');
    }
}
