<?php

namespace App\Filament\Resources\Logistics\Pages;

use App\Filament\Resources\Logistics\LogisticResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLogistics extends ManageRecords
{
    protected static string $resource = LogisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
