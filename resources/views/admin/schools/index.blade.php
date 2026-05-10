@extends('layouts.app')

@section('title', 'Schools')

@section('content')
<div class="pb-16">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Schools</h1>
        <a href="{{ route('admin.schools.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Add School
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Classrooms</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($schools as $school)
                <tr>
                    <td class="px-6 py-4">{{ $school->name }}</td>
                    <td class="px-6 py-4">{{ $school->code ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $school->classrooms_count }}</td>
                    <td class="px-6 py-4">{{ $school->users_count }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.schools.show', $school) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                        <a href="{{ route('admin.schools.edit', $school) }}" class="text-green-600 hover:text-green-800">Edit</a>
                        <form action="{{ route('admin.schools.destroy', $school) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this school?')" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No schools found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $schools->links() }}
    </div>
</div>
@endsection
