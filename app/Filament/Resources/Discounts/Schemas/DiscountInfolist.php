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
            Section::make('Discount details')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('description')->placeholder('No description'),
                    ImageEntry::make('image_path')->label('Promotion image')->disk('public')->placeholder('No image'),
                    TextEntry::make('type')->badge(),
                    TextEntry::make('value')
                        ->numeric(decimalPlaces: 2)
                        ->prefix(fn (Discount $record): string => $record->type === 'fixed' ? '$' : '')
                        ->suffix(fn (Discount $record): string => $record->type === 'percentage' ? '%' : ''),
                    IconEntry::make('is_active')->label('Enabled')->boolean(),
                    TextEntry::make('start_date')->date(),
                    TextEntry::make('end_date')->date(),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('updated_at')->dateTime(),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Products')
                ->schema([
                    RepeatableEntry::make('products')
                        ->hiddenLabel()
                        ->table([
                            TableColumn::make('Product'),
                            TableColumn::make('Product code'),
                        ])
                        ->schema([
                            TextEntry::make('name'),
                            TextEntry::make('product_code')->placeholder('No product code'),
                        ])
                        ->placeholder('No products'),
                ])
                ->columnSpanFull(),
        ]);
    }
}
