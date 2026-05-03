@extends('layouts.app')

@section('title', 'สร้างห้องเรียน')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('teacher.classrooms.index') }}" class="text-indigo-600 hover:underline">← กลับไปห้องเรียน</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold mb-6">สร้างห้องเรียนใหม่</h1>

        <form action="{{ route('teacher.classrooms.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อห้องเรียน</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    placeholder="เช่น ม.1/1" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('teacher.classrooms.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    ยกเลิก
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    สร้างห้องเรียน
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
