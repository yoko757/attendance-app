<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = \App\Models\AttendanceRecord::query();

    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }

    if ($request->filled('date')) {
        $query->whereDate('date', $request->date);
    }

    if ($request->filled('month')) {
        $query->where('date', 'like', $request->month . '%');
    }

    $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

    return response()->json(
        $query->orderBy('date', 'desc')->paginate($perPage)
    );
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
    'user_id' => ['required', 'integer', 'exists:users,id'],
    'date' => ['required', 'date_format:Y-m-d'],
    'clock_in' => ['required', 'date_format:H:i:s'],
    'clock_out' => ['nullable', 'date_format:H:i:s', 'after:clock_in'],
    'comment' => ['nullable', 'string', 'max:1000'],
], [
    'required' => ':attributeは必須です。',
    'integer' => ':attributeは整数で入力してください。',
    'exists' => '指定された:attributeは存在しません。',
    'date_format' => ':attributeの形式が正しくありません。',
    'after' => ':attributeは出勤時間より後にしてください。',
    'string' => ':attributeは文字列で入力してください。',
    'max' => ':attributeは1000文字以内で入力してください。',
], [
    'user_id' => 'ユーザーID',
    'date' => '日付',
    'clock_in' => '出勤時間',
    'clock_out' => '退勤時間',
    'comment' => '備考',
]);
    if (
        $request->user()->role !== 'admin' &&
        (int) $validated['user_id'] !== (int) $request->user()->id
) {
    abort(403, '他の利用者の勤怠記録は登録できません。');
}
    $attendance = \App\Models\AttendanceRecord::create($validated);

    return response()->json($attendance, 201);
}

    /**
     * Display the specified resource.
     */
   public function show(string $attendanceRecord)
{
    $attendance = \App\Models\AttendanceRecord::with([
        'user',
        'breakTimes',
        'attendanceRequests',
    ])->findOrFail($attendanceRecord);

    return response()->json($attendance);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $attendance = \App\Models\AttendanceRecord::findOrFail($id);

    // 本人または管理者だけが更新できる
    $user = $request->user();

    if (
        (int) $attendance->user_id !== (int) $user->id &&
        $user->role !== 'admin'
    ) {
        abort(403, 'この勤怠記録を変更する権限がありません。');
    }

    $validated = $request->validate([
        'date' => ['sometimes', 'required', 'date_format:Y-m-d'],
        'clock_in' => ['sometimes', 'required', 'date_format:H:i:s'],
        'clock_out' => ['sometimes', 'nullable', 'date_format:H:i:s'],
        'comment' => ['sometimes', 'nullable', 'string'],
    ]);

    $attendance->update($validated);

    return response()->json($attendance->fresh(), 200);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
    $attendance = \App\Models\AttendanceRecord::findOrFail($id);

    $user = request()->user();

    // 本人または管理者だけ削除できる
    if (
        (int) $attendance->user_id !== (int) $user->id &&
        $user->role !== 'admin'
    ) {
        return response()->json([
            'message' => 'この勤怠記録を削除する権限がありません。'
        ], 403);
    }

    $attendance->delete();

    return response()->noContent();
}
}
