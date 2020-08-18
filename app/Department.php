<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $guarded = [
    ];

    public function buildings(){
        return $this->hasMany('App\Building','department_id');
    }
}
