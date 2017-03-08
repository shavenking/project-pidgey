<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkItem extends Model
{
    protected $fillable = ['name', 'unit_id', 'cost_type_id'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function costType()
    {
        return $this->belongsTo(CostType::class);
    }
}
