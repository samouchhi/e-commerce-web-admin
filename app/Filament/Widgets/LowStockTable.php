<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\GeneralSetting;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockTable extends TableWidget
{
    protected static bool $isLazy = false;

    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 3];

    protected function getTableHeading(): string
    {
        return __('Stock to watch');
    }

    public function table(Table $table): Table
    {
        $alertStock = GeneralSetting::query()->value('alert_stock') ?? 5;

        return $table
            ->query(fn (): Builder => ProductVariant::query()
                ->limit(5)
                ->with('product.images')
                ->where('stock_qty', '<=', $alertStock)
                ->when($this->pageFilters['product_id'] ?? null, fn (Builder $query, int|string $productId) => $query->where('product_id', $productId))
                ->orderBy('stock_qty'))
            ->paginated(false)
            ->columns([
                ViewColumn::make('product.name')
                    ->label(__('Product'))
                    ->view('filament.tables.columns.product-with-image'),
                TextColumn::make('stock_qty')->label(__('Left'))->badge()
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : 'warning'),
            ])
            ->emptyStateHeading(__('Stock levels look good'))
            ->emptyStateDescription(__('No variants are below the stock alert threshold.'))
            ->headerActions([
                Action::make('restock')->label(__('View Purchases'))->color('gray')->url(PurchaseResource::getUrl('create'))
                    ->visible(fn (): bool => PurchaseResource::canCreate()),
            ]);
    }
}
