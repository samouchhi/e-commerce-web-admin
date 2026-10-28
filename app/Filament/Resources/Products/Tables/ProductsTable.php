<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
                    ->label(__('Status'))
                    ->onIcon(Heroicon::Check)
                    ->offIcon(Heroicon::XMark)
                    ->onColor('success')
                    ->offColor('danger'),
                ImageColumn::make('images.image_path')
                    ->label(__('Image'))
                    ->disk('public')
                    ->getStateUsing(fn (Product $record) => $record->images->first()?->image_path),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('unit.short_name')
                    ->label(__('Unit'))
                    ->sortable()
                    ->searchable()
                    ->badge(),

                TextColumn::make('variants.price')
                    ->label(__('Price'))
                    ->money('USD')
                    ->getStateUsing((fn (Product $record) => $record->variants->min('price'))),
                TextColumn::make('variants.cost')
                    ->label(__('Cost'))
                    ->money('USD')
                    ->getStateUsing((fn (Product $record) => $record->variants->min('cost'))),
                // SelectColumn::make('status')->label(__('Status'))->options(ProductStatusEnum::class)->searchableOptions(),
                TextColumn::make('stock_count')
                    ->label(__('Quantity'))
                    ->state(fn (Product $record) => $record->variants->sum('stock_qty'))
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state < 10 ? 'danger' : 'success'),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters(
                [
                    SelectFilter::make('category_id')
                        ->label(__('Category'))
                        ->relationship('category', 'name'),
                    SelectFilter::make('is_active')
                        ->label(__('Status'))
                        ->options([
                            1 => __('Active'),
                            0 => __('Inactive'),
                        ]),
                    SelectFilter::make('unit_id')
                        ->label(__('Unit'))
                        ->relationship('unit', 'short_name'),
                    // Filter::make('created_at')
                    //     ->schema([
                    //         DatePicker::make('created_from')->label(__('Created From')),
                    //     ])
                    //     ->query(function ($query, $data) {
                    //         if ($data['created_from']) {
                    //             $query->whereDate('created_at', '>=', $data['created_from']);
                    //         }
                    //     }),
                    // Filter::make('created_until')
                    //     ->schema([
                    //         DatePicker::make('created_until')->label(__('Created Until')),
                    //     ])
                    //     ->query(function ($query, $data) {
                    //         if ($data['created_until']) {
                    //             $query->whereDate('created_at', '<=', $data['created_until']);
                    //         }
                    //     }),

                ],

            )

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
