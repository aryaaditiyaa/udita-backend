<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserReadAllNotificationsStatus;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        if ($request->target == 'admin') {
            $notification = Notification::where('target', 'admin')
                ->latest()
                ->limit(50)
                ->get();
        } else if ($request->target == 'leader') {
            if ($request->category == 'announcement') {
                $notification = Notification::where('user_id', auth()->user()->id)
                    ->where('category', 'announcement')
                    ->latest()
                    ->limit(50)
                    ->get();
            } else {
                $notification = Notification::where('user_id', auth()->user()->id)
                    ->latest()
                    ->limit(50)
                    ->get();
            }
        } else {
            $notification = Notification::where('target', 'user_id')
                ->where('user_id', auth()->user()->id)
                ->latest()
                ->limit(50)
                ->get();
        }

        return ResponseFormatter::success([
            'notification' => $notification
        ], 'Fetch all successful');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string'
        ]);

        $userGetNotificationList = User::where('student_activity_unit_id', auth()->user()->student_activity_unit_id)
            ->pluck('id')
            ->toArray();

        foreach ($userGetNotificationList as $value) {
            Notification::create([
                'user_id' => $value,
                'student_activity_unit_id' => auth()->user()->student_activity_unit_id,
                'category' => 'announcement',
                'title' => $request->title,
                'description' => $request->description,
                'target' => 'user_id'
            ]);

            UserReadAllNotificationsStatus::updateOrCreate(
                ['user_id' => $value],
                ['is_read' => 0]
            );
        }

        return ResponseFormatter::success([
            'announcement' => 'ok'
        ], 'Store successful');
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);

        Notification::where('student_activity_unit_id', $notification->student_activity_unit_id)
            ->where('category', $notification->category)
            ->where('title', $notification->title)
            ->where('description', $notification->description)
            ->where('created_at', $notification->created_at)
            ->delete();

        return ResponseFormatter::success([
            'notification' => 'ok'
        ], 'Delete successful');
    }
}
