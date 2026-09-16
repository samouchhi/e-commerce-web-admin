<?php

namespace App\Services;

use App\Models\PaymentMethod;

class TelegramService
{
    /**
     * Create a new class instance.
     */
    public function settings(): ?PaymentMethod
    {
        return PaymentMethod::query()->first();
    }

    public function token(): ?string
    {
        return $this->settings()?->bot_token;
    }

    public function chatId(): ?string
    {
        return $this->settings()?->bot_chat_id;
    }
}
