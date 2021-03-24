<?php

namespace App\DeliveryboyModel;

use Illuminate\Database\Eloquent\Model;

class new_orders extends Model
{
    protected $table = 'new_orders';

    public function prescriptions()
    {
        return $this->belongsTo('App\Prescription', 'prescription_id', 'id'); 
    }
}
