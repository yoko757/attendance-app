<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
public function __construct()
{
    $this->middleware('auth')->except('login');

    $this->middleware(function ($request, $next) {
        abort_unless(auth()->user()->role === 'admin', 403);

        return $next($request);
    })->except('login');
}

public function attendanceList(Request $request)
{
$date = \Carbon\Carbon::parse($request->query('date', today()->toDateString()));

$previousDay = $date->copy()->subDay()->toDateString();

$nextDay = $date->copy()->addDay()->toDateString();

$attendances = AttendanceRecord::with('user', 'breakTimes')
    ->whereDate('date', $date->toDateString())
    ->get();
return view('admin.admin-attendance-list', compact('date', 'attendances', 'previousDay', 'nextDay'));
}
public function requestList()
{
$applications = AttendanceRequest::all();

return view('admin.admin-application-list', compact('applications'));
}
public function requestDetail($id)
{
$application = AttendanceRequest::findOrFail($id);

return view('admin.admin-application-detail', compact('application'));
}
public function approve($id)
{
    if (Auth::user()->role !== 'admin') {
    abort(403);
    }

DB::transaction(function () use ($id) {
$attendanceRequest = AttendanceRequest::findOrFail($id);

if ($attendanceRequest->status !== '承認待ち') {
    abort(403);
}

$attendanceRecord = $attendanceRequest->attendanceRecord;

if (
    !$attendanceRequest->new_clock_in ||
    !$attendanceRequest->new_clock_out ||
    $attendanceRequest->new_clock_in >= $attendanceRequest->new_clock_out
) {
    abort(422, '出勤・退勤時間が正しくありません');
}

foreach ($attendanceRequest->breaks as $break) {
    if (
        !$break->new_break_in ||
        !$break->new_break_out
    ) {
        abort(422, '休憩の開始・終了時間が入力されていません');
    }

if (
    $break->new_break_in < $attendanceRequest->new_clock_in ||
    $break->new_break_out > $attendanceRequest->new_clock_out ||
    $break->new_break_in >= $break->new_break_out
) {
    throw \Illuminate\Validation\ValidationException::withMessages([
    'approval' => '休憩時間が勤務時間の範囲外、または開始・終了が正しくありません',
]);
  }
}
$breaks = $attendanceRequest->breaks
    ->sortBy('new_break_in')
    ->values();

for ($i = 1; $i < $breaks->count(); $i++) {
    if ($breaks[$i]->new_break_in < $breaks[$i - 1]->new_break_out) {
        abort(422, '休憩時間が重複しています');
    }
}

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
});

return redirect('/admin/stamp_correction_request/list');
}


public function login(Request $request)
{
$credentials = $request->only('email', 'password');
if (Auth::attempt($credentials)) {
    $user = Auth::user();

if ($user->role === 'admin') {
    return redirect('/admin/stamp_correction_request/list');
   }
Auth::logout();
 }
return back()->withErrors([
    'email' => 'ログイン情報が登録されていません',
]);
}
public function attendanceDetail($id)
{
    $attendance = \App\Models\AttendanceRecord::with('user', 'breakTimes')->findOrFail($id);

    $user = $attendance->user;

    return view('admin.admin-detail', compact('attendance', 'user'));
}
public function attendanceUpdate(Request $request, $id)
{

$request->validate([
    'new_clock_in' => 'required|date_format:H:i',
    'new_clock_out' => 'required|date_format:H:i|after:new_clock_in',
    'new_break_in.*' => 'nullable|date_format:H:i|required_with:new_break_out.*',
    'new_break_out.*' => 'nullable|date_format:H:i|required_with:new_break_in.*',
    'new_break_in' => 'array',
    'new_break_out' => 'array',
]);

$newBreakIns = $request->input('new_break_in', []);

$newBreakOuts = $request->input('new_break_out', []);

foreach ($newBreakIns as $index => $newBreakIn) {

    $newBreakOut = $newBreakOuts[$index] ?? null;

    if ($newBreakIn && $newBreakOut && $newBreakOut <= $newBreakIn) {
    throw \Illuminate\Validation\ValidationException::withMessages([
        'new_break_out' => '休憩終了時間は開始時間より後にしてください。',
    ]);
}
if ($newBreakIn && $newBreakIn < $request->new_clock_in) {
    throw \Illuminate\Validation\ValidationException::withMessages([
        'new_break_in' => '休憩開始時間は出勤時間より後にしてください。',
    ]);
}
if ($newBreakOut && $newBreakOut > $request->new_clock_out) {
    throw \Illuminate\Validation\ValidationException::withMessages([
        'new_break_out' => '休憩終了時間は退勤時間以前にしてください。',
    ]);
}
}

    DB::beginTransaction();

    try {

    $attendance = \App\Models\AttendanceRecord::findOrFail($id);

    $attendance->clock_in = $request->new_clock_in;

    $attendance->clock_out = $request->new_clock_out;

    $attendance->comment = $request->input('comment');
    
    $attendance->save();

$attendance->breakTimes()->delete();
    foreach ($newBreakIns as $index => $newBreakIn) {
       $newBreakOut = $newBreakOuts[$index] ?? null;

       if (empty($newBreakIn) && empty($newBreakOut)) {
    continue;
}
$attendance->breakTimes()->create([
    'break_in' => $newBreakIn,
    'break_out' => $newBreakOut,
]);
}
DB::commit();

    return redirect('/admin/attendance/' . $id);
} catch (\Throwable $e) {
    DB::rollBack();
    throw $e;
}
}

 public function staffList()
    {
        $staff = \App\Models\User::where('role', 'user')
            ->orderBy('id')
            ->get();

        return view('admin.staff-list', compact('staff'));
    } 

public function staffAttendanceList(\Illuminate\Http\Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $date = \Carbon\Carbon::parse(
            $request->query('date', now()->format('Y-m'))
        )->startOfMonth();

        $previousMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        $records = \App\Models\AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->with('breakTimes')
            ->get()
            ->groupBy(fn ($record) => \Carbon\Carbon::parse($record->date)->format('Y-m-d'));

        $formattedAttendanceRecords = [];

        for ($day = $date->copy(); $day->month === $date->month; $day->addDay()) {
    $dayRecords = $records->get(
        $day->format('Y-m-d'),
        collect()
    );

    if ($dayRecords->isEmpty()) {
        $formattedAttendanceRecords[] = [
            'id' => null,
            'date' => $day->format('m/d') . '(' .
                ['日', '月', '火', '水', '木', '金', '土'][$day->dayOfWeek] . ')',
            'clock_in' => '',
            'clock_out' => '',
            'total_break_time' => '',
            'total_time' => '',
        ];

        continue;
    }

    foreach ($dayRecords as $record) {
        $formattedAttendanceRecords[] = [
            'id' => $record->id,
            'date' => $day->format('m/d') . '(' .
                ['日', '月', '火', '水', '木', '金', '土'][$day->dayOfWeek] . ')',
            'clock_in' => $record->clock_in
                ? substr($record->clock_in, 0, 5) : '',
            'clock_out' => $record->clock_out
                ? substr($record->clock_out, 0, 5) : '',
            'total_break_time' => sprintf(
                '%d:%02d',
                intdiv($record->total_break_time, 60),
                $record->total_break_time % 60
            ),
            'total_time' => $record->clock_out
                ? sprintf(
                    '%d:%02d',
                    intdiv($record->total_time, 60),
                    $record->total_time % 60
                ) : '',
        ];
    }
}

        return view('admin.staff-attendance-list', compact(
            'user', 'date', 'previousMonth', 'nextMonth', 'formattedAttendanceRecords'
        ));
    }
 public function export(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer|exists:users,id',
        'year_month' => 'required|date_format:Y-m',
    ]);

    $userId = $request->input('user_id');
    $month = \Carbon\Carbon::createFromFormat(
        '!Y-m',
        $request->input('year_month')
    );

    $records = \App\Models\AttendanceRecord::where('user_id', $userId)
        ->whereYear('date', $month->year)
        ->whereMonth('date', $month->month)
        ->orderBy('date')
        ->get();

    $filename = 'attendance_' . $userId . '_' . $month->format('Y-m') . '.csv';

    return response()->streamDownload(function () use ($records) {
        $output = fopen('php://output', 'w');

        // Excelで日本語が文字化けしにくいようにする
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, ['日付', '出勤', '退勤', '休憩', '合計']);

        foreach ($records as $record) {
            $breakMinutes = $record->total_break_time;
            $workMinutes = $record->clock_out ? $record->total_time : null;

            fputcsv($output, [
                $record->date,
                $record->clock_in,
                $record->clock_out,
                sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60),
                $workMinutes !== null
                    ? sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60)
                    : '',
            ]);
        }

        fclose($output);
    }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
}
}