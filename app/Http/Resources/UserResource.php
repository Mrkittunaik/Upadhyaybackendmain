<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role->value,
            'status' => $this->status->value,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'faculty_profile' => $this->whenLoaded('facultyProfile', fn () => $this->facultyProfile ? new FacultyProfileResource($this->facultyProfile) : null),
            'company' => $this->whenLoaded('company', fn () => $this->company ? new CompanyResource($this->company) : null),
            'branch' => $this->whenLoaded('branch', fn () => $this->branch ? new BranchResource($this->branch) : null),
        ];
    }
}
