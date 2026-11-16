<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AttendanceReportTest extends TestCase
{
  public function test_guest_cannot_view_report(): void
{
    $response = $this->get('/attendance/report');

    $response->assertRedirect('/login');
}  
public function test_logged_in_user_can_view_report(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/attendance/report');

    $response->assertStatus(200);
}
}
