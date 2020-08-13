<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stiker_category extends Model
{
    protected $guarded = [
    ];
    public function stikers(){
        return $this->hasMany('App\Stiker','category_id');
    }
}
