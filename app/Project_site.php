<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project_site extends Model
{
    protected $guarded = [
    ];
    public function rooms(){
        return $this->hasMany('App\Room','site_id');
    }
}
