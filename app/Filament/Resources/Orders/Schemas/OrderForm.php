<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->prefixIcon('heroicon-m-user')
                    ->required(),
                Select::make('logistic_id')
                    ->relationship('logistic', 'name')
                    ->searchable()
                    ->prefixIcon('heroicon-m-truck')
                    ->preload()
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->prefixIcon('heroicon-m-currency-dollar')
                    ->numeric(),
                TextInput::make('subtotal_amount')
                    ->required()
                    ->prefixIcon('heroicon-m-currency-dollar')
                    ->numeric(),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefixIcon('heroicon-m-currency-dollar'),
                ToggleButtons::make('payment_status')
                    ->options(PaymentStatus::class)
                    ->required()
                    ->default(PaymentStatus::Pending),
                ToggleButtons::make('shipping_status')
                    ->options(ShippingStatus::class)
                    ->required()
                    ->default(ShippingStatus::Pending),
            ]);
    }
}
