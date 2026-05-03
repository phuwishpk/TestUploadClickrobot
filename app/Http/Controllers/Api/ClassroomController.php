<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = $request->user()->classrooms()->withCount('students')->get();
        return response()->json($classrooms);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $classroom = $request->user()->classrooms()->create($validated);

        return response()->json($classroom, 201);
    }

    public function show(Classroom $classroom)
    {
        if ($classroom->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $classroom->load(['students', 'students.user']);

        return response()->json($classroom);
    }

    public function update(Request $request, Classroom $classroom)
    {
        if ($classroom->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $classroom->update($validated);

        return response()->json($classroom);
    }

    public function destroy(Classroom $classroom)
    {
        if ($classroom->teacher_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $classroom->delete();

        return response()->json(['message' => 'Classroom deleted']);
    }
}
