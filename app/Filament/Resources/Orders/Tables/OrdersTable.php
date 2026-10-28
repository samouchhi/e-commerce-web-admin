<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderShippingStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
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

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('order_number')->label(__('Order number'))
                    ->searchable(),
                TextColumn::make('customer.name')->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('logistic.name')->label(__('Name'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')->label(__('Total'))
                    ->numeric()
                    ->money()
                    ->sortable(),
                TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_cost')->label(__('Shipping Cost'))
                    ->money()
                    ->sortable(),
                TextColumn::make('payment_status')->label(__('Payment status'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shipping_status')->label(__('Shipping status'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')->label(__('Payment status'))
                    ->options([
                        'pending' => __('Pending'),
                        'paid' => 'Paid',
                        'expired' => 'Expired',
                    ]),
                SelectFilter::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Customer $customer): string => $customer->name
                            ?? $customer->email
                            ?? "Customer #{$customer->id}",
                    )
                    ->searchable(),

            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('Shipped')
                        ->icon('heroicon-o-truck')
                        ->label(__('Shipped'))
                        ->color('success')
                        ->visible(fn (Order $record): bool => $record->payment_status?->value === 'paid')
                        ->action(function (Order $record): void {
                            $record->shipping_status = OrderShippingStatus::Shipped;
                            $record->save();

                            Notification::make()
                                ->title($record->order_number.' has been shipped.')
                                ->success()
                                ->send();
                        }),
                    ViewAction::make(),
                    Action::make('print')
                        ->label(fn (Order $record): string => $record->payment_status === PaymentStatus::Paid ? __('Print Receipt') : __('Print Invoice'))
                        ->icon('heroicon-o-printer')
                        ->authorize('view')
                        ->visible(fn (Order $record): bool => in_array($record->payment_status, [PaymentStatus::Paid, PaymentStatus::Pending, PaymentStatus::Expired], true))
                        ->url(fn (Order $record): string => route('filament.dashboard.orders.print', $record))
                        ->openUrlInNewTab(),
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
