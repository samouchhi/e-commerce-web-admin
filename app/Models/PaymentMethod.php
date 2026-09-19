<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    //

    protected $fillable = [
        'aba_payway_link',
        'bot_token',
        'bot_chat_id',
    ];
}
