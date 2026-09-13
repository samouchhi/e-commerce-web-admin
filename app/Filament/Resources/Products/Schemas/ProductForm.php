<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ProductForm
{
    protected static function generateBaseCode(): string
    {
        do {
            $code = (string) random_int(100000, 9999999);
        } while (Product::where('product_code', $code)->exists());

        return $code;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Wizard::make([
                    Step::make('Product Details')
                        ->columns(['default' => 1, 'lg' => 3])
                        ->schema([
                            Group::make([
                                Section::make('Product details')
                                    ->icon('heroicon-o-shopping-bag')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Product name')
                                            ->required()
                                            ->columnSpanFull(),
                                        TextInput::make('product_code')
                                            ->label('Product code')
                                            ->required()
                                            ->suffixAction(
                                                Action::make('generateCode')
                                                    ->label('Generate product code')
                                                    ->icon('heroicon-m-arrow-path')
                                                    ->tooltip('Generate product code')
                                                    ->action(function (Set $set): void {
                                                        $set('product_code', self::generateBaseCode());
                                                    })
                                            ),
                                        RichEditor::make('description')
                                            ->label('Description')
                                            ->columnSpanFull(),
                                    ]),

                            ])->columnSpan(['lg' => 2]),
                            Group::make([
                                Section::make('Status')
                                    ->icon('heroicon-o-eye')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Enable')
                                            ->required(),
                                    ])
                                    ->compact(),
                                Section::make('Organization')
                                    ->icon('heroicon-o-tag')
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Category')
                                            ->placeholder('Choose a category')
                                            ->required()
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload(),
                                        Select::make('unit_id')
                                            ->label('Unit of sale')
                                            ->placeholder('Choose a unit')
                                            ->required()
                                            ->relationship('unit', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->compact(),
                            ])->columnSpan(['lg' => 1]),
                        ]),
                    Step::make('Variants & Inventory')
                        ->schema([
                            Section::make('Variants & inventory')
                                ->description('Set the price, cost, and available stock for each option.')
                                ->icon('heroicon-o-cube')
                                ->schema([
                                    Repeater::make('variants')
                                        ->hiddenLabel()
                                        ->relationship('variants')

                                        ->collapsible()
                                        ->addActionLabel('Add variant')
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Variant name')
                                                ->placeholder('e.g. Black / Medium')
                                                ->live(onBlur: true)
                                                ->required()
                                                ->columnSpan(['md' => 2]),
                                            TextInput::make('stock_qty')
                                                ->label('Stock quantity')
                                                ->prefixIcon('heroicon-o-cube')
                                                ->numeric()->required(),
                                            TextInput::make('price')
                                                ->label('Selling price')
                                                ->numeric()->required()->prefix('$')->minValue(0)->step('0.01'),
                                            TextInput::make('cost')
                                                ->label('Unit cost')
                                                ->numeric()->required()->prefix('$')->minValue(0)->step('0.01'),

                                            Toggle::make('is_active')
                                                ->label('Enable')
                                                ->inline(false)
                                                ->required(),
                                        ])
                                        ->required()
                                        ->columns(['default' => 1, 'md' => 3])
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),
                        ]),
                    Step::make('Product Images')
                        ->schema([
                            Section::make('Product images')
                                ->description('Add images to showcase your product.')
                                ->icon('heroicon-o-photo')
                                ->schema([
                                    Repeater::make('product_images_id')
                                        ->hiddenLabel()
                                        ->relationship('images')
                                        ->itemLabel(fn(array $state): string => filled($state['image_path'] ?? null) ? 'Image' : 'New image')
                                        ->collapsible()
                                        ->addActionLabel('Add image')
                                        ->schema([
                                            FileUpload::make('image_path')
                                                ->label('Image')
                                                ->image()
                                                ->disk('public')
                                                ->directory('product-images')
                                                ->required(),
                                            TextInput::make('sort_order')
                                                ->label('Sort order')
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0),
                                        ])
                                        ->required()
                                        ->columns(['default' => 1, 'md' => 2])
                                        ->columnSpanFull(),
                                ])
                                ->columnSpanFull(),

                        ]),
                ])
                    ->submitAction(method_exists($schema->getLivewire(), 'getWizardSubmitAction')
                        ? $schema->getLivewire()->getWizardSubmitAction()
                        : null)
                    ->cancelAction(
                        Action::make('backToProducts')
                            ->label('Back')
                            ->color('gray')
                            ->url(ProductResource::getUrl('index'))
                    )
                    ->previousAction(fn(Action $action): Action => $action->label('Back'))
                    ->contained(false)
                    ->skippable(fn(?Product $record): bool => $record !== null)
                    ->columnSpanFull(),
            ]);
    }
}
