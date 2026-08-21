<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatusEnum: string implements HasLabel, HasColor
{
    case IN_STOCK = 'In Stock';
    case OUT_OF_STOCK = 'Out of Stock';
    case COMING_SOON = 'Coming Soon';

    public function getlabel(): string
    {
        return $this->value;
    }
    public function getColor(): string
    {
        return match ($this) {
            self::IN_STOCK => 'success',
            self::OUT_OF_STOCK => 'danger',
            self::COMING_SOON => 'warning',
        };
    }
}
