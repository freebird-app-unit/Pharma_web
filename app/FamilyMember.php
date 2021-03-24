<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $table = 'family_members';
	
	public function diseases()
    {
        return $this->hasMany('App\Helthsummary', 'family_member_id', 'id');
    }
	
	public function allergy()
    {
        return $this->hasMany('App\Allergy', 'family_member_id', 'id'); 
    }
}
