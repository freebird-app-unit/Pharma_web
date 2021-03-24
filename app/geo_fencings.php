<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class geo_fencings extends Model
{
    //
    protected $table = 'geo_fencings';
	
	public function prescriptions()
    {
        return $this->belongsTo('App\User', 'user_id', 'id'); 
    }
}
