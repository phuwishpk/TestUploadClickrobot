@extends('layouts.app')

@section('title', 'เชื่อมโยงผู้ปกครอง')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">เชื่อมโยงผู้ปกครอง-นักเรียน</h1>
    <a href="{{ school_route('teacher.links.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
        + เพิ่มการเชื่อมโยง
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้ปกครอง</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">นักเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ห้องเรียน</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($links as $link)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $link->parent->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $link->student->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $link->student->classroom->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <form action="{{ school_route('teacher.links.destroy', $link) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('ต้องการยกเลิกการเชื่อมโยง?')">ยกเลิก</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">ยังไม่มีการเชื่อมโยง</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
