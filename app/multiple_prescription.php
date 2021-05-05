<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class multiple_prescription extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'multiple_prescription';
    
    protected $fillable = [
        'multiple_prescription_id','user_id','prescription_id','prescription_name','image','prescription_date','is_delete','created_at','updated_at'
    ];
}
