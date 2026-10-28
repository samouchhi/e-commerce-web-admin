<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\ManageSuppliers;
use App\Models\Supplier;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class SupplierResource extends Resource
{
    public static function getModelLabel(): string
    {
        return __('Supplier');
    }

    public static function getPluralModelLabel(): string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('Suppliers');
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

    protected static ?string $model = Supplier::class;

    protected static string|UnitEnum|null $navigationGroup = 'Peoples';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-s-user';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label(__('Name'))
                    ->prefixIcon('heroicon-o-user')
                    ->label(__('Name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email address'))
                    ->prefixIcon('heroicon-o-envelope')
                    ->email()
                    ->required(),
                TextInput::make('phone')->label(__('Phone'))
                    ->prefixIcon('heroicon-o-phone')
                    ->tel()
                    ->required(),
                TextInput::make('address')->label(__('Address'))
                    ->prefixIcon('heroicon-o-map')
                    ->required(),
                TextInput::make('city')->label(__('City'))
                    ->prefixIcon('heroicon-o-building-office')
                    ->required(),
                // Toggle::make('is_active')
                //     ->label(__('Enable')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ToggleColumn::make('is_active')
                //     ->label(__('Status'))
                //     ->onIcon(Heroicon::OutlinedCheckBadge)
                //     ->offIcon(Heroicon::OutlinedXMark)
                //     ->onColor('success')
                //     ->offColor('danger'),
                TextColumn::make('name')->label(__('Name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email address'))
                    ->searchable(),
                TextColumn::make('phone')->label(__('Phone'))
                    ->searchable(),
                TextColumn::make('address')->label(__('Address'))
                    ->searchable(),
                TextColumn::make('city')->label(__('City'))
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
            'index' => ManageSuppliers::route('/'),
        ];
    }
}
