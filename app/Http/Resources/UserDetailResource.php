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
            'is_verified' => $this->is_verified,

            // Student fields
            'semester' => $this->semester,
            'intake' => $this->intake,
            'section' => $this->section,
            'student_id' => $this->student_id,
            'cgpa' => $this->cgpa,
            'program_id' => $this->program_id,
            'program' => new ProgramResource($this->whenLoaded('program')),

            // Faculty fields
            'department' => $this->department,
            'faculty_code' => $this->faculty_code,
            'designation' => $this->designation,

            // Common fields
            'phone' => $this->phone,
            'profile_picture' => $this->profile_picture
                ? asset('storage/' . $this->profile_picture)
                : null,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}