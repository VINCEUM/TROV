<?php

use App\Http\Controllers\Api\AgentAuthController;
use App\Http\Controllers\Api\CaptureController;
use Illuminate\Support\Facades\Route;

// Desktop monitoring component API.
Route::post('/agent/login', [AgentAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/agent/logout', [AgentAuthController::class, 'logout']);
    Route::get('/session/current', [CaptureController::class, 'current']);
    Route::post('/captures', [CaptureController::class, 'store']);
});
