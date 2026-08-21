<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // public function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['name'] = strtoupper($data['name']);
    //     return $data;
    // }
}
