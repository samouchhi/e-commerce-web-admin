@php
    $product = $getRecord()->product;
    $imagePath = $product?->images->first()?->image_path;
@endphp

<div class="flex items-center gap-2 py-3">
    @if ($imagePath)
        <img src="{{ Storage::disk('public')->url($imagePath) }}" alt="" class="h-10 w-16 shrink-0 object-cover">
    @endif

    <span>{{ $product?->name }}</span>
</div>
