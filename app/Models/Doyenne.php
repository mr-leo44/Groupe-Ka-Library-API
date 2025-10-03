<?php

namespace App\Models;

use App\Models\Clan;
use App\Models\Diocese;
use Illuminate\Database\Eloquent\Model;

class Doyenne extends Model
{
    protected $fillable = ['diocese_id', 'name'];

    public function diocese()
    {
        return $this->belongsTo(Diocese::class);
    }

    public function clans()
    {
        return $this->hasMany(Clan::class);
    }
}
