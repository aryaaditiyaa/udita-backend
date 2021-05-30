<?php

use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SauMemberRegistrationController;
use App\Http\Controllers\StudentActivityUnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserReadAllNotificationsStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

Route::apiResource('user', UserController::class)->only([
    'index'
]);

Route::get('news', [NewsController::class, 'index']);
Route::get('news/{id}', [NewsController::class, 'show']);

Route::apiResource('student_activity_unit', StudentActivityUnitController::class)->only([
    'index', 'show'
]);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);

    Route::apiResource('user', UserController::class)->only([
        'show', 'update'
    ]);

    Route::apiResource('news', NewsController::class)->only([
        'store', 'update', 'destroy'
    ]);

    Route::apiResource('proposal', ProposalController::class);

    Route::apiResource('sau_member_registration', SauMemberRegistrationController::class)->only([
        'index', 'store', 'update'
    ]);

    Route::apiResource('notification', NotificationController::class)->only([
        'index', 'store', 'destroy'
    ]);

    Route::apiResource('student_activity_unit', StudentActivityUnitController::class)->only([
        'update'
    ]);

    Route::apiResource('user_read_notif_status', UserReadAllNotificationsStatusController::class)->only([
        'index'
    ]);

    Route::patch('user_read_notif_status', [UserReadAllNotificationsStatusController::class, 'updateStatus']);

});
