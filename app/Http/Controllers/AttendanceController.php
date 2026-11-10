<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;

class AttendanceController extends Controller
{
 public function index()
{
    $user = auth()->user();

    // 今日の勤怠データを探す
    $todayAttendance = AttendanceRecord::where('user_id', $user->id)
        ->where('date', now()->toDateString())
        ->first();

    // 今日の勤怠データがなければ「勤務外」に戻す
    if (!$todayAttendance) {
        $user->update([
            'attendance_status' => '勤務外'
        ]);
    }

    $formattedDate = now()->format('Y年n月j日');
    $formattedTime = now()->format('H:i');

    return view(
        'user.attendance-register',
        compact('user', 'formattedDate', 'formattedTime')
    );
}
public function store(Request $request)
{
 $user = auth()->user();
 
if ($request->action === 'clock_in') {
AttendanceRecord::create([
    'user_id' => $user->id,
    'date' => now()->toDateString(),
    'clock_in' => now()->format('H:i:s'),
]);
$user->update(['attendance_status' => '出勤中']);

} 
if ($request->action === 'break_in') {

    $attendance = AttendanceRecord::where('user_id', $user->id)
    ->where('date', now()->toDateString())
    ->first();

if ($attendance) {
    BreakTime::create([
        'attendance_record_id' => $attendance->id,
        'break_in' => now()->format('H:i:s'),
    ]);
}    
    $user->update(['attendance_status' => '休憩中']);
}
if ($request->action === 'break_out') {
    
    $attendance = AttendanceRecord::where('user_id', $user->id)
        ->where('date', now()->toDateString())
        ->first();
    
    $breakTime = BreakTime::where('attendance_record_id', $attendance->id)
        ->whereNull('break_out')
        ->latest()
        ->first();

     if ($breakTime) {
    $breakTime->update([
        'break_out' => now()->format('H:i:s'),
    ]);
}   
    $user->update(['attendance_status' => '出勤中']);
}
if ($request->action === 'clock_out') {

$attendance = AttendanceRecord::where('user_id', $user->id)
        ->where('date', now()->toDateString())
        ->first();

if ($attendance) {
        $attendance->update(['clock_out' => now()->format('H:i:s')]);
    }

    $user->update(['attendance_status' => '退勤済']);
}

return redirect('/attendance');

}
public function list(Request $request)
{
    $user = auth()->user();
    $date = $request->date ? \Carbon\Carbon::parse($request->date) : now();

    $previousMonth = $date->copy()->subMonth()->format('Y-m');
    $nextMonth = $date->copy()->addMonth()->format('Y-m');
    $attendanceRecords = AttendanceRecord::where('user_id', $user->id)
    ->whereYear('date', $date->year)
    ->whereMonth('date', $date->month)
    ->get();

    $formattedAttendanceRecords = [];

    foreach ($attendanceRecords as $attendanceRecord) {

    $breakMinutes = 0;

    foreach ($attendanceRecord->breakTimes as $breakTime) {

     if ($breakTime->break_out) {

     $breakIn = \Carbon\Carbon::parse($breakTime->break_in);
     $breakOut = \Carbon\Carbon::parse($breakTime->break_out);
     
     $breakMinutes += $breakIn->diffInMinutes($breakOut);
    
    }

}
     $breakHours = floor($breakMinutes / 60);
     $remainingBreakMinutes = $breakMinutes % 60;
     $totalBreakTime = sprintf('%d:%02d', $breakHours, $remainingBreakMinutes);

     $workTime = '-';

     if ($attendanceRecord->clock_in && $attendanceRecord->clock_out) {
$clockIn = \Carbon\Carbon::parse($attendanceRecord->clock_in);
$clockOut = \Carbon\Carbon::parse($attendanceRecord->clock_out);
$workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
$workHours = floor($workMinutes / 60);
$remainingWorkMinutes = $workMinutes % 60;
$workTime = sprintf('%d:%02d', $workHours, $remainingWorkMinutes);

}
$formattedAttendanceRecords[] = [
    'id' => $attendanceRecord->id,
    'date' => $attendanceRecord->date,
    'clock_in' => $attendanceRecord->clock_in,
    'clock_out' => $attendanceRecord->clock_out,
    'break_time' => $totalBreakTime,
    'work_time' => $workTime,
];

}
return view('user.user-attendance-list', compact(
    'date',
    'previousMonth',
    'nextMonth',
    'formattedAttendanceRecords'
));
}
public function detail($id)
{

$data = AttendanceRecord::findOrFail($id);

$data['year'] = \Carbon\Carbon::parse($data->date)->format('Y年');

$data['date'] = \Carbon\Carbon::parse($data->date)->format('m月d日');

$user = $data->user;

$data['breaks'] = $data->breakTimes;

$attendanceRequest = AttendanceRequest::where('attendance_record_id', $id)
    ->where('status', '承認待ち')
    ->latest()
    ->first();

return view('user.user-detail', compact('data', 'user', 'attendanceRequest'));
}
public function update(UpdateAttendanceRequest $request, $id)
{

   $attendance = AttendanceRecord::findOrFail($id);

   $user = auth()->user();

   $attendanceRequest = AttendanceRequest::create([
    'attendance_record_id' => $attendance->id,
    'user_id' => $user->id,
    'new_clock_in' => $request->new_clock_in,
    'new_clock_out' => $request->new_clock_out,
    'comment' => $request->comment,
    ]);

    $newBreakIns = $request->new_break_in ?? [];
    $newBreakOuts = $request->new_break_out ?? [];

    foreach ($newBreakIns as $index => $newBreakIn) {
        if (empty($newBreakIn) && empty($newBreakOuts[$index])) continue;
        $newBreakOut = $newBreakOuts[$index] ?? null;
        
        $attendanceRequest->breaks()->create([
            'new_break_in' => $newBreakIn,
            'new_break_out' => $newBreakOut,
            ]);
}
return redirect('/attendance/' . $id);
}
public function requestList()
{
    $user = auth()->user();

    $requests = AttendanceRequest::where('user_id', auth()->id())
        ->get();

    $formattedApplications = $requests->map(function ($request) {
    return [
    'id' => $request->id,
    'approval_status' => $request->status,
    'date' => $request->attendanceRecord->date,
    'comment' => $request->comment,
    'application_date' => $request->created_at->format('Y/m/d H:i'),
];
});

    return view('user.user-application-list', compact('formattedApplications', 'user'));
}
public function requestDetail($id)
{
    $attendanceRequest = AttendanceRequest::findOrFail($id);
    $attendanceRecord = $attendanceRequest->attendanceRecord;
    $data = [
        'id' => $attendanceRecord->id,
        'year' => date('Y年', strtotime($attendanceRecord->date)),
        'date' => date('m月d日', strtotime($attendanceRecord->date)),
        'clock_in' => $attendanceRequest->new_clock_in,
        'clock_out' => $attendanceRequest->new_clock_out,
        'breaks' => $attendanceRequest->breaks,
        'comment' => $attendanceRequest->comment,
        ];
        $user = $attendanceRequest->user;
    return view('user.user-detail', compact('attendanceRequest', 'attendanceRecord', 'data', 'user'));
}
public function approve($id)
{
$attendanceRequest = AttendanceRequest::findOrFail($id);

$attendanceRecord = $attendanceRequest->attendanceRecord;

$attendanceRecord->clock_in = $attendanceRequest->new_clock_in;
$attendanceRecord->clock_out = $attendanceRequest->new_clock_out;

$attendanceRecord->save();
$attendanceRecord->breakTimes()->delete();

foreach ($attendanceRequest->breaks as $break) {
$attendanceRecord->breakTimes()->create([
    'break_in' => $break->new_break_in,
    'break_out' => $break->new_break_out,
]);
}
$attendanceRequest->status = '承認済み';
$attendanceRequest->save();
return redirect('/admin/stamp_correction_request/list');

}

 public function report()
    {
        $attendances = AttendanceRecord::with('breakTimes')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [
                now()->startOfMonth()->subMonths(5)->toDateString(),
                now()->endOfMonth()->toDateString()
            ])
            ->orderBy('date')
            ->get();

        return view('user.attendance-report', compact('attendances'));
    }
}