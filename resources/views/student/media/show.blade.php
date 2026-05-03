@extends('layouts.app')

@section('title', $media->original_name)

@section('content')
<div class="mb-6">
    <a href="{{ route('student.media.index') }}" class="text-indigo-600 hover:underline">← กลับไปไฟล์</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <h1 class="text-xl font-bold">{{ $media->original_name }}</h1>
        <p class="text-gray-500 text-sm">
            {{ $media->student->name }} | {{ $media->classroom->name }} | {{ $media->uploaded_date->format('d/m/Y') }}
        </p>
    </div>

    <div class="bg-black flex items-center justify-center" style="min-height: 400px;">
        @if($media->type === 'image')
            <img src="{{ $media->url }}" alt="{{ $media->original_name }}" class="max-w-full max-h-[70vh] object-contain">
        @else
            <video controls class="max-w-full max-h-[70vh]">
                <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                เบราว์เซอร์ไม่รองรับการเล่นวีดีโอ
            </video>
        @endif
    </div>
</div>
@endsection
