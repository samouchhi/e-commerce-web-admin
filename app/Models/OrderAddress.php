<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    use LogsActivityPlus;

    protected $fillable = [
        'order_id',
        'address',
        'phone',
        'city',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
