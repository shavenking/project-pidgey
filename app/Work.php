<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    protected $fillable = ['user_id', 'name', 'amount', 'unit_price', 'engineering_type_id'];

    public function workItems()
    {
        return $this->belongsToMany(WorkItem::class)->withPivot('amount', 'unit_price')->withTimestamps();
    }

    public function engineeringType()
    {
        return $this->belongsTo(EngineeringType::class);
    }
}
