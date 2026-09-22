<?php

namespace App\Filament\Widgets;

use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Products;
use App\Models\ProductVariant;
use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockTable extends TableWidget
{

    protected static ?int $sort = 5;

    protected static ?string $heading = 'Low Stock Products';
    public function table(Table $table): Table
    {
        $alertStock = GeneralSetting::query()->value('alert_stock');
        return $table
            ->query(fn(): Builder => ProductVariant::query()->where('stock_qty', '<=', $alertStock))
            ->columns([
                TextColumn::make('product.name')->label('Name'),
                TextColumn::make('stock_qty')->label('Stock')->sortable()->badge(),
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
