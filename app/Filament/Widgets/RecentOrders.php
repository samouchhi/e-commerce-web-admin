<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Carbon\Carbon;
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

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent paid orders';

    public function table(Table $table): Table
    {

        return $table
            ->query(fn(): Builder => Order::query()
                ->orderByDesc('created_at')
                ->limit(5)
                ->where('payment_status', 'paid')
                ->whereDate('created_at', '>=', $this->pageFilters['start_date'] ?? Carbon::parse($this->pageFilters['end_date'] ?? now())->subDays(6)->toDateString())
                ->whereDate('created_at', '<=', $this->pageFilters['end_date'] ?? now()->toDateString())
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
            ->emptyStateHeading('No paid orders in this period')
            ->emptyStateDescription('Try a different date range or product.')
            ->headerActions([
                Action::make('orders')->label('All orders')->color('gray')->url(OrderResource::getUrl())
                    ->visible(fn(): bool => OrderResource::canViewAny()),
            ]);
    }
}
