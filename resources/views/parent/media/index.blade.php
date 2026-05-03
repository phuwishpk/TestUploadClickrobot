@extends('layouts.app')

@section('title', 'ไฟล์ของบุตรหลาน')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">ไฟล์ของบุตรหลาน</h1>
</div>

<div class="mb-4">
    <form method="GET" class="flex items-center space-x-4">
        <label class="text-sm text-gray-600">เลือกบุตรหลาน:</label>
        <select name="student_id" onchange="this.form.submit()" class="border border-gray-300 rounded px-3 py-1">
            <option value="">ทั้งหมด</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ $selectedStudentId == $student->id ? 'selected' : '' }}>
                    {{ $student->name }} ({{ $student->classroom->name ?? '' }})
                </option>
            @endforeach
        </select>
    </form>
</div>

@if($media->count() > 0)
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($media as $m)
            <a href="{{ route('parent.media.show', $m) }}" class="block">
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition">
                    @if($m->type === 'image')
                        <img src="{{ $m->url }}" alt="{{ $m->original_name }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-red-50 flex items-center justify-center relative">
                            <svg class="w-16 h-16 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <div class="absolute bottom-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">VIDEO</div>
                        </div>
                    @endif
                    <div class="p-3">
                        <p class="text-sm font-medium">{{ $m->student->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $m->original_name }}</p>
                        <p class="text-xs text-gray-400">{{ $m->uploaded_date->format('d/m/Y') }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $media->links() }}
    </div>
@else
    <div class="bg-gray-100 rounded-lg p-8 text-center">
        <p class="text-gray-500">ยังไม่มีไฟล์</p>
    </div>
@endif
@endsection
