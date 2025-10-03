<?php

namespace App\Models;

use App\Models\User;
use App\Models\Doyenne;
use Illuminate\Database\Eloquent\Model;

class Clan extends Model
{
    protected $fillable = ['doyenne_id', 'name'];

    public function doyenne()
    {
        return $this->belongsTo(Doyenne::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
