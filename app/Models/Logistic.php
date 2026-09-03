<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logistic extends Model
{
    protected $table = 'logistics';

    protected $fillable = [
        'name',
        'description',
        'contact_number',
        'image',
        'price',
    ];
}
