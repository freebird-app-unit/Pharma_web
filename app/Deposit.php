<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposit_transaction';
	protected $fillable = ['logistic_id','reference_number','amount','transaction_datetime','total_deposit'];
}
