<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class patient_report_image extends Eloquent
{
     protected $connection = 'mongodb';
    protected $collection = 'patient_report_images';
    
    protected $fillable = ['patient_report_image_id','user_id','patient_report_id','name','image','date','is_delete','created_at','updated_at'];
}
