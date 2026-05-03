<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentController extends Controller
{
    public function index()
    {
        $parents = User::where('role', 'parent')
            ->with('parentStudents.student.classroom')
            ->get();

        return response()->json($parents);
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

        return response()->json($parent, 201);
    }

    public function show(User $parent)
    {
        if ($parent->role !== 'parent') {
            return response()->json(['message' => 'Not found'], 404);
        }

        $parent->load('parentStudents.student.classroom');

        return response()->json($parent);
    }

    public function update(Request $request, User $parent)
    {
        if ($parent->role !== 'parent') {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $parent->id,
        ]);

        $parent->update($validated);

        return response()->json($parent);
    }

    public function destroy(User $parent)
    {
        if ($parent->role !== 'parent') {
            return response()->json(['message' => 'Not found'], 404);
        }

        $parent->parentStudents()->delete();
        $parent->delete();

        return response()->json(['message' => 'Parent deleted']);
    }
}
