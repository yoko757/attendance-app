<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiDeleteNotFoundTest extends TestCase
{
use RefreshDatabase; 
  
 public function test_delete_nonexistent_attendance_returns_404(): void
{
    $user = \App\Models\User::factory()->create();

    $this->assertNotNull($user);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/attendance-records/999999');

    $response->assertStatus(404)
        ->assertJson([
            'message' => '勤怠記録が見つかりません。'
        ]);
}
}
