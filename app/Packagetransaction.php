<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Packagetransaction extends Model
{
    protected $table = 'package_transaction';
	protected $fillable = ['package_id','user_id','total_delivery','package_purchase_date','is_active','package_amount'];
}
