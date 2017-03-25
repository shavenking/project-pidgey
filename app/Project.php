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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
