<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_logo',
        'site_favicon',
        'site_email',
        'site_phone',
        'site_address',
    ];
}
