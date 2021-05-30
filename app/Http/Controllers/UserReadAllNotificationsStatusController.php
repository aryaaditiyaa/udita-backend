<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\UserReadAllNotificationsStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserReadAllNotificationsStatusController extends Controller
{
    public function index()
    {
        $user_read_all_notification_status = UserReadAllNotificationsStatus::where('user_id', auth()->user()->id)
            ->first();

        if ($user_read_all_notification_status != null) {
            return ResponseFormatter::success([
                'user_read_all_notification_status' => $user_read_all_notification_status
            ], 'Fetch successful');
        } else {
            return ResponseFormatter::error([
            ], 'Belum ada notifikasi', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStatus()
    {
        $user_read_all_notification_status = UserReadAllNotificationsStatus::where('user_id', auth()->user()->id)
            ->updateOrCreate(
                ['user_id' => auth()->user()->id],
                ['is_read' => 1]
            );

        return ResponseFormatter::success([
            'user_read_all_notification_status' => $user_read_all_notification_status
        ], 'Update successful');
    }
}
