<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    protected $table = 'disease';
	
	protected $fillable = ['name', 'user_id'];
}
