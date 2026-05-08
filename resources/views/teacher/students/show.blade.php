@extends('layouts.app')

@section('title', $student->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('teacher.students.index') }}" class="text-indigo-600 hover:underline">← กลับไปนักเรียน</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-xl font-bold">{{ $student->name }}</h1>
                    <p class="text-gray-500">รหัส: {{ $student->code }}</p>
                    <p class="text-gray-500">ห้องเรียน: {{ $student->classrooms->pluck('name')->implode(', ') }}</p>
                </div>
                <div>
                    <a href="{{ route('teacher.students.edit', $student) }}" class="text-indigo-600 hover:underline">แก้ไข</a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-500">บัญชีผู้ใช้</p>
                    <p class="font-medium">
                        @if($student->user)
                            <span class="text-green-600">{{ $student->user->email }}</span>
                        @else
                            <span class="text-gray-400">ไม่มีบัญชี</span>
                        @endif
                    </p>
                </div>
                <div class="p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-500">ไฟล์ที่อัปโหลด</p>
                    <p class="font-medium text-indigo-600">{{ $student->media->count() }} ไฟล์</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ผู้ปกครองที่เชื่อมโยง</h2>
            @if($student->parents->count() > 0)
                <div class="space-y-2">
                    @foreach($student->parents as $parent)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div>
                                <p class="font-medium">{{ $parent->name }}</p>
                                <p class="text-sm text-gray-500">{{ $parent->email }}</p>
                            </div>
                            <a href="{{ route('teacher.parents.show', $parent) }}" class="text-indigo-600 hover:underline text-sm">ดู</a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">ยังไม่มีผู้ปกครองที่เชื่อมโยง</p>
            @endif
            <div class="mt-4">
                <a href="{{ route('teacher.links.create') }}" class="text-sm text-indigo-600 hover:underline">+ เพิ่มผู้ปกครอง</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ไฟล์ล่าสุด</h2>
            @if($student->media->count() > 0)
                <div class="space-y-3">
                    @foreach($student->media as $media)
                        <div class="flex items-center p-2 bg-gray-50 rounded">
                            <div class="flex-shrink-0 mr-2 text-xs">
                                @if($media->type === 'image')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">IMG</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded">VID</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm truncate">{{ $media->original_name }}</p>
                                <p class="text-xs text-gray-400">{{ $media->uploaded_date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">ยังไม่มีไฟล์</p>
            @endif
        </div>
    </div>
</div>
@endsection
