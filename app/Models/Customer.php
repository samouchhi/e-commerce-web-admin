<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password'])]
class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
