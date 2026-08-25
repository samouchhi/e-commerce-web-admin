<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label('Reference')->searchable(),
                TextColumn::make('items')

                    ->label('Items')
                    ->badge()
                    ->wrap()
                    ->getStateUsing(fn ($record) => $record->items
                        ->map(fn ($item) => ($item->variant?->name ?? '?').' | '.($item->variant?->product?->name ?? '?'))
                        ->toArray()
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('items', function ($q) use ($search) {
                            $q->whereHas('variant', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('variant.product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('supplier.name')->label('Supplier')->searchable(),
                TextColumn::make('purchase_date')->label('Purchase Date')->date()->sortable(),
                TextColumn::make('grand_total')->label('Grand Total')->money('USD')->sortable(),
                TextColumn::make('payment_status')->label('Payment Status')->sortable()->badge(),
                TextColumn::make('shipping_status')->label('Shipping Status')->sortable()
                    ->badge(),
                TextColumn::make('Items Qty')
                    ->getStateUsing(fn ($record) => $record->items->sum('quantity'))
                    ->label('Items Qty')->sortable()->badge(),
            ])
                //

            ->recordActions([
                                ActionGroup::make([
                                    EditAction::make(),
                                    DeleteAction::make(),
                                ]),
                            ])
            ->toolbarActions([
                                BulkActionGroup::make([
                                    DeleteBulkAction::make(),
                                ]),
                            ]);
    }
}
