<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class new_order_history extends Model
{
    protected $table = 'new_order_history';

    public function prescriptions()
    {
        return $this->belongsTo('App\Prescription', 'prescription_id', 'id'); 
    }

}
