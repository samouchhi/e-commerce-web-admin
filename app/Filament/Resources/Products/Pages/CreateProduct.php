<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Component;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function getWizardSubmitAction(): Action
    {
        return $this->getCreateFormAction();
    }

    protected function getFormActions(): array
    {
        return [$this->getCreateFormAction()];
    }

    public function getFormContentComponent(): Component
    {
        return parent::getFormContentComponent()->footer([]);
    }

    // public function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['name'] = strtoupper($data['name']);
    //     return $data;
    // }
}
