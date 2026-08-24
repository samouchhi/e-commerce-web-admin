<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ToggleColumn::make('is_active')
                    ->label('Status')
                    ->onIcon(Heroicon::OutlinedCheckBadge)
                    ->offIcon(Heroicon::OutlinedXMark)
                    ->onColor('success')
                    ->offColor('danger'),
                ImageColumn::make('images.image_path')
                    ->label('Image')
                    ->getStateUsing(fn (Product $record) => $record->images->first()?->image_path),
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('category.name')->label('Category')->sortable()->searchable(),

                TextColumn::make('variants.price')->label('Price')->money('USD')->badge()
                    ->getStateUsing((fn (Product $record) => $record->variants->min('price'))),
                TextColumn::make('variants.cost')->label('Cost')->money('USD')->badge()
                    ->getStateUsing((fn (Product $record) => $record->variants->min('cost'))),
                // SelectColumn::make('status')->label('Status')->options(ProductStatusEnum::class)->searchableOptions(),
                TextColumn::make('stock_count')
                    ->label('Quantity')
                    ->state(fn (Product $record) => $record->variants->sum('stock_qty'))
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state < 10 ? 'danger' : 'success'),
                TextColumn::make('created_at')->label('Created At')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Updated At')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(
                [

                    SelectFilter::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name'),
                    Filter::make('created_at')
                        ->schema([
                            DatePicker::make('created_from')->label('Created From'),
                        ])
                        ->query(function ($query, $data) {
                            if ($data['created_from']) {
                                $query->whereDate('created_at', '>=', $data['created_from']);
                            }
                        }),
                    Filter::make('created_until')
                        ->schema([
                            DatePicker::make('created_until')->label('Created Until'),
                        ])
                        ->query(function ($query, $data) {
                            if ($data['created_until']) {
                                $query->whereDate('created_at', '<=', $data['created_until']);
                            }
                        }),

                ],
                layout: FiltersLayout::AboveContent
            )

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make()
                    ->modalHeading('Product Details'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
