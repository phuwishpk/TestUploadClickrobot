@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="pb-16">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit User</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="mt-1 w-full px-4 py-2 border rounded-md @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="mt-1 w-full px-4 py-2 border rounded-md @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                    <input type="password" name="password"
                        class="mt-1 w-full px-4 py-2 border rounded-md @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" required class="mt-1 w-full px-4 py-2 border rounded-md">
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="school_admin" {{ $user->role === 'school_admin' ? 'selected' : '' }}>School Admin</option>
                        <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="parent" {{ $user->role === 'parent' ? 'selected' : '' }}>Parent</option>
                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">School</label>
                    <select name="school_id" class="mt-1 w-full px-4 py-2 border rounded-md">
                        <option value="">- Select School -</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ $user->school_id == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">Update</button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
