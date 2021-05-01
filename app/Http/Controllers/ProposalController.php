<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $proposals = Proposal::all();

        if ($request->only('category')) {
            $proposals = Proposal::where('category', $request->category);
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
                'status' => 'required|string',
                'file_path' => 'required|mimes:doc,docx,pdf'
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
                'category' => 'required|string',
                'description' => 'string',
                'status' => 'required|string',
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
                'category' => $request->category,
                'description' => $request->description,
                'status' => $request->status,
                'file_path' => $request->file('file_path') ? substr($image_url, 7) : $proposal->file_path,
            ]);

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
