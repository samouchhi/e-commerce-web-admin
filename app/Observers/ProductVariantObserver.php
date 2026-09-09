<?php

namespace App\Observers;

use App\Models\GeneralSetting;
use App\Models\ProductVariant;
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
        $user = auth()->user();
        $alertStock = GeneralSetting::query()->value('alert_stock');

        if (
            $productVariant->wasChanged('stock_qty') &&
            $productVariant->stock_qty <= $alertStock
        ) {
            Notification::make()
                ->title('Low Stock Alert!')
                ->body("{$productVariant->product->name} | ({$productVariant->name}) has only {$productVariant->stock_qty} items left.")
                ->warning()
                ->sendToDatabase($user);
        }
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
