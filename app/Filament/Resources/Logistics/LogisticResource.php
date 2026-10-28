<?php

namespace App\Filament\Resources\Logistics;

use App\Filament\Resources\Logistics\Pages\ManageLogistics;
use App\Models\Logistic;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class LogisticResource extends Resource
{
    public static function getModelLabel(): string
    {
        return __('Logistic');
    }

    public static function getPluralModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('Logistics');
    }

    protected static ?string $model = Logistic::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-s-truck';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label(__('Name'))
                    ->prefixIcon('heroicon-o-user')
                    ->required(),
                TextInput::make('description')->label(__('Description'))
                    ->prefixIcon('heroicon-o-document-text'),
                TextInput::make('contact_number')
                    ->prefixIcon('heroicon-o-phone'),
                TextInput::make('price')->label(__('Price'))
                    ->numeric()
                    ->required()
                    ->prefixIcon('heroicon-o-currency-dollar'),
                FileUpload::make('image')
                    ->image()
                    ->directory('logistics-images')
                    ->disk('public'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->searchable()
                    ->disk('public'),
                TextColumn::make('name')->label(__('Name'))
                    ->searchable(),
                TextColumn::make('price')->label(__('Price'))
                    ->money('usd', true)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')->label(__('Description'))
                    ->searchable(),
                TextColumn::make('contact_number')
                    ->searchable(),

                TextColumn::make('created_at')->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
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

    public static function getPages(): array
    {
        return [
            'index' => ManageLogistics::route('/'),
        ];
    }
}
