<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EngineeringType extends Model
{
    public $timestamps = false;

    protected $fillable = ['main_title', 'detailing_title'];
}
