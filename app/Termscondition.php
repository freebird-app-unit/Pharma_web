<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Termscondition extends Model
{
    protected $table = 'terms_conditions';
	
	protected $fillable = ['file'];
}
