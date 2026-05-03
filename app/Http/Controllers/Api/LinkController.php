<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParentStudent;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index()
    {
        $links = ParentStudent::with(['parent', 'student.classroom'])->get();
        return response()->json($links);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $parent = User::find($validated['parent_id']);
        if ($parent->role !== 'parent') {
            return response()->json(['message' => 'User is not a parent'], 400);
        }

        $exists = ParentStudent::where('parent_id', $validated['parent_id'])
            ->where('student_id', $validated['student_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Link already exists'], 400);
        }

        $link = ParentStudent::create($validated);

        return response()->json($link->load(['parent', 'student']), 201);
    }

    public function destroy(ParentStudent $link)
    {
        $link->delete();
        return response()->json(['message' => 'Link deleted']);
    }
}
