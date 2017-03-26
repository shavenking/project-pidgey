<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectWorkItem extends Model
{
    protected $fillable = ['project_id', 'unit_id', 'cost_type_id', 'name'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function costType()
    {
        return $this->belongsTo(CostType::class);
    }
}
