<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    protected $table = 'allergies';
	
	protected $fillable = ['allergy_name', 'user_id', 'family_member_id'];
}




