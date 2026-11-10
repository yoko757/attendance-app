<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiPostAttendanceTest extends TestCase
{
 use RefreshDatabase;

public function test_post_attendance_creates_record(): void
{
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/attendance-records', [
            'date' => '2026-11-09',
            'clock_in' => '09:00:00',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('attendance_records', [
        'user_id' => $user->id,
        'date' => '2026-11-09',
        'clock_in' => '09:00:00',
    ]);
}
public function test_post_without_clock_in_returns_422(): void
{
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/attendance-records', [
            'date' => '2026-11-09',
        ]);

    $response->assertStatus(422);

    $response->assertJsonValidationErrors(['clock_in']);

    $this->assertDatabaseMissing('attendance_records', [
        'user_id' => $user->id,
        'date' => '2026-11-09',
    ]);
}
}
