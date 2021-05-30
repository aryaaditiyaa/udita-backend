<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\StudentActivityUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentActivityUnitController extends Controller
{
    public function index()
    {
        $sau = StudentActivityUnit::where('status', 'active')->latest()->get();
        return ResponseFormatter::success([
            'sau' => $sau
        ], 'Fetch all successful');
    }

    public function show(Request $request, $id)
    {
        $sau = StudentActivityUnit::findOrFail($id);
        return ResponseFormatter::success([
            'sau' => $sau
        ], 'Fetch successful');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:255',
            'image_path' => 'sometimes|image'
        ]);

        if ($request->file('image_path')) {
            $image_path = Storage::putFile(
                'public/users/' . date('FY'),
                $request->file('image_path')
            );
        }

        $sau = StudentActivityUnit::findOrFail($id);

        $sau->update([
            'name' => $request->name ? $request->name : $sau->name,
            'description' => $request->description ? $request->description : $sau->description,
            'image_path' => $request->file('image_path') ? substr($image_path, 7) : $sau->image_path,
        ]);

        return ResponseFormatter::success([
            'sau' => $sau
        ], 'Update SAU successful');
    }
}
