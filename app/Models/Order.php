<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'logistic_id',
        'total_amount',
        'subtotal_amount',
        'shipping_cost',
        'payment_status',
        'shipping_status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function logistic()
    {
        return $this->belongsTo(Logistic::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payments::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItems::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(OrderAddress::class);
    }

    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'shipping_status' => ShippingStatus::class,

    ];
}
