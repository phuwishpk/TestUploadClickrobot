@extends('layouts.app')

@section('title', $media->original_name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.media.index') }}" class="text-indigo-600 hover:underline">← กลับไปไฟล์</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <h1 class="text-xl font-bold">{{ $media->original_name }}</h1>
        <p class="text-gray-500 text-sm mt-1">
            <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs mr-2">
                {{ $media->classroom->school->name ?? 'No school' }}
            </span>
            {{ $media->student->name ?? 'No student' }} | {{ $media->classroom->name ?? 'No classroom' }} | {{ $media->uploaded_date->format('d/m/Y H:i') }}
        </p>
        <p class="text-gray-400 text-xs mt-1">
            Uploaded by: {{ $media->uploader->name ?? 'Unknown' }}
        </p>
    </div>

    <div class="bg-black flex items-center justify-center" style="min-height: 400px;">
        @if($media->type === 'image')
            <img src="{{ $media->url }}" alt="{{ $media->original_name }}" class="max-w-full max-h-[70vh] object-contain">
        @else
            <video controls class="max-w-full max-h-[70vh]" autoplay>
                <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                เบราว์เซอร์ไม่รองรับการเล่นวีดีโอ
            </video>
        @endif
    </div>

    <div class="p-4 border-t">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">File Size:</span>
                <span class="ml-2">{{ $media->formatted_size }}</span>
            </div>
            @if($media->formatted_compression_change)
            <div>
                <span class="text-gray-500">Compression:</span>
                <span class="ml-2 text-green-600">{{ $media->formatted_compression_change }}</span>
            </div>
            @endif
            <div>
                <span class="text-gray-500">Type:</span>
                <span class="ml-2 uppercase">{{ $media->type }}</span>
            </div>
            <div>
                <span class="text-gray-500">Format:</span>
                <span class="ml-2">{{ $media->mime_type }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
