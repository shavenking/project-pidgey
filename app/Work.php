<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $fillable = ['name', 'amount', 'unit_price', 'engineering_type_id'];

    public function engineeringType()
    {
        return $this->belongsTo(EngineeringType::class);
    }
}
