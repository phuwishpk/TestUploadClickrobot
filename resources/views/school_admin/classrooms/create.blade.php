@extends('layouts.app')

@section('title', 'Create Classroom')

@section('content')
<div class="pb-16">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Classroom</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ school_route('school_admin.classrooms.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="mt-1 w-full px-4 py-2 border rounded-md @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teacher</label>
                    <select name="teacher_id" class="mt-1 w-full px-4 py-2 border rounded-md">
                        <option value="">- Select Teacher -</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">Save</button>
                <a href="{{ school_route('school_admin.classrooms.index') }}" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
