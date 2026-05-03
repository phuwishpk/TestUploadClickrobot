@extends('layouts.app')

@section('title', 'อัปโหลดไฟล์')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">อัปโหลดไฟล์</h1>
    <p class="text-gray-600">อัปโหลดรูปภาพและวีดีโอให้นักเรียน</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('teacher.upload.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-1">ห้องเรียน</label>
                <select name="classroom_id" id="classroom_id" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('classroom_id') border-red-500 @enderror"
                    required onchange="loadStudents(this.value)">
                    <option value="">เลือกห้องเรียน</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" {{ $selectedClassroom && $selectedClassroom->id == $classroom->id ? 'selected' : '' }}>
                            {{ $classroom->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="upload_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่อัปโหลด</label>
                <input type="date" name="upload_date" id="upload_date" 
                    value="{{ old('upload_date', now()->format('Y-m-d')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">เลือกนักเรียน</label>
            <div id="student_list" class="border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto">
                <p class="text-gray-500 text-center">กรุณาเลือกห้องเรียนก่อน</p>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                <input type="checkbox" id="select_all" class="mr-1" onchange="toggleSelectAll()">
                <label for="select_all">เลือกทั้งหมด</label>
            </p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">ไฟล์ (รูป/วีดีโอ)</label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 transition cursor-pointer" id="drop_zone">
                <input type="file" name="files[]" id="files" multiple accept="image/*,video/*"
                    class="hidden" onchange="handleFileSelect(this)">
                <label for="files" class="cursor-pointer">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-gray-600">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวาง</p>
                    <p class="text-xs text-gray-400 mt-1">รองรับ: JPG, PNG, GIF, WebP, MP4, MOV, AVI (ไม่เกิน 200MB ต่อไฟล์)</p>
                </label>
            </div>
            <div id="file_preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition">
                อัปโหลดไฟล์
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const classrooms = @json($classrooms);

function loadStudents(classroomId) {
    const container = document.getElementById('student_list');
    const classroom = classrooms.find(c => c.id == classroomId);
    
    if (!classroom || !classroom.students || classroom.students.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center">ไม่มีนักเรียนในห้องนี้</p>';
        return;
    }

    let html = '';
    classroom.students.forEach(student => {
        html += `
            <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                <input type="checkbox" name="student_ids[]" value="${student.id}" class="student-checkbox w-4 h-4 text-indigo-600 rounded mr-3">
                <span class="text-sm">${student.code} - ${student.name}</span>
            </label>
        `;
    });
    container.innerHTML = html;
}

function toggleSelectAll() {
    const checked = document.getElementById('select_all').checked;
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = checked);
}

function handleFileSelect(input) {
    const container = document.getElementById('file_preview');
    container.innerHTML = '';
    
    Array.from(input.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 rounded p-2 text-sm';
        
        if (file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            div.innerHTML = `<img src="${url}" class="w-full h-24 object-cover rounded mb-1"><span class="truncate">${file.name}</span><span class="text-xs text-gray-400 block">${(file.size/1024).toFixed(1)} KB</span>`;
        } else {
            div.innerHTML = `<div class="w-full h-24 bg-red-50 rounded flex items-center justify-center mb-1"><span class="text-2xl">🎬</span></div><span class="truncate">${file.name}</span><span class="text-xs text-gray-400 block">${(file.size/1024).toFixed(1)} KB</span>`;
        }
        
        container.appendChild(div);
    });
}

// Drop zone
const dropZone = document.getElementById('drop_zone');
dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.classList.add('border-indigo-400', 'bg-indigo-50');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('border-indigo-400', 'bg-indigo-50');
    const input = document.getElementById('files');
    input.files = e.dataTransfer.files;
    handleFileSelect(input);
});

// Load students if classroom already selected
@if($selectedClassroom)
loadStudents({{ $selectedClassroom->id }});
@endif
</script>
@endpush
