<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    protected function getMaxAttempts(): int
    {
        return 100; // default is 5
    }
}
