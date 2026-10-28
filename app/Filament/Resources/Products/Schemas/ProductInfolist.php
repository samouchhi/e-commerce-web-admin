<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Product details'))
                    ->schema([
                        TextEntry::make('name')->label(__('Product name')),
                        TextEntry::make('product_code')->label(__('Product code')),
                        TextEntry::make('category.name')->label(__('Name'))->placeholder(__('No category')),
                        TextEntry::make('unit.name')->label(__('Unit'))->placeholder(__('No unit')),
                        IconEntry::make('is_active')->label(__('Active'))->boolean(),
                        IconEntry::make('is_best_seller')->label(__('Best seller'))->boolean(),
                        TextEntry::make('description')->label(__('Description'))
                            ->html()
                            ->placeholder(__('No description'))
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'sm' => 2]),
                Section::make(__('Product variants'))
                    ->schema([
                        RepeatableEntry::make('variants')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Name'),
                                TableColumn::make('Stock'),
                                TableColumn::make('Selling price'),
                                TableColumn::make('Unit cost'),
                                TableColumn::make('Active'),
                            ])
                            ->schema([
                                TextEntry::make('name')->label(__('Name')),
                                TextEntry::make('stock_qty')->numeric(),
                                TextEntry::make('price')->label(__('Price'))->money('USD'),
                                TextEntry::make('cost')->label(__('Cost'))->money('USD'),
                                IconEntry::make('is_active')->boolean(),
                            ]),
                    ]),
                Section::make(__('Images'))
                    ->schema([
                        ImageEntry::make('images.image_path')
                            ->hiddenLabel()
                            ->disk('public')
                            ->imageSize(120),
                    ]),
            ]);
    }
}
