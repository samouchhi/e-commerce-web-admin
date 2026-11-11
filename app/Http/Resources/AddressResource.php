<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'name' => $request->user()->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'note' => $this->note,
        ];
    }
}
