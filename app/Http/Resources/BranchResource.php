<?php

namespace App\Http\Resources;

use App\Support\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'branch_code' => $this->branch_code,
            'branch_name' => $this->branch_name,
            'location_display' => Location::display($this->city, $this->city_code, $this->location),
            'location' => $this->location,
            'city' => $this->city,
            'city_code' => $this->city_code,
            'state' => $this->state,
            'country' => $this->country,
            'status' => $this->status->value,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'company' => $this->whenLoaded('company', fn () => new CompanyResource($this->company)),
            'user' => $this->whenLoaded('user', fn () => ['id' => $this->user->id, 'name' => $this->user->name, 'email' => $this->user->email]),
        ];
    }
}
