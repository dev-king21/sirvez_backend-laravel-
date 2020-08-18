<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $guarded = [
    ];

    public function floors(){
        return $this->hasMany('App\Floor','building_id');
    }
}
