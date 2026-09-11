<?php

namespace App\Observers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('payment_status') || $order->payment_status !== PaymentStatus::Paid) {
            return;
        }

        Notification::make()
            ->title('New paid order')
            ->body("Order {$order->order_number} has been paid.")
            ->success()
            ->sendToDatabase(User::query()->whereHas('roles')->get());
    }
}
