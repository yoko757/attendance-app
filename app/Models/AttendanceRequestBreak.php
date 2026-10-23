<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
    'attendance_request_id',
    'new_break_in',
    'new_break_out',
];
public function attendanceRequest()
{
    return $this->belongsTo(AttendanceRequest::class);
}
}
