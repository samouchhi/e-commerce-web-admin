<?php

namespace App\Observers;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\GeneralSetting;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "created" event.
     */
    public function created(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Handle the ProductVariant "updated" event.
     */
    public function updated(ProductVariant $productVariant): void
    {
        $user = User::whereHas('roles.permissions', function ($query) {
            $query->where('name', 'ViewAny:Order');
        })->first();

        if (! $user) {
            return;
        }
        $alertStock = GeneralSetting::query()->value('alert_stock');

        if (
            $user === null ||
            ! $productVariant->wasChanged('stock_qty') ||
            $productVariant->stock_qty > $alertStock
        ) {
            return;
        }

        Notification::make()
            ->title('Low Stock Alert!')
            ->body("{$productVariant->product->name} | ({$productVariant->name}) has only {$productVariant->stock_qty} items left.")
            ->warning()
            ->actions([
                Action::make('buyMore')
                    ->label('Buy More')
                    ->url(PurchaseResource::getUrl('create', panel: 'dashboard')),
            ])
            ->sendToDatabase($user);
    }

    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleted(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Handle the ProductVariant "restored" event.
     */
    public function restored(ProductVariant $productVariant): void
    {
        //
    }

    /**
     * Handle the ProductVariant "force deleted" event.
     */
    public function forceDeleted(ProductVariant $productVariant): void
    {
        //
    }
}
