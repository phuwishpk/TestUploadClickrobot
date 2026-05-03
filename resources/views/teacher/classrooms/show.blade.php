@extends('layouts.app')

@section('title', $classroom->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('teacher.classrooms.index') }}" class="text-indigo-600 hover:underline">← กลับไปห้องเรียน</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-bold">{{ $classroom->name }}</h1>
                <a href="{{ route('teacher.classrooms.edit', $classroom) }}" class="text-indigo-600 hover:underline">แก้ไข</a>
            </div>
            <p class="text-gray-600">นักเรียน {{ $students->count() }} คน</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">รายชื่อนักเรียน</h2>
                <a href="{{ route('teacher.students.create') }}?classroom_id={{ $classroom->id }}" class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                    + เพิ่มนักเรียน
                </a>
            </div>

            @if($students->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">รหัส</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">บัญชี</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($students as $student)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $student->code }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $student->name }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        @if($student->user)
                                            <span class="text-green-600">มี</span>
                                        @else
                                            <span class="text-gray-400">ไม่มี</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('teacher.students.show', $student) }}" class="text-indigo-600 hover:underline">ดู</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">ยังไม่มีนักเรียนในห้องนี้</p>
            @endif
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">ไฟล์ล่าสุด</h2>
            @if($media->count() > 0)
                <div class="space-y-3">
                    @foreach($media as $m)
                        <div class="flex items-center p-2 bg-gray-50 rounded">
                            <div class="flex-shrink-0 mr-2">
                                @if($m->type === 'image')
                                    <span class="text-blue-500">IMG</span>
                                @else
                                    <span class="text-red-500">VID</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm truncate">{{ $m->original_name }}</p>
                                <p class="text-xs text-gray-400">{{ $m->student->name }}</p>
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
