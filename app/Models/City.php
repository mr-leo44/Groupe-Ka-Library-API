<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name'];

    public function users ()
    {
        return $this->hasMany(User::class);
    }
}
