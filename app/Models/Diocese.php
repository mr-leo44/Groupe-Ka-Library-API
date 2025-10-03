<?php

namespace App\Models;

use App\Models\Doyenne;
use Illuminate\Database\Eloquent\Model;

class Diocese extends Model
{
    protected $fillable = ['name'];

    public function doyennes()
    {
        return $this->hasMany(Doyenne::class);
    }
}
