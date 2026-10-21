<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrders extends TableWidget
{
    protected static bool $isLazy = false;

    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 9];

    protected static ?string $heading = 'Recent paid orders';

    public function table(Table $table): Table
    {

        return $table
            ->query(fn(): Builder => Order::query()
                ->orderByDesc('created_at')
                ->limit(5)
                ->where('payment_status', 'paid')
                ->when($this->pageFilters['product_id'] ?? null, fn(Builder $query, int|string $productId) => $query->whereHas('items.productVariant', fn(Builder $query) => $query->where('product_id', $productId)))
                ->with('customer')->withCount('items'))

            ->paginated(false)
            ->columns([
                TextColumn::make('order_number')->label('Order')->weight('medium'),
                TextColumn::make('customer.name')->label('Customer')->placeholder('Guest'),
                TextColumn::make('items_count')->label('Items')->alignCenter()->badge(),
                TextColumn::make('total_amount')->label('Total')->money('usd', true),
                TextColumn::make('payment_status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Date')->dateTime('M d, Y'),
            ])
            ->recordUrl(fn(Order $record): ?string => OrderResource::canView($record) ? OrderResource::getUrl('view', ['record' => $record]) : null)
            ->emptyStateHeading('No paid orders')
            ->emptyStateDescription('Paid orders matching the selected product will appear here.')
            ->headerActions([
                Action::make('orders')->label('View Orders')->color('gray')->url(OrderResource::getUrl())
                    ->visible(fn(): bool => OrderResource::canViewAny()),
            ]);
    }
}
