<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Activities;
use App\Models\Documentation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentationController extends Controller
{
    public function store(Request $request)
    {
        if ($request->activity_id) {
            $activity = Activities::findOrFail($request->activity_id);

            if ($activity) {
                $validator = Validator::make($request->all(), [
                    'file_path' => 'mimes:doc,docx,pdf|max:10240'
                ]);

                if ($validator->fails()) {
                    return ResponseFormatter::error([
                        'error' => $validator->errors()
                    ], 'Ups, file yang kamu unggah lebih dari 10 MB', Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                if ($request->file('file_path')) {
                    $file_path = Storage::putFile(
                        'public/documentation/' . date('FY'),
                        $request->file('file_path')
                    );
                }

                $documentation = Documentation::updateOrCreate(
                    [
                        'user_id' => auth()->user()->id,
                        'activity_id' => $activity->id,
                        'student_activity_unit_id' => auth()->user()->student_activity_unit_id
                    ],
                    [
                        'file_path' => $request->file('file_path') ? substr($file_path, 7) : null
                    ]
                );

                $activity->update([
                    'documentation_id' => $documentation->id
                ]);

                return ResponseFormatter::success([
                    'documentation' => $documentation
                ], 'Documentation created successfully');
            }
        }
    }
}
