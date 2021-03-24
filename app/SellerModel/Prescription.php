<?php

namespace App\SellerModel;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescription';
	
	protected $fillable = ['name', 'image','id'];
}
