<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckpointController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskFlowController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['name' => config('app.name'), 'version' => '1.0']));

Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('login-otp', [AuthController::class, 'loginOtp']);
    Route::post('login-password', [AuthController::class, 'loginPassword']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('check-login', [AuthController::class, 'checkLogin']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::prefix('v1/task-management')->group(function () {
        Route::post('tasks/open', [TaskController::class, 'open']);
        Route::get('tasks/{id}', [TaskController::class, 'show']);
        Route::get('tasks/{id}/history', [TaskController::class, 'history']);
        Route::get('checkpoints/{id}', [CheckpointController::class, 'show']);
        Route::post('checkpoints/{id}/apply', [CheckpointController::class, 'apply']);
        Route::post('checkpoints/{id}/assign', [CheckpointController::class, 'assign']);
        Route::post('checkpoints/{id}/claim', [CheckpointController::class, 'claim']);
        Route::post('checkpoints/{id}/start', [CheckpointController::class, 'start']);
    });

    Route::prefix('v1/task-flow')->group(function () {
        Route::get('stages-stats', [TaskFlowController::class, 'getStagesStats']);
        Route::get('sub-stats', [TaskFlowController::class, 'getSubStats']);
        Route::get('collection', [TaskFlowController::class, 'getTaskCollection']);
    });
});
