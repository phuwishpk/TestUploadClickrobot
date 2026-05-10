<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\School;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with(['classroom.school', 'student', 'uploader']);

        if ($request->school_id) {
            $query->whereHas('classroom', fn($q) => $q->where('school_id', $request->school_id));
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $media = $query->latest()->paginate(20);
        $schools = School::all();

        return view('admin.media.index', compact('media', 'schools'));
    }

    public function show(Media $media)
    {
        $media->load(['classroom.school', 'student', 'uploader']);
        return view('admin.media.show', compact('media'));
    }
}
