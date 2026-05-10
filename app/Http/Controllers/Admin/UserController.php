<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('school');

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->school_id) {
            $query->where('school_id', $request->school_id);
        }

        $users = $query->latest()->paginate(15);
        $schools = School::all();

        return view('admin.users.index', compact('users', 'schools'));
    }

    public function create()
    {
        $schools = School::all();
        return view('admin.users.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,school_admin,teacher,parent,student',
            'school_id' => 'nullable|exists:schools,id',
            'student_code' => 'nullable|string|max:50',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $user->load('school');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $schools = School::all();
        return view('admin.users.edit', compact('user', 'schools'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,school_admin,teacher,parent,student',
            'school_id' => 'nullable|exists:schools,id',
            'student_code' => 'nullable|string|max:50',
        ];

        if ($request->password) {
            $rules['password'] = 'min:6';
            $user->password = Hash::make($request->password);
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }
}
