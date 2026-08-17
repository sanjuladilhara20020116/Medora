<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim($this->first_name.' '.$this->last_name),
            'email' => $this->email,
            'phone' => $this->phone,
            'job_title' => $this->job_title,
            'employment_type' => $this->employment_type,
            'joined_on' => $this->joined_on?->toDateString(),
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'status' => $this->status,
            'notes' => $this->notes,
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department?->id,
                'code' => $this->department?->code,
                'name' => $this->department?->name,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'username' => $this->user?->username,
                'role' => $this->user?->relationLoaded('role') ? $this->user?->role?->name : null,
            ]),
            'attendance_records_count' => $this->whenCounted('attendanceRecords'),
            'leave_requests_count' => $this->whenCounted('leaveRequests'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
