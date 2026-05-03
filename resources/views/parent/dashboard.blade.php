@extends('layouts.app')

@section('title', 'แดชบอร์ดผู้ปกครอง')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">ยินดีต้อนรับ, {{ auth()->user()->name }}</h1>
    <p class="text-gray-600">ดูแล {{ $stats['children_count'] }} คน</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">จำนวนบุตรหลาน</p>
                <p class="text-3xl font-bold text-indigo-600">{{ $stats['children_count'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">จำนวนไฟล์ทั้งหมด</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['media_count'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-lg font-semibold mb-4">บุตรหลานที่ดูแล</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($linkedStudents as $ls)
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="font-bold">{{ $ls->student->name ?? '-' }}</p>
                <p class="text-sm text-gray-500">
                    {{ $ls->student->code ?? '' }} | {{ $ls->student->classroom->name ?? '' }}
                </p>
            </div>
        @endforeach
    </div>
    <div class="mt-4">
        <a href="{{ route('parent.media.index') }}" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            ดูไฟล์ทั้งหมด
        </a>
    </div>
</div>

@if(isset($recentMedia) && $recentMedia->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">ไฟล์ล่าสุด</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($recentMedia as $media)
                <a href="{{ route('parent.media.show', $media) }}" class="block">
                    <div class="bg-gray-50 rounded-lg overflow-hidden hover:shadow-md transition">
                        @if($media->type === 'image')
                            <img src="{{ $media->url }}" alt="{{ $media->original_name }}" class="w-full h-32 object-cover">
                        @else
                            <div class="w-full h-32 bg-red-50 flex items-center justify-center">
                                <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        <div class="p-2">
                            <p class="text-sm font-medium">{{ $media->student->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $media->original_name }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
@endsection
