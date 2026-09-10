<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
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
