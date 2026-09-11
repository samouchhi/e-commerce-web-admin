<?php

namespace App\Observers;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Auth;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('payment_status') || $order->payment_status !== PaymentStatus::Paid) {
            return;
        }
        $user = Auth::user();

        Notification::make()
            ->title('New paid order')
            ->body("Order {$order->order_number} has been paid.")
            ->success()
            ->actions([
                Action::make('viewOrder')
                    ->label('View Order')
                    ->url(OrderResource::getUrl('view', ['record' => $order], panel: 'dashboard')),
            ])
            ->sendToDatabase($user);
    }
}
