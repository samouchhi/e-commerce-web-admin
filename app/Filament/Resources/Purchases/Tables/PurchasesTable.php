<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label('Reference')->searchable(),
                TextColumn::make('supplier.name')->label('Supplier')->searchable(),
                TextColumn::make('date')->label('Date')->date()->sortable(),
                TextColumn::make('quantity')->label('Items Qty')->sortable()->badge(),
                TextColumn::make('total_price')->label('Total')->money('USD')->sortable(),
                TextColumn::make('payment_status')->label('Payment')->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
