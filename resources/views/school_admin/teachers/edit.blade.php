@extends('layouts.app')

@section('title', 'Edit Teacher')

@section('content')
<div class="pb-16">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Teacher</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('school_admin.teachers.update', $teacher) }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required
                        class="mt-1 w-full px-4 py-2 border rounded-md @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $teacher->email) }}" required
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
            </div>
            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">Update</button>
                <a href="{{ route('school_admin.teachers.index') }}" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
