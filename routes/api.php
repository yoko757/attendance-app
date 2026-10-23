<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/attendance-records', [
        \App\Http\Controllers\Api\V1\AttendanceRecordController::class,
        'index'
    ]);
Route::post('/attendance-records', [
    \App\Http\Controllers\Api\V1\AttendanceRecordController::class,
    'store'
])->middleware('auth:sanctum');

 Route::get('/attendance-records/{attendanceRecord}', [
    \App\Http\Controllers\Api\V1\AttendanceRecordController::class,
    'show'
]);
Route::match(['put', 'patch'], '/attendance-records/{attendanceRecord}', [
    \App\Http\Controllers\Api\V1\AttendanceRecordController::class,
    'update'
])->middleware('auth:sanctum');

Route::delete('/attendance-records/{attendanceRecord}', [
    \App\Http\Controllers\Api\V1\AttendanceRecordController::class,
    'destroy'
])->middleware('auth:sanctum');
});