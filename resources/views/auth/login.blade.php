@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">เข้าสู่ระบบ</h1>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">อีเมล</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                    required autofocus>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
                <input type="password" name="password" id="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror"
                    required>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">เข้าสู่ระบบในฐานะ</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="role" value="teacher" {{ old('role') === 'teacher' ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" required>
                        <span class="ml-2 text-sm text-gray-700">ครู</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="role" value="parent" {{ old('role') === 'parent' ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">ผู้ปกครอง</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="role" value="student" {{ old('role') === 'student' ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">นักเรียน</span>
                    </label>
                </div>
                @error('role')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-50 rounded-md">
            <p class="text-xs text-gray-500 text-center">
                รหัสผ่านเริ่มต้น: <code class="bg-gray-200 px-1 rounded">12345</code>
            </p>
        </div>
    </div>
</div>
@endsection
