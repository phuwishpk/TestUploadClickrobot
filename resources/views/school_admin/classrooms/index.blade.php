@extends('layouts.app')

@section('title', 'Classrooms')

@section('content')
<div class="pb-16">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Classrooms</h1>
        <a href="{{ route('school_admin.classrooms.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Add Classroom
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($classrooms as $classroom)
                <tr>
                    <td class="px-6 py-4">{{ $classroom->name }}</td>
                    <td class="px-6 py-4">{{ $classroom->teacher?->name ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $classroom->students()->count() }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('school_admin.classrooms.show', $classroom) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                        <a href="{{ route('school_admin.classrooms.edit', $classroom) }}" class="text-green-600 hover:text-green-800">Edit</a>
                        <form action="{{ route('school_admin.classrooms.destroy', $classroom) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this classroom?')" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No classrooms found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $classrooms->links() }}
    </div>
</div>
@endsection
