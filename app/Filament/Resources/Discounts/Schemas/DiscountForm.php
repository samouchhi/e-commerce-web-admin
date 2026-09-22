<?php

namespace App\Filament\Resources\Discounts\Schemas;

use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->prefixIcon('heroicon-o-tag')
                    ->required(),
                TextInput::make('description')
                    ->prefixIcon('heroicon-o-document-text')
                    ->required(),

                TextInput::make('value')
                    ->prefixIcon(
                        fn (Get $get): string => $get('type') === 'percentage'
                            ? 'heroicon-o-percent-badge'
                            : 'heroicon-o-currency-dollar'
                    )
                    ->required()
                    ->numeric(),

                Select::make('type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed',
                    ])
                    ->live()
                    ->searchable()
                    ->required(),
                DatePicker::make('start_date')
                    ->prefixIcon('heroicon-o-calendar')
                    ->required(),
                DatePicker::make('end_date')
                    ->prefixIcon('heroicon-o-calendar')
                    ->required(),

                ModalTableSelect::make('products')
                    ->label('Choose Products')
                    ->relationship('products', 'name')
                    ->multiple()
                    ->tableConfiguration(ProductsTable::class)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Enable')
                    ->required(),
                // Select::make('products')
                //     ->label('Products')
                //     ->relationship('products', 'name')
                //     ->multiple()
                //     ->searchable()
                //     ->preload()
                //     ->live()
                //     ->required(),

                RepeatableEntry::make('selectedProducts')
                    ->hiddenLabel()
                    ->state(fn (Get $get) => Product::query()
                        ->whereIn('id', $get('products') ?? [])
                        ->get())
                    ->table([
                        TableColumn::make('Product'),
                        TableColumn::make('Product code'),
                    ])
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('product_code'),

                    ])
                    ->contained(false)
                    ->visible(fn (Get $get): bool => filled($get('products')))
                    ->columnSpanFull(),

            ]);
    }
}
