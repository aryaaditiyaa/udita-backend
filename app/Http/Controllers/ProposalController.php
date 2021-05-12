<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Notification;
use App\Models\Proposal;
use App\Models\StudentActivityUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $proposals = Proposal::latest()->get();

        if ($request->category != null && $request->user_id != null) {
            $proposals = Proposal::where('category', $request->category)
                ->where('user_id', $request->user_id)
                ->latest()
                ->get();
        } else if ($request->category != null) {
            $proposals = Proposal::where('category', $request->category)
                ->latest()
                ->get();
        } else if ($request->user_id != null) {
            $proposals = Proposal::where('user_id', $request->user_id)
                ->latest()
                ->get();
        }

        return ResponseFormatter::success([
            'proposal' => $proposals
        ], 'Proposal fetch successfully');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'category' => 'required|string',
                'description' => 'required|string',
                'file_path' => 'mimes:doc,docx,pdf'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'error' => $validator->errors()
                ], 'Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($request->file('file_path')) {
                $image_url = Storage::putFile(
                    'public/proposal/' . date('FY'),
                    $request->file('file_path')
                );
            }

            $proposal = Proposal::create([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'status' => 'pending',
                'file_path' => $request->file('file_path') ? substr($image_url, 7) : null,
            ]);

            if ($proposal != null) {
                Notification::create([
                    'user_id' => $proposal->user_id,
                    'student_activity_unit_id' => null,
                    'category' => 'new-sau',
                    'title' => 'Ada proposal pembentukan ' . $proposal->title
                ]);
            }

            return ResponseFormatter::success([
                'proposal' => $proposal
            ], 'Proposal added successfully');

        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'error' => $exception
            ], 'Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function show($id)
    {
        $proposal = Proposal::findOrFail($id);

        return ResponseFormatter::success([
            'proposal' => $proposal
        ], 'Success');
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category' => 'sometimes|string',
                'description' => 'sometimes|string',
                'status' => 'sometimes|string',
                'file_path' => 'sometimes|mimes:doc,docx,pdf'
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'error' => $validator->errors()
                ], 'Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if ($request->file('file_path')) {
                $image_url = Storage::putFile(
                    'public/proposal/' . date('FY'),
                    $request->file('file_path')
                );
            }

            $proposal = Proposal::findOrFail($id);

            $proposal->update([
                'title' => $request->title ? $request->title : $proposal->title,
                'category' => $request->category ? $request->category : $proposal->category,
                'description' => $request->description ? $request->description : $proposal->description,
                'status' => $request->status ? $request->status : $proposal->status,
                'file_path' => $request->file('file_path') ? substr($image_url, 7) : $proposal->file_path,
            ]);

            if ($request->status == 'approve') {
                StudentActivityUnit::create([
                    'name' => $proposal->title,
                    'description' => $proposal->description,
                    'status' => 'active',
                    'proposal_id' => $proposal->id
                ]);

                User::whereId($proposal->user_id)->update([
                    'role' => 'leader'
                ]);
            }

            return ResponseFormatter::success([
                'proposal' => $proposal
            ], 'Proposal updated successfully');

        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'error' => $exception
            ], 'Something went wrong', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function destroy($id)
    {
        Proposal::findOrFail($id)->delete();

        return ResponseFormatter::success([
        ], 'Proposal deleted successfully');
    }
}
