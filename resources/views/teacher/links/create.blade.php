@extends('layouts.app')

@section('title', 'เพิ่มการเชื่อมโยง')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('teacher.links.index') }}" class="text-indigo-600 hover:underline">← กลับ</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold mb-6">เพิ่มการเชื่อมโยงผู้ปกครอง-นักเรียน</h1>

        <form action="{{ route('teacher.links.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">ผู้ปกครอง</label>
                <select name="parent_id" id="parent_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('parent_id') border-red-500 @enderror"
                    required>
                    <option value="">เลือกผู้ปกครอง</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }} ({{ $parent->email }})
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">นักเรียน</label>
                <select name="student_id" id="student_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('student_id') border-red-500 @enderror"
                    required>
                    <option value="">เลือกนักเรียน</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->code }} - {{ $student->name }} ({{ $student->classroom->name }})
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('teacher.links.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    ยกเลิก
                </a>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    เชื่อมโยง
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
