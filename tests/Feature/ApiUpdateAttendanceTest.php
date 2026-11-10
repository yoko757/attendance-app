<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUpdateAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_can_be_updated(): void
    {
        // テスト用ユーザーを作る
        $user = \App\Models\User::factory()->create();

        // 更新する前の勤怠記録を作る
        $attendance = \App\Models\AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => '2026-11-09',
            'clock_in' => '09:00:00',
        ]);

        // 出勤時刻を8時30分に変更する
        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/attendance-records/' . $attendance->id, [
                'clock_in' => '08:30:00',
            ]);

        // 更新が成功したか確認する
        $response->assertStatus(200);

        // データベースに変更が保存されたか確認する
        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendance->id,
            'clock_in' => '08:30:00',
        ]);
    }
}

