<x-filament-widgets::widget>
    <x-filament::section heading="Order Status">
        <div class="@container">
            <div class="flex flex-col items-center gap-6 py-2 @lg:flex-row @lg:justify-around @lg:gap-8">
            <div class="relative size-48 shrink-0 sm:size-52" role="img" aria-label="{{ number_format($total) }} orders by payment status">
                <svg class="size-full" viewBox="0 0 100 100" aria-hidden="true">
                    <circle cx="50" cy="50" r="42" fill="none" stroke="#e5e7eb" stroke-width="16" />
                    @foreach ($statuses as $status)
                        @if ($status['count'] > 0)
                            <circle
                                cx="50"
                                cy="50"
                                r="42"
                                fill="none"
                                stroke="{{ $status['color'] }}"
                                stroke-width="16"
                                stroke-dasharray="{{ $status['segment'] }} {{ 100 - $status['segment'] }}"
                                stroke-dashoffset="{{ $status['offset'] }}"
                                pathLength="100"
                                transform="rotate(-90 50 50)"
                            />
                        @endif
                    @endforeach
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold tracking-tight text-gray-950 tabular-nums dark:text-white">{{ number_format($total) }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Orders</span>
                </div>
            </div>

            <div class="w-full max-w-sm" aria-label="Order status breakdown">
                <div class="grid grid-cols-[minmax(0,1fr)_auto_auto] gap-x-4 border-b border-gray-100 pb-2 text-xs font-medium text-gray-500 dark:border-white/10 dark:text-gray-400">
                    <span>Status</span>
                    <span class="text-right">Orders</span>
                    <span class="text-right">Share</span>
                </div>
                @foreach ($statuses as $status)
                    <div class="grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-x-4 border-b border-gray-100 py-3 text-sm last:border-b-0 dark:border-white/10">
                        <span class="flex min-w-0 items-center gap-2.5 font-medium text-gray-800 dark:text-gray-200">
                            <span class="size-2.5 shrink-0 rounded-full {{ $status['dot'] }}" aria-hidden="true"></span>
                            <span class="truncate">{{ $status['label'] }}</span>
                        </span>
                        <span class="text-right font-semibold text-gray-950 tabular-nums dark:text-white">{{ number_format($status['count']) }}</span>
                        <span class="min-w-12 text-right text-gray-500 tabular-nums dark:text-gray-400">{{ number_format($status['percentage'], 1) }}%</span>
                    </div>
                @endforeach
            </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
