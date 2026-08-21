<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'name',
        'product_id',
        'sku',
        'price',
        'cost',
        'stock_qty',
        'unit_id',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function attributeValues()
    {
        return $this->belongsToMany(AttributesValue::class, 'product_variant_values', 'product_variant_id', 'attribute_value_id');
    }
}
