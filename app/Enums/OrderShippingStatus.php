<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderShippingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';

    case Shipped = 'shipped';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Shipped => __('Shipped'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Shipped => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::Clock,
            self::Shipped => Heroicon::Truck,
        };
    }
}
