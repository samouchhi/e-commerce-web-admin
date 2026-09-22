<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\ManageSuppliers;
use App\Models\Supplier;
use BackedEnum;
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
                TextInput::make('name')
                    ->prefixIcon('heroicon-o-user')
                    ->label('Name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->prefixIcon('heroicon-o-envelope')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->prefixIcon('heroicon-o-phone')
                    ->tel()
                    ->required(),
                TextInput::make('address')
                    ->prefixIcon('heroicon-o-map')
                    ->required(),
                TextInput::make('city')
                    ->prefixIcon('heroicon-o-building-office')
                    ->required(),
                // Toggle::make('is_active')
                //     ->label('Enable'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ToggleColumn::make('is_active')
                //     ->label('Status')
                //     ->onIcon(Heroicon::OutlinedCheckBadge)
                //     ->offIcon(Heroicon::OutlinedXMark)
                //     ->onColor('success')
                //     ->offColor('danger'),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
