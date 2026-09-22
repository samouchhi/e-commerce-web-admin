<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    //

    protected $fillable = [
        'name',
        'description',
        'value',
        'type',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_product');
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active
            && $this->start_date->lte(today())
            && $this->end_date->gte(today());
    }

    public function priceAfterDiscount(float $price): float
    {
        $discounted = $this->type === 'percentage'
            ? $price - ($price * (float) $this->value / 100)
            : $price - (float) $this->value;

        return round(max($discounted, 0), 2);
    }
}
