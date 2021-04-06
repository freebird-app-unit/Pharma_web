<?php

namespace App\SellerModel;

use Illuminate\Database\Eloquent\Model;

class Orderassign extends Model
{
    protected $table = 'order_assign';
    protected $fillable = [
        'id','order_id','logistic_id','	deliveryboy_id','order_status','rejectreason_id	','accept_date','assign_date','reject_date','created_at','updated_at'];
}
