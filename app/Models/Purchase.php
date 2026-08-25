<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'reference',
        'total_price',
        'grand_total',
        'purchase_item_id',
        'supplier_id',
        'payment_status',
        'shipping_cost',
        'shipping_status',
        'purchase_date',
        'image_path',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Purchase $purchase) {
            if ($purchase->shipping_status === ShippingStatus::Delivered) {
                $purchase->items->each(function ($item) {
                    $item->variant?->decrement('stock_qty', $item->quantity);
                });
            }
        });
    }

    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'shipping_status' => ShippingStatus::class,
        'purchase_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
