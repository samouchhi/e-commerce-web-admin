<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm {
        mountHasFilters as initializeFilters;
    }
    use HasPageShield;

    public function mountHasFilters(): void
    {
        $this->initializeFilters();
        $this->filters['end_date'] ??= now()->toDateString();
        $this->filters['start_date'] ??= Carbon::parse($this->filters['end_date'])->subDays(6)->toDateString();
        $this->filters['date_range'] = 'custom';
        foreach ($this->getDateRanges() as $preset => [$start, $end]) {
            if ($this->filters['start_date'] === $start && $this->filters['end_date'] === $end) {
                $this->filters['date_range'] = $preset;
                break;
            }
        }
        $this->getFiltersForm()->fill($this->filters);
        $this->updatedFilters();
    }

    public function getHeading(): string
    {
        return 'Dashboard';
    }

    public function getPageClasses(): array
    {
        return [...parent::getPageClasses(), 'store-dashboard'];
    }

    // public function getSubheading(): string
    // {
    //     return 'Sales, payments and inventory at a glance.';
    // }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('orders')
                ->label('View orders')
                ->color('gray')
                ->icon('heroicon-o-shopping-bag')
                ->url(OrderResource::getUrl())
                ->visible(fn(): bool => OrderResource::canViewAny()),
            Action::make('product')
                ->label('Add product')

                ->url(ProductResource::getUrl('create'))
                ->visible(fn(): bool => ProductResource::canCreate()),
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->compact()
                    ->extraAttributes(['class' => 'dashboard-filters'])
                    ->schema([
                        Hidden::make('start_date'),
                        Hidden::make('end_date'),
                        ViewField::make('date_range')
                            ->label('Date range')
                            ->view('filament.forms.date-range', fn(): array => ['ranges' => $this->getDateRanges()]),

                        Select::make('product_id')
                            ->label('Product')
                            ->placeholder('All products')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function applyDateRange(string $start, string $end): void
    {
        Validator::make(['start' => $start, 'end' => $end], [
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
        ])->validate();

        $this->filters['start_date'] = $start;
        $this->filters['end_date'] = $end;
        $this->filters['date_range'] = 'custom';
        foreach ($this->getDateRanges() as $preset => $range) {
            if ($range === [$start, $end]) {
                $this->filters['date_range'] = $preset;
                break;
            }
        }
        $this->updatedFilters();
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 6,
        ];
    }

    /** @return array<string, array{0: string, 1: string}> */
    protected function getDateRanges(): array
    {
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay()->toDateString();
        $lastMonth = $today->copy()->subMonthNoOverflow();

        return [
            'today' => [$today->toDateString(), $today->toDateString()],
            'yesterday' => [$yesterday, $yesterday],
            'last_7_days' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
            'last_30_days' => [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
            'this_month' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'last_month' => [$lastMonth->copy()->startOfMonth()->toDateString(), $lastMonth->copy()->endOfMonth()->toDateString()],
        ];
    }
}
