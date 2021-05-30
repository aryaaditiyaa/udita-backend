<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user_id) {
            $news = News::where('user_id', $request->user_id)
                ->latest()
                ->limit(50)
                ->get();
        } else {
            $news = News::latest()
                ->limit(50)
                ->get();
        }
        return ResponseFormatter::success([
            'news' => $news
        ], 'Fetch all successful');
    }

    public function show($id)
    {
        $news = News::findOrFail($id);

        return ResponseFormatter::success([
            'news' => $news
        ], 'Fetch successful');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'image_path' => 'required|image'
        ]);

        if ($request->file('image_path')) {
            $image_path = Storage::putFile(
                'public/news/' . date('FY'),
                $request->file('image_path')
            );
        }

        $news = News::create([
            'user_id' => auth()->user()->id,
            'student_activity_unit_id' => auth()->user()->student_activity_unit_id,
            'title' => $request->title,
            'body' => $request->body,
            'image_path' => $request->file('image_path') ? substr($image_path, 7) : null,
        ]);

        return ResponseFormatter::success([
            'news' => $news
        ], 'News added successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string',
            'body' => 'sometimes|string',
            'image_path' => 'sometimes|image'
        ]);

        if ($request->file('image_path')) {
            $image_path = Storage::putFile(
                'public/news/' . date('FY'),
                $request->file('image_path')
            );
        }

        $news = News::find($id);

        $news->update([
            'title' => $request->title ? $request->title : $news->title,
            'body' => $request->body ? $request->body : $news->body,
            'image_path' => $request->file('image_path') ? substr($image_path, 7) : $news->image_path,
        ]);

        return ResponseFormatter::success([
            'news' => $news
        ], 'News updated successfully');
    }

    public function destroy($id)
    {
        News::destroy($id);
        return ResponseFormatter::success([], 'Data has been destroyed');
    }
}
