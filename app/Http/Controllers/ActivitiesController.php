<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Activities;
use App\Models\Attendance;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserReadAllNotificationsStatus;
use Illuminate\Http\Request;

class ActivitiesController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activities::where('student_activity_unit_id', $request->student_activity_unit_id)
            ->where('category', $request->category)
            ->latest()
            ->get();

        if ($request->status) {
            $activities = Activities::where('student_activity_unit_id', $request->student_activity_unit_id)
                ->where('category', $request->category)
                ->where('status', $request->status)
                ->latest()
                ->get();
        }

        return ResponseFormatter::success([
            'activities' => $activities
        ], 'Activities fetch successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'started_at' => 'required|date',
            'ended_at' => 'required|date'
        ]);

        $activities = Activities::create([
            'student_activity_unit_id' => auth()->user()->student_activity_unit_id,
            'proposal_id' => null,
            'documentation_id' => null,
            'status' => 'approved',
            'category' => $request->category,
            'name' => $request->name,
            'description' => $request->description,
            'started_at' => $request->started_at,
            'ended_at' => $request->ended_at
        ]);

        if ($activities != null) {
            $userGetNotificationList = User::where('student_activity_unit_id', $activities->student_activity_unit_id)
                ->pluck('id')
                ->toArray();

            foreach ($userGetNotificationList as $value) {
                Attendance::create([
                    'user_id' => $value,
                    'activity_id' => $activities->id,
                    'is_attended' => 0,
                ]);

                Notification::create([
                    'user_id' => $value,
                    'student_activity_unit_id' => $activities->student_activity_unit_id,
                    'category' => 'attendance',
                    'title' => 'Jangan lupa mengisi absensi untuk ' . $activities->name,
                    'description' => null,
                    'target' => 'user_id'
                ]);

                UserReadAllNotificationsStatus::updateOrCreate(
                    ['user_id' => $value],
                    ['is_read' => 0]
                );
            }
        }

        return ResponseFormatter::success([
            'activities' => $activities
        ], 'Activities stored successfully');
    }

    public function update(Request $request, $id)
    {
        $activities = Activities::findOrFail($id);

        $activities->update([
            'status' => $request->status ? $request->status : $activities->status,
            'name' => $request->name ? $request->name : $activities->name,
            'description' => $request->description ? $request->description : $activities->description,
            'started_at' => $request->started_at ? $request->started_at : $activities->started_at,
            'ended_at' => $request->ended_at ? $request->ended_at : $activities->ended_at,
        ]);

        if ($request->status == 'canceled') {
            $categoryTitle = $activities->category == 'event' ? 'Acara' : 'Kegiatan Rutin';

            $userGetNotificationList = User::where('student_activity_unit_id', auth()->user()->student_activity_unit_id)
                ->pluck('id')
                ->toArray();

            foreach ($userGetNotificationList as $value) {
                Notification::create([
                    'user_id' => $value,
                    'student_activity_unit_id' => auth()->user()->student_activity_unit_id,
                    'category' => $activities->category,
                    'title' => $categoryTitle . ' ' . $activities->name . ' telah dibatalkan',
                    'description' => null,
                    'target' => 'user_id'
                ]);

                UserReadAllNotificationsStatus::updateOrCreate(
                    ['user_id' => $value],
                    ['is_read' => 0]
                );
            }
        }

        return ResponseFormatter::success([
            'activities' => $activities
        ], 'Activities updated successfully');
    }
}
