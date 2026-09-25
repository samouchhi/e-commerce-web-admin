<?php

namespace App\Filament\Livewire;

use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\Action;
use Filament\Livewire\DatabaseNotifications as FilamentDatabaseNotifications;
use Filament\Notifications\Notification;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotifications extends FilamentDatabaseNotifications
{
    public function getNotification(DatabaseNotification $notification): Notification
    {
        $filamentNotification = parent::getNotification($notification);
        $actions = $filamentNotification->getActions();

        if ($filamentNotification->getTitle() === 'Low Stock Alert!') {
            $actions = [
                Action::make('buyMore')
                    ->label('Buy More')
                    ->url(PurchaseResource::getUrl('create', panel: 'dashboard')),
            ];
        }

        return $filamentNotification->actions([
            ...$actions,
            $notification->unread()
                ? Action::make('markAsRead')->label('Mark as read')->color('gray')->markAsRead()
                : Action::make('markAsUnread')->label('Mark as unread')->color('gray')->markAsUnread(),
        ]);
    }
}
