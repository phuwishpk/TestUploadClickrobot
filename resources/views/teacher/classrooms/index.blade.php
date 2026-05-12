@extends('layouts.app')

@section('title', 'ห้องเรียน')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">ห้องเรียน</h1>
    <a href="{{ school_route('teacher.classrooms.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        + สร้างห้องเรียน
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อห้องเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จำนวนนักเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($classrooms as $classroom)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ school_route('teacher.classrooms.show', $classroom) }}" class="text-indigo-600 hover:underline font-medium">
                            {{ $classroom->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                        {{ $classroom->students_count }} คน
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ school_route('teacher.classrooms.show', $classroom) }}" class="text-indigo-600 hover:underline mr-3">ดู</a>
                        <a href="{{ school_route('teacher.classrooms.edit', $classroom) }}" class="text-green-600 hover:underline mr-3">แก้ไข</a>
                        <form action="{{ school_route('teacher.classrooms.destroy', $classroom) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการลบห้องเรียนนี้?')">ลบ</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">ยังไม่มีห้องเรียน</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
