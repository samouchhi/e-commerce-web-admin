<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use LogsActivityPlus;

    protected $fillable = [
        'name',
        'short_name',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
