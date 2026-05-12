@extends('layouts.app')

@section('title', 'แก้ไขนักเรียน')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ school_route('school_admin.students.show', $student) }}" class="text-indigo-600 hover:underline">← กลับไปรายละเอียดนักเรียน</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold mb-6">แก้ไขข้อมูลนักเรียน</h1>

        <form action="{{ school_route('school_admin.students.update', $student) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อนักเรียน <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $student->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    required>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="classroom_ids" class="block text-sm font-medium text-gray-700 mb-1">ห้องเรียน (เลือกได้หลายห้อง) <span class="text-red-500">*</span></label>
                <select name="classroom_ids[]" id="classroom_ids" multiple
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('classroom_ids') border-red-500 @enderror"
                    required>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ in_array($classroom->id, old('classroom_ids', $selectedClassrooms)) ? 'selected' : '' }}>
                            {{ $classroom->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">กด Ctrl/Cmd เพื่อเลือกหลายห้อง</p>
                @error('classroom_ids')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <p class="text-sm text-gray-600">
                    <strong>รหัสนักเรียน:</strong> {{ $student->code }}
                </p>
                @if($student->user)
                    <p class="text-sm text-gray-600 mt-1">
                        <strong>บัญชี:</strong> {{ $student->user->email }}
                    </p>
                @endif
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ school_route('school_admin.students.show', $student) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    ยกเลิก
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
