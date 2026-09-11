<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->label('Email address'),
                        TextEntry::make('phone'),
                        TextEntry::make('orders.address.city')
                            ->label('Delivery addresses')
                            ->distinctList()
                            ->listWithLineBreaks()
                            ->badge()
                            ->placeholder('No delivery address saved'),
                        TextEntry::make('orders.order_number')
                            ->label('Orders')
                            ->distinctList()
                            ->listWithLineBreaks()
                            ->badge()
                            ->placeholder('No orders placed yet')

                    ])
                    ->columns(3)
                    ->columnSpanFull(),

            ]);
    }
}
