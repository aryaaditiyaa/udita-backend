<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Activities;
use App\Models\Attendance;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserReadAllNotificationsStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user_id) {
            $attendance = Attendance::where('user_id', $request->user_id)
                ->latest()
                ->get();
        } else if ($request->activity_id) {
            $attendance = Attendance::where('activity_id', $request->activity_id)
                ->latest()
                ->get();
        }

        return ResponseFormatter::success([
            'attendance' => $attendance
        ], 'Attendance fetch successfully');
    }

    public function store(Request $request)
    {
        if ($request->activity_id) {
            $activity = Activities::findOrFail($request->activity_id);

            if ($activity) {
                $userGetNotificationList = User::where('student_activity_unit_id', $activity->student_activity_unit_id)
                    ->pluck('id')
                    ->toArray();

                $attendance = Attendance::where('activity_id', $activity->id)->value('activity_id');

                if ($attendance != null) {
                    return ResponseFormatter::error([
                    ], 'Ups, absensi untuk kegiatan ini sudah ada', Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    foreach ($userGetNotificationList as $value) {
                        Attendance::create([
                            'user_id' => $value,
                            'activity_id' => $activity->id,
                            'is_attended' => 0,
                        ]);

                        Notification::create([
                            'user_id' => $value,
                            'student_activity_unit_id' => $activity->student_activity_unit_id,
                            'category' => 'attendance',
                            'title' => 'Jangan lupa mengisi absensi untuk ' . $activity->name,
                            'description' => null,
                            'target' => 'user_id'
                        ]);

                        UserReadAllNotificationsStatus::updateOrCreate(
                            ['user_id' => $value],
                            ['is_read' => 0]
                        );
                    }
                }
            }
        }

        return ResponseFormatter::success([
            'attendance' => 'ok'
        ], 'Attendance created successfully');
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        if ($attendance->is_attended == 0) {
            $attendance->update([
                'is_attended' => $request->is_attended
            ]);
        } else {
            return ResponseFormatter::error([
                'attendance' => 'fail'
            ], 'Ups, kamu telah absen', Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return ResponseFormatter::success([
            'attendance' => 'ok'
        ], 'Attendance updated successfully');
    }
}
