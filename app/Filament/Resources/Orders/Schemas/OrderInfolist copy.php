<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderItems;
use Dom\Text;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Order details')
                            ->schema([
                                TextEntry::make('order_number'),
                                TextEntry::make('customer.name'),
                                TextEntry::make('payment_status')->badge(),
                                TextEntry::make('shipping_cost')->money('USD'),
                                TextEntry::make('total_amount')->money('USD'),

                            ])
                            ->columns(3)
                            ->compact(),
                        Section::make('Notes')
                            ->schema([
                                TextEntry::make('address.note')
                                    ->label(' ')
                                    ->placeholder('No notes available')
                                    ->disabled()
                                    ->columnSpanFull(),
                            ])
                            ->compact(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Shipping Details')
                            ->schema([
                                TextEntry::make('shipping_status')
                                    ->label('Status')
                                    ->badge(),
                            ])
                            ->compact(),

                        Section::make('Delivery details')
                            ->schema([
                                TextEntry::make('logistic.name'),
                                TextEntry::make('address.phone'),
                                TextEntry::make('address.address'),
                            ])
                            ->compact(),

                    ])
                    ->columnSpan(['lg' => 1]),

                Section::make('Order Items')
                    ->compact()
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->state(fn(Order $record) => $record->loadMissing('items.productVariant.product.images')->items)
                            ->table([
                                TableColumn::make('Image'),
                                TableColumn::make('Product'),
                                TableColumn::make('Variant'),
                                TableColumn::make('Quantity'),
                            ])
                            ->schema([
                                ImageEntry::make('product_image')
                                    ->label('Image')
                                    ->disk('public')
                                    ->imageSize(48)
                                    ->state(
                                        fn(OrderItems $record): ?string => $record->productVariant?->product?->images->first()?->image_path
                                    ),
                                TextEntry::make('productVariant.product.name')->label('Product'),
                                TextEntry::make('productVariant.name')->label('Variant')->badge(),
                                TextEntry::make('quantity')->badge(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

            ]);
    }
}
