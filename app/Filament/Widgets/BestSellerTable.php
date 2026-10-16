<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BestSellerTable extends TableWidget
{
    protected static bool $isLazy = false;

    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 3];

    protected static ?string $heading = 'Best sellers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductVariant::query()
                    ->select('product_variants.*')
                    ->selectRaw('SUM(order_items.quantity) as total_quantity')
                    ->join('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.payment_status', 'paid')
                    ->whereDate('orders.created_at', '>=', $this->pageFilters['start_date'] ?? Carbon::parse($this->pageFilters['end_date'] ?? now())->subDays(6)->toDateString())
                    ->whereDate('orders.created_at', '<=', $this->pageFilters['end_date'] ?? now()->toDateString())
                    ->when($this->pageFilters['product_id'] ?? null, fn(Builder $query, int|string $productId) => $query->where('product_variants.product_id', $productId))
                    ->groupBy('product_variants.id')
                    ->limit(5)
                    ->orderByDesc('total_quantity')
                    ->with('product.images')
            )
            ->paginated(false)
            ->emptyStateHeading('No sales in this period')
            ->emptyStateDescription('Paid product sales will appear here.')
            ->columns([
                TextColumn::make('#')->label('#')->getStateUsing(fn($rowLoop) => $rowLoop->iteration)->alignCenter(),
                ViewColumn::make('product.name')
                    ->label('Product')
                    ->view('filament.tables.columns.product-with-image'),
                TextColumn::make('total_quantity')->label('Sold')->badge(),

            ])
            ->headerActions([
                Action::make('products')->label('Products')->color('gray')->url(ProductResource::getUrl())
                    ->visible(fn(): bool => ProductResource::canViewAny()),
            ]);
    }
}
