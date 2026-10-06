<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacultyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'designation' => $this->designation,
            'department' => $this->department,
            'college' => $this->college,
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years !== null ? (float) $this->experience_years : null,
            'bio' => $this->bio,
            'location' => $this->location,
            'city' => $this->city,
            'profile_photo_file_id' => $this->profile_photo_file_id,
            'resume_file_id' => $this->resume_file_id,
            'education' => $this->whenLoaded('education', fn () => $this->education->map(fn ($e) => [
                'id' => $e->id, 'degree' => $e->degree, 'institution' => $e->institution,
                'field_of_study' => $e->field_of_study, 'start_year' => $e->start_year, 'end_year' => $e->end_year,
            ])->values()),
            'experience' => $this->whenLoaded('experience', fn () => $this->experience->map(fn ($e) => [
                'id' => $e->id, 'title' => $e->title, 'organization' => $e->organization, 'location' => $e->location,
                'start_date' => $e->start_date?->toDateString(), 'end_date' => $e->end_date?->toDateString(),
                'is_current' => $e->is_current, 'description' => $e->description,
            ])->values()),
            'skills' => $this->whenLoaded('skills', fn () => $this->skills->pluck('skill')->values()),
        ];
    }
}
