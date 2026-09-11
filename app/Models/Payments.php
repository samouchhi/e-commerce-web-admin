<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payments extends Model
{
    protected $casts = [
        'amount' => 'decimal:2',
        'qr_expiration_at' => 'datetime',
        'qr_paid_at' => 'datetime',
        'aba_context' => 'encrypted:array',
    ];

    protected $hidden = ['aba_context', 'qr_string'];

    protected $attributes = ['status' => 'pending'];

    protected $fillable = [
        'order_id',
        'currency',
        'amount',
        'status',
        'md5_hash',
        'qr_expiration_at',
        'qr_paid_at',
        'qr_string',
        'aba_transaction_id',
        'aba_context',
        'aba_last_action',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
