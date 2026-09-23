<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrders extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    protected static ?string $heading = 'Recent Orders';

    public function table(Table $table): Table
    {

        return $table
            ->query(fn (): Builder => Order::query()
                ->orderByDesc('created_at')
                ->limit(5)
                ->where('payment_status', 'paid')->with('customer')->withCount('items'))

            ->paginated(false)
            ->columns([
                TextColumn::make('#')->label('#')->getStateUsing(fn ($rowLoop) => $rowLoop->iteration)->alignCenter(),
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('items_count')->label('Items')->alignCenter()->badge(),
                TextColumn::make('total_amount')->label('Total')->money('usd', true),
                TextColumn::make('payment_status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Date')->dateTime('M d, Y'),
            ])
            ->filters([])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
