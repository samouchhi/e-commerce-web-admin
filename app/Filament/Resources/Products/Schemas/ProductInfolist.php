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
                Section::make('Product details')
                    ->schema([
                        TextEntry::make('name')->label('Product name'),
                        TextEntry::make('product_code')->label('Product code'),
                        TextEntry::make('category.name')->placeholder('No category'),
                        TextEntry::make('unit.name')->label('Unit')->placeholder('No unit'),
                        IconEntry::make('is_active')->label('Active')->boolean(),
                        IconEntry::make('is_best_seller')->label('Best seller')->boolean(),
                        TextEntry::make('description')
                            ->html()
                            ->placeholder('No description')
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'sm' => 2]),
                Section::make('Product variants')
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
                                TextEntry::make('name'),
                                TextEntry::make('stock_qty')->numeric(),
                                TextEntry::make('price')->money('USD'),
                                TextEntry::make('cost')->money('USD'),
                                IconEntry::make('is_active')->boolean(),
                            ]),
                    ]),
                Section::make('Images')
                    ->schema([
                        ImageEntry::make('images.image_path')
                            ->hiddenLabel()
                            ->disk('public')
                            ->imageSize(120),
                    ]),
            ]);
    }
}
