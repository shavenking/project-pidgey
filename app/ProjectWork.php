<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectWork extends Model
{
    protected $fillable = ['name', 'amount', 'unit_price', 'engineering_type_id', 'project_id'];

    public function engineeringType()
    {
        return $this->belongsTo(EngineeringType::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
