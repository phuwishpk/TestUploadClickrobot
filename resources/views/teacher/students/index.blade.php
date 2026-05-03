@extends('layouts.app')

@section('title', 'นักเรียน')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">นักเรียน</h1>
    <a href="{{ route('teacher.students.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        + เพิ่มนักเรียน
    </a>
</div>

<div class="mb-4">
    <form method="GET" action="{{ route('teacher.students.index') }}" class="flex items-center space-x-4">
        <label class="text-sm text-gray-600">กรองตามห้องเรียน:</label>
        <select name="classroom_id" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-1">
            <option value="">ทุกห้องเรียน</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>
                    {{ $classroom->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รหัส</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ห้องเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">บัญชี</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($students as $student)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ $student->code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $student->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $student->classroom->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($student->user)
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">มี</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">ไม่มี</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('teacher.students.show', $student) }}" class="text-indigo-600 hover:underline mr-3">ดู</a>
                        <a href="{{ route('teacher.students.edit', $student) }}" class="text-green-600 hover:underline mr-3">แก้ไข</a>
                        <form action="{{ route('teacher.students.destroy', $student) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการลบนักเรียนนี้?')">ลบ</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">ยังไม่มีนักเรียน</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
