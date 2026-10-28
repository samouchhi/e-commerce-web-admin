<?php

namespace App\Filament\Resources\Discounts\Schemas;

use App\Models\Discount;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Discount details'))
                ->schema([
                    TextEntry::make('name')->label(__('Name')),
                    TextEntry::make('description')->label(__('Description'))->placeholder(__('No description')),
                    ImageEntry::make('image_path')->label(__('Promotion image'))->disk('public')->placeholder(__('No image')),
                    TextEntry::make('type')->badge(),
                    TextEntry::make('value')
                        ->numeric(decimalPlaces: 2)
                        ->prefix(fn (Discount $record): string => $record->type === 'fixed' ? '$' : '')
                        ->suffix(fn (Discount $record): string => $record->type === 'percentage' ? '%' : ''),
                    IconEntry::make('is_active')->label(__('Enabled'))->boolean(),
                    TextEntry::make('start_date')->date(),
                    TextEntry::make('end_date')->date(),
                    TextEntry::make('created_at')->label(__('Created at'))->dateTime(),
                    TextEntry::make('updated_at')->label(__('Updated at'))->dateTime(),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make(__('Products'))
                ->schema([
                    RepeatableEntry::make('products')
                        ->hiddenLabel()
                        ->table([
                            TableColumn::make('Product'),
                            TableColumn::make('Product code'),
                        ])
                        ->schema([
                            TextEntry::make('name')->label(__('Name')),
                            TextEntry::make('product_code')->label(__('Product code'))->placeholder(__('No product code')),
                        ])
                        ->placeholder(__('No products')),
                ])
                ->columnSpanFull(),
        ]);
    }
}
