<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\LogRequests;
use App\Http\Services\LogService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([LogRequests::class])->group(function () {
    Route::resource('tasks', TaskController::class)->missing(function (Request $request) {
        $logService = new LogService();
        return $logService->logForMissingModelRequests($request);
    });
});
