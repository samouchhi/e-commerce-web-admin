<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    protected $casts = [
        'qr_expiration_at' => 'datetime',
        'qr_paid_at' => 'datetime',
    ];

    protected $fillable = [
        'order_id',
        'currency',
        'amount',
        'md5_hash',
        'qr_expiration_at',
        'qr_paid_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
