<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use App\Models\Attribute;
use App\Models\AttributesValue;
use Dom\Text;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Metadata\Repeat;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    TextInput::make('name')->label('Name')->required(),
                    Select::make('category_id')->label('Category')->required()
                        ->relationship('category', 'name'),
                    TextInput::make('cost')->label('Cost')->numeric()->required()->prefix('$'),
                    TextInput::make('price')->label('Price')->numeric()->required()->prefix('$'),
                    Select::make('unit_id')->label('Unit')->required()
                        ->relationship('unit', 'name')

                ])
                    ->label('Product Detail')
                    ->columns(3)
                    ->columnSpanFull(),
                Repeater::make('variants')
                    ->label('Variants')
                    ->relationship('variants')

                    ->schema([
                        TextInput::make('name')->label('Variant Name')->required(),
                        TextInput::make('sku')->label('SKU')->required(),
                        TextInput::make('price')->label('Price')->numeric()->required()->prefix('$'),
                        TextInput::make('cost')->label('Cost')->numeric()->required()->prefix('$'),
                        TextInput::make('stock_qty')->label('Stock Quantity')->numeric()->required(),
                        Select::make('attribute_id')
                            ->label('Attribute')
                            ->options(Attribute::pluck('name', 'id'))
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('attributeValues', null))
                            ->afterStateHydrated(function (Select $component, $record) {
                                $attributeValue = $record?->attributeValues()->first();
                                $component->state($attributeValue?->attribute_id);
                            })
                            ->dehydrated(false),


                        Select::make('attributeValues')
                            ->label('Value')
                            ->relationship(
                                name: 'attributeValues',
                                titleAttribute: 'value',
                                modifyQueryUsing: fn(Builder $query, Get $get) => $query->where('attribute_id', $get('attribute_id')),
                            )
                            ->disabled(fn(Get $get) => blank($get('attribute_id')))
                            ->required()
                            ->live(),
                        Select::make('is_active')->label('Active')->options([
                            1 => 'Active',
                            0 => 'Inactive',
                        ])->required(),
                    ])

                    ->columns(3)
                    ->columnSpanFull()




            ]);
    }
}
