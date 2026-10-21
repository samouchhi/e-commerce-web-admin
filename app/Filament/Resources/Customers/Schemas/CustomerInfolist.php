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
                Section::make('Customer details')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Customer name')
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('Not provided'),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->placeholder('Not provided'),
                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->placeholder('Not provided'),
                        TextEntry::make('created_at')
                            ->label('Customer since')
                            ->dateTime('M j, Y')
                            ->placeholder('Not available'),
                    ])
                    ->columns(['default' => 1, 'sm' => 2])
                    ->compact(),
                Section::make('Delivery locations')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextEntry::make('orders.address.city')
                            ->distinctList()
                            ->listWithLineBreaks()
                            ->placeholder('No delivery address saved'),
                    ])
                    ->compact(),
                Section::make('Order history')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->hiddenLabel()
                            ->placeholder('No orders placed yet')
                            ->table([
                                TableColumn::make('Order'),
                                TableColumn::make('Placed on'),
                                TableColumn::make('Total')->alignEnd(),
                                TableColumn::make('Payment'),
                                TableColumn::make('Shipping'),
                            ])
                            ->schema([
                                TextEntry::make('order_number')
                                    ->weight(FontWeight::SemiBold)
                                    ->copyable(),
                                TextEntry::make('created_at')->dateTime('M j, Y'),
                                TextEntry::make('total_amount')->money('USD')->alignEnd(),
                                TextEntry::make('payment_status')->badge(),
                                TextEntry::make('shipping_status')->badge(),
                            ]),
                    ])
                    ->compact(),
            ]);
    }
}
