<?php

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class BestSellerTable extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    protected static ?string $heading = 'Best Selling Products';

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
                    ->groupBy('product_variants.id')
                    ->orderByDesc('total_quantity')
                    ->with('product.images')
            )
            ->columns([
                TextColumn::make('#')->label('#')->getStateUsing(fn ($rowLoop) => $rowLoop->iteration)->alignCenter(),
                ViewColumn::make('product.name')
                    ->label('Product')
                    ->view('filament.tables.columns.product-with-image'),
                TextColumn::make('name')->label('Variant'),
                TextColumn::make('total_quantity')->label('Sold')->badge(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
