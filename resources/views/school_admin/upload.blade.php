@extends('layouts.app')

@section('title', 'Upload Media')

@section('content')
<div class="pb-16">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Upload Media</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('school_admin.upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Classroom</label>
                    <select name="classroom_id" required
                        class="mt-1 w-full px-4 py-2 border rounded-md @error('classroom_id') border-red-500 @enderror">
                        <option value="">- Select Classroom -</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->name }} ({{ $classroom->students->count() }} students)</option>
                        @endforeach
                    </select>
                    @error('classroom_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Media Type</label>
                    <select name="media_type" required
                        class="mt-1 w-full px-4 py-2 border rounded-md @error('media_type') border-red-500 @enderror">
                        <option value="photo">Photo</option>
                        <option value="video">Video</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Files</label>
                    <input type="file" name="files[]" multiple accept="image/*,video/*" required
                        class="mt-1 w-full @error('files.*') border-red-500 @enderror">
                    @error('files.*')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="mt-6 bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                Upload
            </button>
        </form>
    </div>
</div>
@endsection
