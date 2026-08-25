<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', ProductResource::getModel()::count()),
            Stat::make('Product Inventory', ProductResource::getModel()::with('variants')->get()->sum(fn (Product $product) => $product->variants->sum('stock_qty'))),
            Stat::make('Average Cost', '$'.number_format(ProductResource::getModel()::with('variants')->get()->avg(fn (Product $product) => $product->variants->avg('cost')), 2)),
        ];
    }
}
