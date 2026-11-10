<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;

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

    $attendances = $query->orderBy('date', 'desc')->paginate($perPage);
    
    return response()->json([
    'data' => AttendanceRecordResource::collection($attendances->items()),
    'meta' => [
        'current_page' => $attendances->currentPage(),
        'last_page' => $attendances->lastPage(),
        'per_page' => $attendances->perPage(),
        'total' => $attendances->total(),
    ],
]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRecordRequest $request)
{
    $validated = $request->validated();
    $validated['user_id'] = $request->user()->id;
    
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
    ])->find($attendanceRecord);
if (!$attendance) {
    return response()->json([
        'error' => '勤怠情報が見つかりませんでした。',
    ], 404);
}
    return new AttendanceRecordResource($attendance);
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
    $attendance = \App\Models\AttendanceRecord::find($id);

if (!$attendance) {
    return response()->json([
        'message' => '勤怠記録が見つかりません。'
    ], 404);
}

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
