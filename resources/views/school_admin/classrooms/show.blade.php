@extends('layouts.app')

@section('title', $classroom->name)

@section('content')
<div class="pb-16">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ $classroom->name }}</h1>
        <div class="space-x-2">
            <a href="{{ school_route('school_admin.classrooms.edit', $classroom) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Edit</a>
            <a href="{{ school_route('school_admin.classrooms.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">Teacher</dt>
                    <dd class="font-medium">{{ $classroom->teacher?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Students</dt>
                    <dd class="font-medium">{{ $classroom->students()->count() }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Students</h2>
        </div>
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($classroom->students as $student)
                <tr>
                    <td class="px-6 py-4">{{ $student->name }}</td>
                    <td class="px-6 py-4">{{ $student->code }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="px-6 py-4 text-center text-gray-500">No students</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
