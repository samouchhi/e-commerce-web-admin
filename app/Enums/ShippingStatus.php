<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ShippingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Shipped = 'shipped';

    case Delivered = 'delivered';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Shipped, self::Delivered => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Shipped => Heroicon::Truck,
            self::Delivered => Heroicon::CheckBadge,
            self::Pending => Heroicon::Clock,
        };
    }
}
