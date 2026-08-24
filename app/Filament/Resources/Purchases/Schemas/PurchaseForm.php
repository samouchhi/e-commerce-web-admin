<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    protected static function updateLineTotals(Get $get, Set $set): void
    {
        $quantity = (float) ($get('quantity') ?: 0);
        $unitCost = (float) ($get('unit_cost') ?: 0);

        $set('subtotal', number_format($quantity * $unitCost, 2, '.', ''));
    }

    protected static function updatePurchaseTotal(Get $get, Set $set): void
    {
        $items = collect($get('items') ?? []);

        $total = $items->sum(function (array $item): float {
            return (float) ($item['subtotal'] ?? 0);
        });

        $set('total_price', number_format($total, 2, '.', ''));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    TextInput::make('reference')->label('Reference')->required(),
                    DatePicker::make('date')->label('Date')->required()->default(now()),
                    Select::make('supplier_id')->label('Supplier')->required()
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('payment_status')->label('Payment Status')->required()
                        ->options([
                            'paid' => 'Paid',
                            'unpaid' => 'Unpaid',
                            'partial' => 'Partial',
                        ]),

                    TextInput::make('total_price')
                        ->label('Total')
                        ->numeric()
                        ->prefix('$')
                        ->readOnly()
                        ->default(0)
                        ->dehydrated(),

                    Repeater::make('items')
                        ->label('Purchase Items')
                        ->relationship('items')
                        ->required()
                        ->defaultItems(1)
                        ->columns(12)
                        ->reorderable(false)
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::updatePurchaseTotal($get, $set))
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(fn () => Product::query()->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->dehydrated(false)
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('product_variant_id', null);
                                    $set('unit_cost', 0);
                                    $set('subtotal', 0);
                                })
                                ->columnSpan(4),

                            Select::make('product_variant_id')
                                ->label('Variant (Code + Stock)')
                                ->required()
                                ->searchable()
                                ->options(function (Get $get): array {
                                    $productId = $get('product_id');

                                    if (! $productId) {
                                        return [];
                                    }

                                    return ProductVariant::query()
                                        ->where('product_id', $productId)
                                        ->with('product:id,product_code,name')
                                        ->get()
                                        ->mapWithKeys(function (ProductVariant $variant): array {
                                            $label = sprintf(
                                                '%s | %s | %s | Stock: %d',
                                                $variant->product?->product_code ?? '-',
                                                $variant->product?->name ?? '-',
                                                $variant->name,
                                                (int) $variant->stock_qty,
                                            );

                                            return [$variant->id => $label];
                                        })
                                        ->all();
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                    if (! $state) {
                                        $set('unit_cost', 0);
                                        $set('subtotal', 0);
                                        self::updatePurchaseTotal($get, $set);

                                        return;
                                    }

                                    $variant = ProductVariant::query()->find($state);
                                    $set('unit_cost', $variant?->cost ?? 0);
                                    self::updateLineTotals($get, $set);
                                    self::updatePurchaseTotal($get, $set);
                                })
                                ->columnSpan(4),

                            TextInput::make('quantity')
                                ->label('Qty')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set): void {
                                    self::updateLineTotals($get, $set);
                                    self::updatePurchaseTotal($get, $set);
                                })
                                ->columnSpan(1),

                            TextInput::make('unit_cost')
                                ->label('Unit Cost')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->readOnly()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set): void {
                                    self::updateLineTotals($get, $set);
                                    self::updatePurchaseTotal($get, $set);
                                })
                                ->columnSpan(1),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->readOnly()
                                ->default(0)
                                ->columnSpan(2),
                        ])
                        ->columnSpanFull(),

                    DatePicker::make('purchase_status')
                        ->label('Received Date')
                        ->default(now())
                        ->required(),

                    TextInput::make('shipping_cost')
                        ->label('Shipping Cost')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->prefix('$'),

                    Select::make('shipping_status')
                        ->label('Shipping Status')
                        ->required()
                        ->default('pending')
                        ->options([
                            'pending' => 'Pending',
                            'shipped' => 'Shipped',
                            'received' => 'Received',
                        ]),

                ])
                    ->label('Purchase Detail')
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
