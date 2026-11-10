<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
    'id' => $this->id,
    'user_id' => $this->user_id,
    'date' => $this->date,
    'clock_in' => $this->clock_in,
    'clock_out' => $this->clock_out,
    'comment' => $this->comment,
    'breaks' => $this->whenLoaded('breakTimes'),
    'user' => $this->whenLoaded('user', function () {
    return [
        'id' => $this->user->id,
        'name' => $this->user->name,
    ];
}),
'correction_requests' => $this->whenLoaded('attendanceRequests'),
];
    }
}
