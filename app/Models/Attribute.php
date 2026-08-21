<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'name',

    ];
    public function attributeValues()
    {
        return $this->hasMany(AttributesValue::class);
    }
}
