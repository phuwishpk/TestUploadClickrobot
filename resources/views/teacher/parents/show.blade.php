@extends('layouts.app')

@section('title', $parent->name)

@section('content')
<div class="mb-6">
    <a href="{{ school_route('teacher.parents.index') }}" class="text-indigo-600 hover:underline">← กลับไปผู้ปกครอง</a>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-xl font-bold">{{ $parent->name }}</h1>
            <p class="text-gray-500">{{ $parent->email }}</p>
        </div>
        <div>
            <a href="{{ school_route('teacher.parents.edit', $parent) }}" class="text-indigo-600 hover:underline">แก้ไข</a>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-4">นักเรียนที่ดูแล</h2>
    @if($parent->parentStudents->count() > 0)
        <div class="space-y-3">
            @foreach($parent->parentStudents as $ps)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium">{{ $ps->student->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $ps->student->code ?? '' }} | {{ $ps->student->classroom->name ?? '' }}
                        </p>
                    </div>
                    <a href="{{ school_route('teacher.students.show', $ps->student) }}" class="text-indigo-600 hover:underline text-sm">ดูข้อมูลนักเรียน</a>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">ยังไม่มีนักเรียนที่เชื่อมโยง</p>
    @endif
    
    <div class="mt-4">
        <a href="{{ school_route('teacher.links.create') }}" class="text-sm text-indigo-600 hover:underline">+ เชื่อมโยงนักเรียน</a>
    </div>
</div>
@endsection
