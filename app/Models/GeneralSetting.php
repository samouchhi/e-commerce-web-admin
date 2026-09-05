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
        'site_description',
        'site_facebook_url',
        'site_twitter_url',
        'site_instagram_url',
        'site_linkedin_url',
        'site_youtube_url',
        'site_telegram_url',
    ];
}
