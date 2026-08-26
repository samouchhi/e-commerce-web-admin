<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Models\ProductVariant;
use App\Models\Unit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make([
                            TextInput::make('reference')
                                ->default('OR-'.random_int(100000, 999999))
                                ->disabled()
                                ->label('Reference')
                                ->dehydrated()
                                ->required(),
                            DatePicker::make('purchase_date')
                                ->label('Purchase Date')
                                ->required()
                                ->default(now()),
                            Select::make('supplier_id')
                                ->label('Supplier')
                                ->required()
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload(),

                            TextInput::make('shipping_cost')
                                ->label('Shipping Cost')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->prefix('$')
                                ->live()
                                ->afterStateUpdated(fn ($state, Set $set) => $set('shipping_cost', $state)),

                        ])
                            ->columns(2),
                        Section::make('Attachment')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->directory('attachments/purchases')
                                    ->maxSize(1024),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                ToggleButtons::make('payment_status')
                                    ->inline()
                                    ->options(PaymentStatus::class),
                                ToggleButtons::make('shipping_status')
                                    ->inline()
                                    ->label('Shipping Status')
                                    ->options(ShippingStatus::class)
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
                Group::make()
                    ->schema([
                        Section::make('Purchase Items')

                            ->schema([
                                Repeater::make('items')
                                    ->relationship('items')
                                    ->table([
                                        TableColumn::make('Product Name'),

                                        TableColumn::make('Quantity')
                                            ->width(100),
                                        TableColumn::make('Unit')
                                            ->width(100),
                                        TableColumn::make('Price')
                                            ->width(150),
                                        TableColumn::make('Sub Total')
                                            ->width(150),
                                    ])
                                    ->schema([
                                        Select::make('product_variant_id')
                                            ->options(
                                                fn () => ProductVariant::with('product')
                                                    ->get()
                                                    ->mapWithKeys(fn ($variant) => [
                                                        $variant->id => "{$variant->name} | {$variant->product->name}",
                                                    ])
                                            )
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                $set('unit_price', ProductVariant::find($state)?->price ?? 0);
                                                $variant = ProductVariant::with('product.unit')->find($state);
                                                $set('unit_id', $variant?->product?->unit?->id ?? null);
                                            })
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->searchable(),
                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn ($state, Set $set, $get) => $set('sub_total', $state * $get('unit_price'))),
                                        Select::make('unit_id')
                                            ->options(Unit::pluck('short_name', 'id'))
                                            ->label('Unit')
                                            ->dehydrated()
                                            ->required()
                                            ->live()
                                            ->disabled(),
                                        TextInput::make('unit_price')
                                            ->label('Price')
                                            ->numeric()
                                            ->required()
                                            ->disabled()
                                            ->prefix('$')
                                            ->dehydrated()
                                            ->live()
                                            ->afterStateUpdated(fn ($state, Set $set, $get) => $set('sub_total', $state * $get('quantity'))),

                                        TextInput::make('sub_total')
                                            ->label('Sub Total')
                                            ->numeric()
                                            ->required()
                                            ->prefix('$')
                                            ->disabled()
                                            ->dehydrated()
                                            ->live(),

                                    ])
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $items = $state ?? [];
                                        $subTotal = collect($items)->sum(fn ($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
                                        $shipping = $get('shipping_cost') ?? 0;

                                        $set('items_count', count($items));
                                        $set('total_price', $subTotal);
                                        $set('grand_total', $subTotal + $shipping);
                                    })
                                    ->afterStateHydrated(function ($state, Set $set, Get $get) {
                                        $items = $state ?? [];
                                        $subTotal = collect($items)->sum(fn ($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
                                        $shipping = $get('shipping_cost') ?? 0;

                                        $set('items_count', count($items));
                                        $set('total_price', $subTotal);
                                        $set('grand_total', $subTotal + $shipping);
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),
                Group::make()
                    ->schema([
                        Section::make('Summary')
                            ->schema([
                                TextInput::make('items_count')
                                    ->label('Items Count')
                                    ->numeric()
                                    ->disabled()
                                    ->default(0)
                                    ->dehydrated(),

                                TextInput::make('shipping_cost')
                                    ->label('Shipping Cost')
                                    ->numeric()
                                    ->prefix('$')
                                    ->reactive()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('total_price')
                                    ->label('Total Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->reactive()
                                    ->disabled()
                                    ->default(0)
                                    ->dehydrated(),

                                TextInput::make('grand_total')
                                    ->label('Grand Total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->default(0)
                                    ->reactive()
                                    ->dehydrated(),

                            ])
                            ->columns(4),
                    ])->columnSpanFull(),

            ])
            ->columns(3);
    }
}
