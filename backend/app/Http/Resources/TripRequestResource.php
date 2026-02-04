<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripRequestResource extends JsonResource
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
            'traveler_id' => $this->traveler_id,
            'destination_id' => $this->destination_id,
            'description' => $this->description,
            'departure_datetime' => $this->departure_datetime->toISOString(),
            'return_datetime' => $this->return_datetime->toISOString(),
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'traveler' => $this->whenLoaded('traveler', fn () => [
                'id' => $this->traveler->id,
                'name' => $this->traveler->name,
                'is_active' => $this->traveler->is_active,
                'user' => $this->when($this->traveler->relationLoaded('user'), fn () => [
                    'id' => $this->traveler->user->id,
                    'name' => $this->traveler->user->name,
                    'email' => $this->traveler->user->email,
                    'is_admin' => $this->traveler->user->is_admin,
                ]),
            ]),
            'destination' => $this->whenLoaded('destination', fn () => [
                'id' => $this->destination->id,
                'city' => $this->destination->city,
                'state' => $this->destination->state,
                'country' => $this->destination->country,
                'full_location' => $this->destination->full_location,
            ]),
        ];
    }
}
