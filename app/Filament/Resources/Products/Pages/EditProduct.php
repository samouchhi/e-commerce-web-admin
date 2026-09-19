<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Component;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function getWizardSubmitAction(): Action
    {
        return $this->getSaveFormAction();
    }

    public function getFormContentComponent(): Component
    {
        return parent::getFormContentComponent()->footer([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
