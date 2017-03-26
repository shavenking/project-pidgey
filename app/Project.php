<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name'];

    public function works()
    {
        return $this->hasMany(ProjectWork::class);
    }

    public function workItems()
    {
        return $this->hasMany(ProjectWorkItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
