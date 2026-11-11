<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderItems;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Section::make(__('Order overview'))
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label(__('Order reference'))
                            ->weight(FontWeight::Bold)
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label(__('Placed on'))
                            ->dateTime('M j, Y · g:i A'),
                        TextEntry::make('payment_status')->label(__('Payment'))->badge(),
                        TextEntry::make('shipping_status')->label(__('Shipping'))->badge(),
                    ])
                    ->columns(['default' => 1, 'sm' => 2, 'xl' => 4])
                    ->compact()
                    ->columnSpanFull(),
                Group::make([
                    Section::make(__('Customer note'))
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->visible(fn (Order $record): bool => filled($record->address?->note))
                        ->schema([
                            TextEntry::make('address.note')
                                ->hiddenLabel()
                                ->prose(),
                        ])
                        ->compact(),
                    Section::make(__('Order items'))
                        ->icon('heroicon-o-cube')
                        ->schema([
                            RepeatableEntry::make('items')
                                ->hiddenLabel()
                                ->state(fn (Order $record) => $record->loadMissing('items.productVariant.product.images')->items)
                                ->table([
                                    TableColumn::make('Image')->width('64px'),
                                    TableColumn::make('Product'),
                                    TableColumn::make('Qty')->alignEnd(),
                                    TableColumn::make('Unit price')->alignEnd(),
                                    TableColumn::make('Subtotal')->alignEnd(),
                                ])
                                ->schema([
                                    ImageEntry::make('product_image')
                                        ->hiddenLabel()
                                        ->disk('public')
                                        ->imageSize(48)
                                        ->state(fn (OrderItems $record): ?string => $record->productVariant?->product?->images->first()?->image_path),
                                    Group::make([
                                        TextEntry::make('productVariant.product.name')->label(__('Name'))
                                            ->hiddenLabel()
                                            ->weight(FontWeight::SemiBold)
                                            ->placeholder(__('Product unavailable')),
                                        TextEntry::make('productVariant.name')->label(__('Name'))
                                            ->hiddenLabel()
                                            ->color('gray')
                                            ->size(TextSize::Small)
                                            ->placeholder(__('Variant unavailable')),
                                    ]),
                                    TextEntry::make('quantity')->label(__('Quantity'))->hiddenLabel()->alignEnd(),
                                    TextEntry::make('unit_price')->hiddenLabel()->money('USD')->alignEnd(),
                                    TextEntry::make('subtotal_price')
                                        ->hiddenLabel()
                                        ->money('USD')
                                        ->weight(FontWeight::SemiBold)
                                        ->alignEnd(),
                                ]),
                        ])
                        ->compact(),
                    Section::make(__('Payment summary'))
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            TextEntry::make('subtotal_amount')->label(__('Subtotal'))->money('USD')->inlineLabel()->alignEnd(),
                            TextEntry::make('shipping_cost')->label(__('Shipping'))->money('USD')->inlineLabel()->alignEnd(),
                            TextEntry::make('total_amount')
                                ->label(__('Order total'))
                                ->money('USD')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold)
                                ->inlineLabel()
                                ->alignEnd(),
                        ])
                        ->compact(),
                ])
                    ->columnSpan(['lg' => 2])
                    ->columnOrder(['default' => 2, 'lg' => 1]),
                Group::make([
                    Section::make(__('Customer & delivery'))
                        ->icon('heroicon-o-truck')
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label(__('Customer'))
                                ->weight(FontWeight::SemiBold)
                                ->placeholder(__('Not provided')),
                            TextEntry::make('address.phone')
                                ->label(__('Phone'))
                                ->icon('heroicon-o-phone')
                                ->copyable()
                                ->placeholder(__('Not provided')),
                            TextEntry::make('address.address')
                                ->label(__('Delivery address'))
                                ->placeholder(__('No delivery address saved')),
                            TextEntry::make('address.city')->label(__('City'))->placeholder(__('Not provided')),
                            TextEntry::make('logistic.name')
                                ->label(__('Delivery service'))
                                ->badge()
                                ->color('gray')
                                ->placeholder(__('Not assigned')),
                        ])
                        ->compact(),
                ])
                    ->columnSpan(['lg' => 1])
                    ->columnOrder(['default' => 1, 'lg' => 2]),
            ]);
    }
}
