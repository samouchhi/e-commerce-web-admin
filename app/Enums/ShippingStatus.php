<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ShippingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Processing = 'processing';

    case Shipped = 'shipped';

    case Delivered = 'delivered';

    public function getLabel(): string
    {
        return match ($this) {
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Processing => 'warning',
            self::Shipped, self::Delivered => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Processing => Heroicon::ArrowPath,
            self::Shipped => Heroicon::Truck,
            self::Delivered => Heroicon::CheckBadge,
        };
    }
}
