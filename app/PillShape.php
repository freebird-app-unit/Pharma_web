<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PillShape extends Model
{
    protected $table = 'pill_shape';
	
	protected $fillable = ['name', 'image'];
}
