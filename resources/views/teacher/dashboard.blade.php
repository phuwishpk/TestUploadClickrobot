@extends('layouts.app')

@section('title', 'แดชบอร์ดครู')

@section('content')
<style>
    html,
    body {
        min-height: 100%;
        overflow-y: auto;
        scroll-behavior: smooth;
    }
</style>

<div class="pb-16">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">ยินดีต้อนรับ, {{ auth()->user()->name }}</h1>
        <p class="text-gray-600">ระบบจัดการคลาสเรียนและอัปโหลดไฟล์</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">จำนวนห้องเรียน</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $stats['classrooms'] }}</p>
                </div>
                <div class="text-indigo-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">จำนวนนักเรียน</p>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['students'] }}</p>
                </div>
                <div class="text-green-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">จำนวนไฟล์ที่อัปโหลด</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['media_count'] }}</p>
                </div>
                <div class="text-purple-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1 space-y-4">
            <a href="{{ route('teacher.classrooms.index') }}" class="block bg-indigo-600 text-white p-4 rounded-lg hover:bg-indigo-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                จัดการห้องเรียน
            </a>
            <a href="{{ route('teacher.students.index') }}" class="block bg-green-600 text-white p-4 rounded-lg hover:bg-green-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                จัดการนักเรียน
            </a>
            <a href="{{ route('teacher.parents.index') }}" class="block bg-yellow-600 text-white p-4 rounded-lg hover:bg-yellow-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                จัดการผู้ปกครอง
            </a>
            <a href="{{ route('teacher.links.index') }}" class="block bg-purple-600 text-white p-4 rounded-lg hover:bg-purple-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                เชื่อมโยงผู้ปกครอง
            </a>
            <a href="{{ route('teacher.upload.create') }}" class="block bg-red-600 text-white p-4 rounded-lg hover:bg-red-700 transition text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                อัปโหลดไฟล์
            </a>
        </div>

        <div class="lg:col-span-3">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">ไฟล์ที่อัปโหลดล่าสุด</h2>
                @if($recentMedia->count() > 0)
                    <div class="space-y-3 max-h-[28rem] overflow-y-auto pr-2">
                        @foreach($recentMedia as $media)
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg group">
                                <div class="flex-shrink-0 mr-3 w-12 h-12">
                                    @if($media->type === 'image')
                                        <img src="{{ $media->url }}" alt="{{ $media->original_name }}" class="w-full h-full object-cover rounded">
                                    @else
                                        @if($media->thumbnail_path)
                                            <img src="{{ $media->thumbnail_url }}" alt="{{ $media->original_name }}" class="w-full h-full object-cover rounded">
                                        @else
                                            <div class="w-full h-full bg-red-100 rounded flex items-center justify-center">
                                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $media->original_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $media->student->name }} | {{ $media->classroom->name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        หลังบีบอัด: {{ $media->formatted_size }}
                                        @if($media->formatted_compression_change)
                                            | {{ $media->formatted_compression_change }}
                                        @endif
                                    </p>
                                </div>
                                <div class="text-xs text-gray-400 mr-3">
                                    {{ $media->uploaded_date->format('d/m/Y') }}
                                </div>
                                <form action="{{ route('teacher.media.destroy', $media) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white p-2 rounded transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">ยังไม่มีไฟล์ที่อัปโหลด</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-2">ยืนยันการลบ</h3>
        <p class="text-gray-600 mb-4">คุณต้องการลบไฟล์นี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้</p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">ยกเลิก</button>
            <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">ลบ</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let deleteFormToSubmit = null;

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        deleteFormToSubmit = this;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteFormToSubmit) {
        deleteFormToSubmit.submit();
    }
});

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteModal').classList.remove('flex');
    deleteFormToSubmit = null;
}
</script>
@endpush
