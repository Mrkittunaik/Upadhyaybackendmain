<?php

namespace App\Http\Resources;

use App\Support\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $p = $this->relationLoaded('profile') ? $this->profile : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_code' => $this->company_code,
            'verification_status' => $this->verification_status->value,
            'location_display' => $p ? Location::display($p->city, $p->city_code, $p->location) : null,
            'profile' => $p ? [
                'description' => $p->description, 'website' => $p->website, 'email' => $p->email,
                'phone' => $p->phone, 'industry' => $p->industry, 'address' => $p->address,
                'city' => $p->city, 'city_code' => $p->city_code, 'state' => $p->state,
                'country' => $p->country, 'location' => $p->location, 'logo_file_id' => $p->logo_file_id,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
