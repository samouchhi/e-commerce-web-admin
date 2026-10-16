<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use LogsActivityPlus;

    protected $fillable = [
        'title',
        'image',
        'sort_order',
    ];
}
