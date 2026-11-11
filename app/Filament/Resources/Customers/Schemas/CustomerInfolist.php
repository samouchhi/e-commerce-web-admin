<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Customer details'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Customer name'))
                            ->weight(FontWeight::SemiBold)
                            ->placeholder(__('Not provided')),
                        TextEntry::make('email')
                            ->label(__('Email address'))
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder(__('Not provided')),
                        TextEntry::make('phone')->label(__('Phone'))
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->placeholder(__('Not provided')),
                        TextEntry::make('created_at')
                            ->label(__('Customer since'))
                            ->dateTime('M j, Y')
                            ->placeholder(__('Not available')),
                    ])
                    ->columns(['default' => 1, 'sm' => 2])
                    ->compact(),
                Section::make(__('Delivery locations'))
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextEntry::make('orders.address.city')->label(__('City'))
                            ->distinctList()
                            ->listWithLineBreaks()
                            ->placeholder(__('No delivery address saved')),
                    ])
                    ->compact(),
                Section::make(__('Order history'))
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->hiddenLabel()
                            ->placeholder(__('No orders placed yet'))
                            ->table([
                                TableColumn::make('Order'),
                                TableColumn::make('Placed on'),
                                TableColumn::make('Total')->alignEnd(),
                                TableColumn::make('Payment'),
                                TableColumn::make('Shipping'),
                            ])
                            ->schema([
                                TextEntry::make('order_number')->label(__('Order number'))
                                    ->weight(FontWeight::SemiBold)
                                    ->copyable(),
                                TextEntry::make('created_at')->label(__('Created at'))->dateTime('M j, Y'),
                                TextEntry::make('total_amount')->money('USD')->alignEnd(),
                                TextEntry::make('payment_status')->label(__('Payment status'))->badge(),
                                TextEntry::make('shipping_status')->label(__('Shipping status'))->badge(),
                            ]),
                    ])
                    ->compact(),
            ]);
    }
}
