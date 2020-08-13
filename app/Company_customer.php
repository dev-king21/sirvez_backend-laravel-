<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company_customer extends Model
{
    protected $guarded = [
    ];
    public function companyies()
    {
        return $this->belongsTo('App\Company','customer_id');
    }

    public function company()
    {
        return $this->belongsTo('App\Company');
    }
    // public function users()
    // {
    //     return $this->belongTo('App\User','company_id','customer_id');
    // }

}
