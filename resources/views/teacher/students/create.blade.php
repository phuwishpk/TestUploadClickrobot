@extends('layouts.app')

@section('title', 'เพิ่มนักเรียน')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ school_route('teacher.students.index') }}" class="text-indigo-600 hover:underline">← กลับไปนักเรียน</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-bold mb-6">เพิ่มนักเรียนใหม่</h1>

        <form action="{{ school_route('teacher.students.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">ชื่อนักเรียน</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    required>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="classroom_ids" class="block text-sm font-medium text-gray-700 mb-1">ห้องเรียน (เลือกได้หลายห้อง)</label>
                <select name="classroom_ids[]" id="classroom_ids" multiple
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('classroom_ids') border-red-500 @enderror"
                    required>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ in_array($classroom->id, old('classroom_ids', [])) ? 'selected' : '' }}>
                            {{ $classroom->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">กด Ctrl/Cmd เพื่อเลือกหลายห้อง</p>
                @error('classroom_ids')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="create_account" value="1" {{ old('create_account') ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">สร้างบัญชีให้นักเรียน (เข้าสู่ระบบได้)</span>
                </label>
            </div>

            <div id="account_fields" class="{{ old('create_account') ? '' : 'hidden' }}">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="student@example.com">
                    <p class="mt-1 text-xs text-gray-500">รหัสผ่านเริ่มต้น: 12345</p>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ school_route('teacher.students.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    ยกเลิก
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    เพิ่มนักเรียน
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelector('input[name="create_account"]').addEventListener('change', function() {
    document.getElementById('account_fields').classList.toggle('hidden', !this.checked);
});
</script>
@endpush
