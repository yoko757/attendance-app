<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login' , function() {
    return view('user.user-login');
})->name('login');

Route::get('/register' , function() {
    return view('user.register');
});

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

Route::post('/attendance', [AttendanceController::class, 'store'])
    ->middleware('auth');

Route::get('/attendance/list', [AttendanceController::class, 'list'])
    ->middleware('auth');
    
Route::get('/attendance/report', [AttendanceController::class, 'report'])
    ->middleware(['auth', 'verified']);

Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])
    ->middleware('auth');

Route::post('/attendance/{id}', [AttendanceController::class, 'update'])
    ->middleware('auth');

Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])
    ->middleware('auth');

Route::get('/application/{id}', [AttendanceController::class, 'requestDetail'])
    ->middleware('auth');

Route::post('/stamp_correction_request/approve/{id}', [AdminController::class, 'approve'])
    ->middleware('auth');

Route::get('/admin/stamp_correction_request/list', [AdminController::class, 'requestList'])
    ->middleware('auth');

Route::get('/admin/attendance/list', [AdminController::class, 'attendanceList'])
    ->middleware('auth');

Route::get('/admin/attendance/{id}', [AdminController::class, 'attendanceDetail'])
    ->middleware('auth');

Route::post('/admin/attendance/{id}', [AdminController::class, 'attendanceUpdate'])
    ->middleware('auth');

Route::get('/admin/stamp_correction_request/{id}', [AdminController::class, 'requestDetail'])
    ->middleware('auth');

Route::get('/admin/staff/list', [AdminController::class, 'staffList'])
    ->middleware('auth');

Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'staffAttendanceList'])
    ->middleware('auth');

Route::post('/admin/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/admin/login');
})->middleware('auth');   

Route::post('/export', [AdminController::class, 'export'])
    ->middleware('auth');

Route::get('/admin/login', function () {
    return view('admin.admin-login');
});

Route::post('/admin/login', [AdminController::class, 'login']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/attendance');
})->middleware(['auth', 'signed', 'throttle:6,1'])
  ->name('verification.verify');

Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])
  ->name('verification.send');

Route::get('/attendance/report', [
    AttendanceController::class,
    'report'
])->middleware(['auth', 'verified']);

