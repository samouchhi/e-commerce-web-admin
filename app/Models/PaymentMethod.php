<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use LogsActivityPlus;

    /** @return list<string> */
    protected function activityExcept(): array
    {
        return ['bot_token'];
    }

    //

    protected $fillable = [
        'aba_payway_link',
        'bot_token',
        'bot_chat_id',
    ];
}
