<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [
    ];

    public function company_customers(){
        return $this->hasMany('App\Company_customer','customer_id');
    }
}
