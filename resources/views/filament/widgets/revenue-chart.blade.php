<x-filament-widgets::widget class="fi-wi-chart dashboard-revenue-chart">
    <x-filament::section :heading="$this->getHeading()">
        <div class="dashboard-revenue-summary">
            <div>
                <p class="dashboard-revenue-total">{{ $this->getTotalRevenue() }}</p>
            </div>
            <div class="dashboard-revenue-period">
                <span class="dashboard-revenue-legend">{{ __('Paid revenue') }}</span>
                <span>{{ $this->getPeriodLabel() }}</span>
            </div>
        </div>

        <div wire:poll.5s="updateChartData">
            <div
                x-load
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                wire:ignore
                data-chart-type="bar"
                x-data="chart({
                    cachedData: @js($this->getCachedData()),
                    options: @js($this->getOptions()),
                    type: 'bar',
                })"
                class="fi-wi-chart-frame fi-wi-chart-canvas-ctn fi-wi-chart-frame-no-aspect-ratio dashboard-revenue-plot"
            >
                <canvas x-ref="canvas" role="img" aria-label="Revenue over time: daily paid revenue in USD. Period and total are shown above the chart."></canvas>
                <span x-ref="backgroundColorElement" class="fi-wi-chart-bg-color"></span>
                <span x-ref="borderColorElement" class="fi-wi-chart-border-color"></span>
                <span x-ref="gridColorElement" class="fi-wi-chart-grid-color"></span>
                <span x-ref="textColorElement" class="fi-wi-chart-text-color"></span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
