<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use LogsActivityPlus;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'is_active',
    ];
}
