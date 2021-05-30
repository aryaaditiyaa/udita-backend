<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Notification;
use App\Models\SauMemberRegistration;
use App\Models\User;
use App\Models\UserReadAllNotificationsStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SauMemberRegistrationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->student_activity_unit_id != null && $request->user_id != null) {
            $smr = SauMemberRegistration::where('student_activity_unit_id', $request->student_activity_unit_id)
                ->where('user_id', $request->user_id)
                ->latest()
                ->get();
        } else if ($request->student_activity_unit_id != null) {
            $smr = SauMemberRegistration::where('student_activity_unit_id', $request->student_activity_unit_id)
                ->latest()
                ->get();
        } else if ($request->user_id != null) {
            $smr = SauMemberRegistration::where('user_id', $request->user_id)
                ->latest()
                ->get();
        }

        return ResponseFormatter::success([
            'smr' => $smr
        ], 'Fetch successful');
    }

    public function store(Request $request)
    {
        if (auth()->user()->student_activity_unit_id == null) {
            $smr = SauMemberRegistration::create([
                'user_id' => auth()->user()->id,
                'student_activity_unit_id' => $request->student_activity_unit_id,
                'status' => 'pending'
            ]);

            Notification::create([
                'user_id' => User::where('student_activity_unit_id', $request->student_activity_unit_id)
                    ->where('role', 'leader')
                    ->value('id'),
                'student_activity_unit_id' => $smr->student_activity_unit_id,
                'category' => 'smr-new',
                'title' => 'Ada pengajuan permintaan gabung UKM baru',
                'target' => 'leader'
            ]);

            UserReadAllNotificationsStatus::updateOrCreate(
                ['user_id' => User::where('student_activity_unit_id', $request->student_activity_unit_id)
                    ->where('role', 'leader')
                    ->value('id')],
                ['is_read' => 0]
            );

            return ResponseFormatter::success([
            ], 'Join Sau Request Successful');
        } else {
            return ResponseFormatter::error([
            ], 'Ups, kamu sudah tergabung di salah satu UKM', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $smr = SauMemberRegistration::findOrFail($id);

        if ($smr->status == 'pending'){
            if (User::where('id', $smr->user_id)->value('student_activity_unit_id') == null){
                $request->validate([
                    'status' => 'required'
                ]);

                $smr->update([
                    'status' => $request->status
                ]);

                if ($request->status == 'approved') {
                    $title = 'Kamu diterima untuk masuk ke ' . $smr->student_activity_unit_name;
                    User::where('id', $smr->user_id)->update([
                        'student_activity_unit_id' => $smr->student_activity_unit_id,
                        'role' => 'member'
                    ]);
                } else if ($request->status == 'declined') {
                    $title = 'Kamu ditolak untuk masuk ke ' . $smr->student_activity_unit_name;
                }

                Notification::create([
                    'user_id' => $smr->user_id,
                    'student_activity_unit_id' => null,
                    'category' => 'smr-status',
                    'title' => $title,
                    'target' => 'user_id'
                ]);

                UserReadAllNotificationsStatus::updateOrCreate(
                    ['user_id' => $smr->user_id],
                    ['is_read' => 0]
                );

                return ResponseFormatter::success([
                ], 'Success');
            } else {
                return ResponseFormatter::error([
                ], 'Ups, pengguna ini sudah tergabung di salah satu UKM', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return ResponseFormatter::error([
            ], 'Ups, tidak dapat mengubah data yang telah memiliki status', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
