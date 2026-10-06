<?php

namespace App\Http\Resources;

use App\Support\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'branch_name' => $this->branch_name,
            'location_display' => Location::display($this->city, $this->city_code, $this->location),
            'city' => $this->city,
            'city_code' => $this->city_code,
            'state' => $this->state,
            'country' => $this->country,
            'status' => $this->status->value,
            'rejection_reason' => $this->rejection_reason,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'company' => $this->whenLoaded('company', fn () => ['id' => $this->company->id, 'name' => $this->company->name]),
            'requester' => $this->whenLoaded('requester', fn () => ['id' => $this->requester->id, 'name' => $this->requester->name, 'email' => $this->requester->email]),
        ];
    }
}
