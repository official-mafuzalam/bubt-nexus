<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
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

            // Student fields
            'semester' => $this->semester,
            'intake' => $this->intake,
            'program' => $this->program,
            'student_id' => $this->student_id,
            'cgpa' => $this->cgpa,

            // Faculty fields
            'department' => $this->department,
            'faculty_id' => $this->faculty_id,
            'designation' => $this->designation,
            'office_room' => $this->office_room,
            'office_hours' => $this->office_hours,

            // Common fields
            'phone' => $this->phone,
            'address' => $this->address,
            'date_of_birth' => $this->date_of_birth,
            'emergency_contact' => $this->emergency_contact,
            'profile_picture' => $this->profile_picture,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}