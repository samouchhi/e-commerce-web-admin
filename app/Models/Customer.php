<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password'])]
class Customer extends Model
{
    use HasApiTokens;

    protected $table = 'customers';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
