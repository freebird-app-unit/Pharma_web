<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Helthsummary extends Model
{
    protected $table = 'helth_summary_timeline'; 
	
	protected $fillable = ['user_id','family_member_id','disease_id', 'hospital_name', 'case_number', 'doctor_name', 'symptoms', 'doctor_remark', 'next_appointment','disease_date'];
	
	public function disease_report()
    {
        return $this->hasMany('App\DiseaseReport', 'disease_id', 'id');
    }
	
	public function prescription_report()
    {
        return $this->hasMany('App\PrescriptionReport', 'disease_id', 'id');
    }
	
	public function diseases()
    {
        return $this->belongsTo('App\Disease', 'disease_id', 'id');
    }
}
