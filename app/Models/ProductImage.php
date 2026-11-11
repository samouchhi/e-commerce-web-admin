<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use LogsActivityPlus;

    protected $fillable = [
        'product_id',
        'image_path',
        'sort_order',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
