<?php

namespace App\Http\Resources\Pets;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            'name' => $this->resource->name,
            'specie' => $this->resource->specie,
            'breed' => $this->resource->breed,
            'birth_date' => $this->resource->birth_date ? Carbon::parse($this->resource->birth_date)->format("Y-m-d") : null,
            'gender' => $this->resource->gender,
            'color' => $this->resource->color,
            'weight' => $this->resource->weight,
            'avatar' => env("APP_URL")."storage/".$this->resource->avatar,
            'medical_notes' => $this->resource->medical_notes,
            'owner_id' => $this->resource->owner_id,
            "n_appointment" => $this->resource->appointments->count(),
            "n_vaccination" => $this->resource->vaccinations->count(),
            "n_surgerie" => $this->resource->surgeries->count(),
            'owner' => [
                'names' => $this->resource->owner->names,
                'surnames' => $this->resource->owner->surnames,
                'type_document' => $this->resource->owner->type_document,
                'n_document' => $this->resource->owner->n_document,
                'email' => $this->resource->owner->email,
                'phone' => $this->resource->owner->phone,
                'address' => $this->resource->owner->address,
                'city' => $this->resource->owner->city,
                'emergency_contact' => $this->resource->owner->emergency_contact,
            ],
        ];
    }
}
