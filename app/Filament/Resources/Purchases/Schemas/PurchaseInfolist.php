<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Purchase;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Purchase details'))
                ->schema([
                    TextEntry::make('reference')->label(__('Reference'))->copyable(),
                    TextEntry::make('supplier.name')->label(__('Name'))->placeholder(__('Supplier unavailable')),
                    TextEntry::make('purchase_date')->date(),
                    TextEntry::make('payment_status')->label(__('Payment status'))->badge(),
                    TextEntry::make('shipping_status')->label(__('Shipping status'))->badge(),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make(__('Purchase items'))
                ->schema([
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->state(fn (Purchase $record) => $record->loadMissing(['items.variant.product', 'items.unit'])->items)
                        ->table([
                            TableColumn::make('Product'),
                            TableColumn::make('Variant'),
                            TableColumn::make('Quantity'),
                            TableColumn::make('Unit'),
                            TableColumn::make('Unit price'),
                            TableColumn::make('Subtotal'),
                        ])
                        ->schema([
                            TextEntry::make('variant.product.name')->label(__('Name'))->placeholder(__('Product unavailable')),
                            TextEntry::make('variant.name')->label(__('Name'))->placeholder(__('Variant unavailable')),
                            TextEntry::make('quantity')->label(__('Quantity'))->numeric(),
                            TextEntry::make('unit.short_name')->label(__('Short name'))->placeholder(__('Unit unavailable')),
                            TextEntry::make('unit_price')->money('USD'),
                            TextEntry::make('sub_total')->money('USD'),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make(__('Summary'))
                ->schema([
                    TextEntry::make('total_price')->label(__('Subtotal'))->money('USD'),
                    TextEntry::make('shipping_cost')->money('USD'),
                    TextEntry::make('grand_total')->money('USD'),
                    ImageEntry::make('image_path')->label(__('Receipt'))->placeholder(__('No receipt')),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
