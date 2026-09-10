<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
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
                TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('logistic_id')
                    ->numeric(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('subtotal_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
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
