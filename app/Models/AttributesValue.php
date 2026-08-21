<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributesValue extends Model
{
    protected $fillable = [
        'value',
        'attribute_id',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_values', 'attribute_value_id', 'product_variant_id');
    }
}
