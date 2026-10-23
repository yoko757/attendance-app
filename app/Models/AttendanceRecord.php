<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id',
    'date',
    'clock_in',
    'clock_out',
    'comment',
];
public function user()
{
    return $this->belongsTo(User::class);
}
public function breakTimes()
{
    return $this->hasMany(BreakTime::class);
}
public function attendanceRequests()
{
    return $this->hasMany(AttendanceRequest::class);
}
public function getTotalBreakTimeAttribute()
{
    return $this->breakTimes->sum(function ($break) {
        if (!$break->break_in || !$break->break_out) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->date . ' ' . $break->break_in);
$end = \Carbon\Carbon::parse($this->date . ' ' . $break->break_out);

if ($end->lt($start)) {
    $end->addDay();
}

return $start->diffInMinutes($end);
    });
}
public function getTotalTimeAttribute()
{
    if (!$this->clock_in || !$this->clock_out) {
        return null;
    }

$start = \Carbon\Carbon::parse($this->date . ' ' . $this->clock_in);
$end = \Carbon\Carbon::parse($this->date . ' ' . $this->clock_out);

if ($end->lt($start)) {
    $end->addDay();
}

$minutes = $start->diffInMinutes($end);
    return $minutes - $this->total_break_time;
}
}


