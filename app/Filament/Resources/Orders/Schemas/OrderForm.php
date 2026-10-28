<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderShippingStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
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
                TextInput::make('order_number')->label(__('Order number'))
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Customer $customer): string => $customer->name
                            ?? $customer->email
                            ?? "Customer #{$customer->id}",
                    )
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
                ToggleButtons::make('payment_status')->label(__('Payment status'))
                    ->options(PaymentStatus::class)
                    ->required()
                    ->default(PaymentStatus::Pending),
                ToggleButtons::make('shipping_status')->label(__('Shipping status'))
                    ->options(OrderShippingStatus::class)
                    ->required()
                    ->default(OrderShippingStatus::Pending),
            ]);
    }
}
