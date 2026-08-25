<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Partial = 'partial';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Partial => 'Partial',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'info',
            self::Paid => 'success',
            self::Partial => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::Clock,
            self::Paid => Heroicon::CheckCircle,
            self::Partial => Heroicon::ExclamationTriangle,
        };
    }
}
