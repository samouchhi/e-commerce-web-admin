<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
            'products' => $this->whenLoaded('products'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
