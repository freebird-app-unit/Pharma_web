<?php

namespace App\SellerModel;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';

	public function prescriptions()
    {
        return $this->belongsTo('App\SellerModel\Prescription', 'prescription_id', 'id'); 
    }

    public function users()
    {
        return $this->belongsTo('App\SellerModel\User', 'customer_id', 'id'); 
    }
}
