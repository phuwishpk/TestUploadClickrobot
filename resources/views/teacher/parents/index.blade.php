@extends('layouts.app')

@section('title', 'ผู้ปกครอง')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">ผู้ปกครอง</h1>
    <a href="{{ route('teacher.parents.create') }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
        + เพิ่มผู้ปกครอง
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">อีเมล</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">นักเรียนที่ดูแล</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($parents as $parent)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $parent->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $parent->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @foreach($parent->parentStudents as $ps)
                            <span class="inline-block px-2 py-1 mr-1 text-xs bg-blue-100 text-blue-700 rounded">
                                {{ $ps->student->name ?? '-' }}
                            </span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('teacher.parents.show', $parent) }}" class="text-indigo-600 hover:underline mr-3">ดู</a>
                        <a href="{{ route('teacher.parents.edit', $parent) }}" class="text-green-600 hover:underline mr-3">แก้ไข</a>
                        <form action="{{ route('teacher.parents.destroy', $parent) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการลบ?')">ลบ</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">ยังไม่มีผู้ปกครอง</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
