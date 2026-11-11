<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Enums\ShippingStatus;
use App\Models\Purchase;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label(__('Reference'))->searchable(),
                // TextColumn::make('items')

                //     ->label(__('Items'))
                //     ->badge()
                //     ->wrap()
                //     ->getStateUsing(
                //         fn ($record) => $record->items
                //             ->map(fn ($item) => ($item->variant?->name ?? '?').' | '.($item->variant?->product?->name ?? '?'))
                //             ->toArray()
                //     )
                //     ->searchable(query: function (Builder $query, string $search): Builder {
                //         return $query->whereHas('items', function ($q) use ($search) {
                //             $q->whereHas('variant', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                //                 ->orWhereHas('variant.product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                //         });
                //     }),
                TextColumn::make('supplier.name')->label(__('Supplier'))->searchable(),
                TextColumn::make('purchase_date')->label(__('Purchase Date'))->date()->sortable(),
                TextColumn::make('grand_total')->label(__('Grand Total'))->money('USD')->sortable(),
                TextColumn::make('payment_status')->label(__('Payment Status'))->sortable()->badge(),
                TextColumn::make('shipping_status')->label(__('Shipping Status'))->sortable()
                    ->badge(),
                TextColumn::make('Items Qty')
                    ->getStateUsing(fn ($record) => $record->items->sum('quantity'))
                    ->label(__('Items Qty'))->sortable()->badge(),
            ])

            //

            ->filters(
                [
                    SelectFilter::make('supplier_id')
                        ->label(__('Supplier'))
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Select Supplier')),
                ],

            )

            ->recordActions([
                ActionGroup::make([
                    Action::make('Shipped')
                        ->icon('heroicon-o-truck')
                        ->color('success')
                        ->visible(fn (Purchase $record): bool => $record->shipping_status === ShippingStatus::Pending)
                        ->action(function (Purchase $record): void {
                            $record->update(['shipping_status' => ShippingStatus::Shipped]);

                            Notification::make()
                                ->title($record->reference.' has been shipped.')
                                ->success()
                                ->send();
                        }),

                    Action::make('Delivered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Purchase $record): bool => $record->shipping_status === ShippingStatus::Shipped)
                        ->action(function (Purchase $record): void {
                            $record->update(['shipping_status' => ShippingStatus::Delivered]);

                            Notification::make()
                                ->title($record->reference.' has been delivered.')
                                ->success()
                                ->send();
                        }),
                    ViewAction::make(),
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
