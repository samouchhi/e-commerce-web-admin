<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Order;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
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

    /** @var array<string, mixed> | null */
    public ?array $filters = null;

    public function mountHasFilters(): void
    {
        $this->initializeFilters();
        unset($this->filters['product_id']);
        if (($this->filters['date_range'] ?? null) === 'all_time') {
            [$this->filters['start_date'], $this->filters['end_date']] = $this->getDateRanges()['all_time'];
            $this->getFiltersForm()->fill($this->filters);
            $this->updatedFilters();

            return;
        }
        $this->filters['end_date'] ??= now()->toDateString();
        $this->filters['start_date'] ??= Carbon::parse($this->filters['end_date'])->subDays(6)->toDateString();
        $this->filters['date_range'] = 'custom';
        foreach ($this->getDateRanges() as $preset => [$start, $end]) {
            if ($preset !== 'all_time' && $this->filters['start_date'] === $start && $this->filters['end_date'] === $end) {
                $this->filters['date_range'] = $preset;
                break;
            }
        }
        $this->getFiltersForm()->fill($this->filters);
        $this->updatedFilters();
    }

    public function getHeading(): string
    {
        return __('Dashboard');
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
                ->label(__('View orders'))
                ->color('gray')
                ->icon('heroicon-o-shopping-bag')
                ->url(OrderResource::getUrl())
                ->visible(fn (): bool => OrderResource::canViewAny()),
            Action::make('product')
                ->label(__('Add product'))

                ->url(ProductResource::getUrl('create'))
                ->visible(fn (): bool => ProductResource::canCreate()),
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
                            ->label(__('Date range'))
                            ->hiddenLabel()
                            ->view('filament.forms.date-range', fn (): array => ['ranges' => $this->getDateRanges()]),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    public function applyDateRange(string $start, string $end, bool $allTime = false): void
    {
        Validator::make(['start' => $start, 'end' => $end], [
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
        ])->validate();

        $this->filters['start_date'] = $start;
        $this->filters['end_date'] = $end;
        $this->filters['date_range'] = 'custom';
        foreach ($this->getDateRanges() as $preset => $range) {
            if ($preset !== 'all_time' && $range === [$start, $end]) {
                $this->filters['date_range'] = $preset;
                break;
            }
        }
        if ($allTime) {
            $this->filters['date_range'] = 'all_time';
        }
        $this->updatedFilters();
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 12,
        ];
    }

    /** @return array<string, array{0: string, 1: string}> */
    protected function getDateRanges(): array
    {
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay()->toDateString();
        $lastMonth = $today->copy()->subMonthNoOverflow();
        $firstOrder = Order::query()->min('created_at');
        $lastOrder = Order::query()->max('created_at');

        return [
            'all_time' => [
                Carbon::parse($firstOrder ?? $today)->min($today)->toDateString(),
                Carbon::parse($lastOrder ?? $today)->max($today)->toDateString(),
            ],
            'today' => [$today->toDateString(), $today->toDateString()],
            'yesterday' => [$yesterday, $yesterday],
            'last_7_days' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
            'last_30_days' => [$today->copy()->subDays(29)->toDateString(), $today->toDateString()],
            'this_month' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            'last_month' => [$lastMonth->copy()->startOfMonth()->toDateString(), $lastMonth->copy()->endOfMonth()->toDateString()],
        ];
    }
}
