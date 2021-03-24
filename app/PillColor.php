<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PillColor extends Model
{
    protected $table = 'pill_color';
	
	protected $fillable = ['name', 'color'];
}
