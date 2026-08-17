<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'leave_code' => $this->leave_code,
            'leave_type' => $this->leave_type,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewer_comment' => $this->reviewer_comment,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee?->id,
                'employee_code' => $this->employee?->employee_code,
                'full_name' => trim(($this->employee?->first_name ?? '').' '.($this->employee?->last_name ?? '')),
                'job_title' => $this->employee?->job_title,
                'department' => $this->employee?->relationLoaded('department') ? $this->employee?->department?->name : null,
            ]),
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn () => $this->reviewedBy?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
