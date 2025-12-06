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
        // Start with basic fields from the model
        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'is_verified' => $this->is_verified,

            // Student fields
            'semester' => $this->semester,
            'intake' => $this->intake,
            'section' => $this->section,
            'student_id' => $this->student_id,
            'cgpa' => $this->cgpa,

            // Faculty fields
            'department' => $this->department,
            'faculty_code' => $this->faculty_code,
            'designation' => $this->designation,

            // Common fields
            'phone' => $this->phone,
            'profile_picture' => $this->profile_picture
                ? asset('storage/' . $this->profile_picture)
                : null,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // User type - get it from the accessor
            'user_type' => $this->user_type,
        ];

        // Add program data if the relationship is loaded
        if ($this->relationLoaded('program')) {
            if ($this->program) {
                $data['program'] = [
                    'id' => $this->program->id,
                    'name' => $this->program->name,
                    'code' => $this->program->code,
                ];
            } else {
                $data['program'] = null;
            }
        } else {
            // If program relationship is not loaded, set it to null
            $data['program'] = null;
        }

        return $data;
    }
}