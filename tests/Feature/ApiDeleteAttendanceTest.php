<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDeleteAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_can_be_deleted(): void
    {
        // テスト用ユーザーを作成
        $user = \App\Models\User::factory()->create();

        // 削除する勤怠記録を作成
        $attendance = \App\Models\AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-11-09',
            'clock_in' => '09:00:00',
        ]);

        // DELETE APIで削除
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/attendance-records/' . $attendance->id);

        // 204（削除成功）が返るか確認
        $response->assertStatus(204);

        // データベースから削除されたか確認
        $this->assertDatabaseMissing('attendance_records', [
            'id' => $attendance->id,
        ]);
    }
}