<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductForm
{
    protected static function generateBaseCode(): string
    {
        // e.g. next sequential number based on latest product
        do {
            // random number between 100000 (6 digits) and 9999999 (7 digits)
            $code = (string) random_int(100000, 9999999);
        } while (Product::where('product_code', $code)->exists());

        return $code;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)

            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')->label('Name')->required(),
                        TextInput::make('product_code')
                            ->label('Product Code')
                            ->required()
                            ->suffixAction(
                                Action::make('generateCode')
                                    ->icon('heroicon-m-arrow-path')
                                    ->tooltip('Generate code')
                                    ->action(function (Set $set) {
                                        $set('product_code', self::generateBaseCode());
                                    })
                            ),

                        RichEditor::make('description')->label('Description')->columnSpanFull(),

                    ])
                    ->columnSpan(2)
                    ->columns(2),

                Section::make([
                    Select::make('category_id')->label('Category')->required()
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                    Select::make('unit_id')->label('Unit')->required()
                        ->relationship('unit', 'name'),
                    Toggle::make('is_active')->label('Active')->required(),
                ]),

                Section::make()
                    ->schema([
                        Repeater::make('images')
                            ->label('Product Images')
                            ->relationship('images')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Image')
                                    ->image()
                                    ->directory('product-images')
                                    ->required(),
                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add image'),
                    ])
                    ->columnSpanFull(),

                Section::make()
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                TextInput::make('name')->label('Variant Name')->required(),

                                TextInput::make('price')->label('Price')->numeric()->required()->prefix('$')->minValue(0)->step('0.01'),
                                TextInput::make('cost')->label('Cost')->numeric()->required()->prefix('$')->minValue(0)->step('0.01'),
                                TextInput::make('stock_qty')->label('Stock Quantity')->numeric()->required(),

                                Select::make('is_active')->label('Active')->required()
                                    ->options([
                                        1 => 'Active',
                                        0 => 'Inactive',
                                    ]),

                            ])
                            ->required()
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

            ]);
    }
}
