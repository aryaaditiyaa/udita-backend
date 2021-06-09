<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Activities;
use App\Models\Notification;
use App\Models\Proposal;
use App\Models\StudentActivityUnit;
use App\Models\User;
use App\Models\UserReadAllNotificationsStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $proposals = Proposal::latest()->limit(50)->get();

        if ($request->user_id != null) {
            $proposals = Proposal::where('user_id', $request->user_id)
                ->latest()
                ->limit(50)
                ->get();
        }

        if ($request->keyword != null && auth()->user()->role == 'admin') {
            $proposals = Proposal::where('title', 'LIKE', '%' . $request->keyword . '%')
                ->latest()
                ->get();
        } else if ($request->keyword != null && auth()->user()->role != 'admin') {
            $proposals = Proposal::where('title', 'LIKE', '%' . $request->keyword . '%')
                ->where('user_id', auth()->user()->id)
                ->latest()
                ->get();
        }

        return ResponseFormatter::success([
            'proposal' => $proposals
        ], 'Proposal fetch successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'category' => 'required|string',
            'description' => 'required|string',
            'file_path' => 'mimes:doc,docx,pdf|size:10240'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error([
                'error' => $validator->errors()
            ], 'Ups, file yang kamu unggah lebih dari 10 MB', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($request->file('file_path')) {
            $image_url = Storage::putFile(
                'public/proposal/' . date('FY'),
                $request->file('file_path')
            );
        }

        if ($request->category == 'new-sau') {
            if (auth()->user()->student_activity_unit_id == null) {
                if (StudentActivityUnit::where('name', $request->title)->first() != null) {
                    return ResponseFormatter::error([
                    ], 'Ups, UKM dengan nama ' . $request->title . ' sudah ada, silahkan coba nama lain', Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    $proposal = Proposal::create([
                        'user_id' => auth()->user()->id,
                        'student_activity_unit_id' => auth()->user()->student_activity_unit_id == null ? null : auth()->user()->student_activity_unit_id,
                        'title' => $request->title,
                        'category' => $request->category,
                        'description' => $request->description,
                        'status' => 'pending',
                        'file_path' => $request->file('file_path') ? substr($image_url, 7) : null,
                    ]);

                    if ($proposal != null) {
                        Notification::create([
                            'user_id' => User::where('role', 'admin')->value('id'),
                            'student_activity_unit_id' => null,
                            'category' => 'proposal',
                            'title' => 'Ada proposal pembentukan ' . $proposal->title,
                            'target' => 'admin'
                        ]);

                        UserReadAllNotificationsStatus::updateOrCreate(
                            ['user_id' => User::where('role', 'admin')->value('id')],
                            ['is_read' => 0]
                        );
                    }

                    return ResponseFormatter::success([
                        'proposal' => $proposal
                    ], 'Proposal added successfully');
                }
            } else {
                return ResponseFormatter::error([
                ], 'Ups, kamu telah tergabung di UKM', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else if ($request->category == 'event') {
            $proposal = Proposal::create([
                'user_id' => auth()->user()->id,
                'student_activity_unit_id' => auth()->user()->student_activity_unit_id == null ? null : auth()->user()->student_activity_unit_id,
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'status' => 'pending',
                'file_path' => $request->file('file_path') ? substr($image_url, 7) : null,
            ]);

            if ($proposal != null) {
                Activities::create([
                    'status' => 'pending',
                    'proposal_id' => $proposal->id,
                    'category' => 'event',
                    'name' => $proposal->title,
                    'description' => $proposal->description,
                    'student_activity_unit_id' => auth()->user()->student_activity_unit_id == null ? null : auth()->user()->student_activity_unit_id,
                    'started_at' => $request->started_at,
                    'ended_at' => $request->ended_at,
                ]);

                Notification::create([
                    'user_id' => User::where('role', 'admin')->value('id'),
                    'student_activity_unit_id' => null,
                    'category' => 'proposal',
                    'title' => 'Ada proposal acara ' . $proposal->title,
                    'target' => 'admin'
                ]);

                UserReadAllNotificationsStatus::updateOrCreate(
                    ['user_id' => User::where('role', 'admin')->value('id')],
                    ['is_read' => 0]
                );
            }

            return ResponseFormatter::success([
                'proposal' => $proposal
            ], 'Proposal added successfully');
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
        $validator = Validator::make($request->all(), [
            'category' => 'sometimes|string',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string',
            'file_path' => 'sometimes|mimes:doc,docx,pdf|size:10240'
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

        if ($request->status == 'approved') {
            $status = 'approved';
            $title = "Proposal" . $proposal->title . " yang kamu ajukan diterima";

            if ($proposal->category == 'new-sau') {
                $isUserHasSau = User::where('id', $proposal->user_id)->value('student_activity_unit_id');
                if ($isUserHasSau == null) {
                    $sau = StudentActivityUnit::create([
                        'name' => $proposal->title,
                        'description' => $proposal->description,
                        'status' => 'active',
                        'proposal_id' => $proposal->id
                    ]);

                    User::whereId($proposal->user_id)->update([
                        'student_activity_unit_id' => $sau->id,
                        'role' => 'leader'
                    ]);
                } else {
                    return ResponseFormatter::error([
                    ], 'Ups, pengguna ini telah memiliki UKM', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } else if ($request->status == 'declined') {
            $status = 'declined';
            $title = "Proposal " . $proposal->title . " yang kamu ajukan ditolak";
        }

        if ($proposal->category == 'event') {
            Activities::where('category', 'event')->where('proposal_id', $proposal->id)->update([
                'status' => $status
            ]);

            $userGetNotificationList = User::where('student_activity_unit_id', $proposal->student_activity_unit_id)
                ->pluck('id')
                ->toArray();

            foreach ($userGetNotificationList as $value) {
                Notification::create([
                    'user_id' => $value,
                    'student_activity_unit_id' => $proposal->student_activity_unit_id,
                    'category' => $proposal->category,
                    'title' => 'Ada acara ' . $proposal->title . '. Yuk, ikutan!',
                    'description' => null,
                    'target' => 'user_id'
                ]);

                UserReadAllNotificationsStatus::updateOrCreate(
                    ['user_id' => $value],
                    ['is_read' => 0]
                );
            }
        }

        $proposal->update([
            'title' => $request->title ? $request->title : $proposal->title,
            'category' => $request->category ? $request->category : $proposal->category,
            'description' => $request->description ? $request->description : $proposal->description,
            'status' => $status ? $status : $proposal->status,
            'file_path' => $request->file('file_path') ? substr($image_url, 7) : $proposal->file_path,
        ]);

        Notification::create([
            'user_id' => $proposal->user_id,
            'student_activity_unit_id' => null,
            'category' => 'proposal',
            'title' => $title,
            'target' => 'user_id'
        ]);

        UserReadAllNotificationsStatus::updateOrCreate(
            ['user_id' => $proposal->user_id],
            ['is_read' => 0]
        );

        return ResponseFormatter::success([
            'proposal' => $proposal
        ], 'Proposal updated successfully');
    }

    public function destroy($id)
    {
        Proposal::findOrFail($id)->delete();

        return ResponseFormatter::success([
        ], 'Proposal deleted successfully');
    }
}
