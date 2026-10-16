<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Illuminate\Database\Eloquent\Model;

class Logistic extends Model
{
    use LogsActivityPlus;

    protected $table = 'logistics';

    protected $fillable = [
        'name',
        'description',
        'contact_number',
        'image',
        'price',
    ];
}
