<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class users_doc extends Model
{
    protected $table = 'users_doc';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'user_id', 'license', 'pancard', 'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
	
	public function User()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
