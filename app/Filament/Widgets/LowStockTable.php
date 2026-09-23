<?php

namespace App\Filament\Widgets;

use App\Models\GeneralSetting;
use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockTable extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    protected static ?string $heading = 'Low Stock Products';

    public function table(Table $table): Table
    {
        $alertStock = GeneralSetting::query()->value('alert_stock');

        return $table
            ->query(fn (): Builder => ProductVariant::query()
                ->limit(5)
                ->with('product.images')->where('stock_qty', '<=', $alertStock))
            ->paginated(false)
            ->columns([
                TextColumn::make('#')->label('#')->getStateUsing(fn ($rowLoop) => $rowLoop->iteration)->alignCenter(),

                ViewColumn::make('product.name')
                    ->label('Product')
                    ->view('filament.tables.columns.product-with-image'),
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
