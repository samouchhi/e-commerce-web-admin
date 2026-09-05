<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class SettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'site_name' => $this->site_name ?? null,
            'site_logo' => $this->site_logo
                ? Storage::disk('public')->url($this->site_logo)
                : null,
            'site_favicon' => $this->site_favicon
                ? Storage::disk('public')->url($this->site_favicon)
                : null,
            'site_email' => $this->site_email ?? null,
            'site_phone' => $this->site_phone ?? null,
            'site_address' => $this->site_address ?? null,
        ];
    }
}
