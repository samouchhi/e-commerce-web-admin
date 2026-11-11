@php
    $product = $getRecord()->product;
    $imagePath = $product?->images->first()?->image_path;
@endphp

<div class="dashboard-product-cell flex items-center gap-3 py-3">
    <div class="relative flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
        <x-filament::icon icon="heroicon-o-cube" class="size-5 text-gray-400" />
        @if ($imagePath)
            <img src="{{ Storage::disk('public')->url($imagePath) }}" alt="" loading="lazy"
                x-data x-on:error="$el.remove()" class="absolute inset-0 size-full object-cover">
        @endif
    </div>
    <div class="min-w-0">
        <span class="block text-sm font-medium">{{ $product?->name }}</span>
        <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $getRecord()->name }}</span>
    </div>
</div>
