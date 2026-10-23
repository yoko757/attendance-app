<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
    'attendance_record_id',
    'user_id',
    'new_clock_in',
    'new_clock_out',
    'comment',
    'status',
];
public function attendanceRecord()
{
    return $this->belongsTo(AttendanceRecord::class);
}
public function user()
{
    return $this->belongsTo(User::class);
}
public function breaks()
{
    return $this->hasMany(AttendanceRequestBreak::class);
}
}
