<?php

namespace App\Filament\Livewire;

use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Prodstarter\FilamentNotificationCenter\Livewire\NotificationCenter;
use Prodstarter\FilamentNotificationCenter\NotificationCenterTab;

class DatabaseNotifications extends NotificationCenter
{
    public function render(): View
    {
        return view('filament.livewire.notification-center');
    }

    public function restockProductsAction(): Action
    {
        return Action::make('restockProducts')
            ->label('Restock products')
            ->icon('heroicon-o-shopping-cart')
            ->url(PurchaseResource::getUrl('create', panel: 'dashboard'))
            ->visible(fn (): bool => $this->activeCategory === 'low-stock' && PurchaseResource::canCreate());
    }

    /** @return Collection<int, NotificationCenterTab> */
    #[Computed]
    public function categoryTabs(): Collection
    {
        return parent::categoryTabs()
            ->reject(fn (NotificationCenterTab $tab): bool => $tab->id === 'general')
            ->values();
    }

    public function getUnreadNotificationsCount(): int
    {
        return $this->getBaseNotificationsQuery()->whereNull('read_at')->count();
    }

    protected function scopeQueryToCategory(Builder|Relation $query, string $categoryId): Builder|Relation
    {
        $legacyTitles = ['low-stock' => 'Low Stock Alert!', 'orders' => 'New paid order'];

        if (isset($legacyTitles[$categoryId])) {
            return $query->where(function (Builder $query) use ($categoryId, $legacyTitles): void {
                $query->where('data->viewData->category', $categoryId)
                    ->orWhere(function (Builder $query) use ($categoryId, $legacyTitles): void {
                        $query->whereNull('data->viewData->category')
                            ->where('data->title', $legacyTitles[$categoryId]);
                    });
            });
        }

        if ($categoryId === $this->getCategoryRegistry()->getDefaultCategory()) {
            return $query->where(function (Builder $query) use ($categoryId, $legacyTitles): void {
                $query->where('data->viewData->category', $categoryId)
                    ->orWhere(function (Builder $query) use ($legacyTitles): void {
                        $query->whereNull('data->viewData->category')
                            ->where(function (Builder $query) use ($legacyTitles): void {
                                $query->whereNull('data->title')->orWhereNotIn('data->title', array_values($legacyTitles));
                            });
                    });
            });
        }

        return parent::scopeQueryToCategory($query, $categoryId);
    }

    public function getNotification(DatabaseNotification $notification): Notification
    {
        $filamentNotification = parent::getNotification($notification);
        $actions = $filamentNotification->getActions();

        if ($filamentNotification->getTitle() === 'Low Stock Alert!') {
            $actions = [];
        }

        $method = $notification->unread() ? 'markNotificationAsRead' : 'markNotificationAsUnread';
        $handler = $method."('".$notification->getKey()."')";

        return $filamentNotification->actions([
            ...$actions,
            Action::make($notification->unread() ? 'markAsRead' : 'markAsUnread')
                ->label($notification->unread() ? 'Mark as read' : 'Mark as unread')
                ->color('gray')
                ->action($handler)
                ->livewireTarget($handler),
        ]);
    }
}
