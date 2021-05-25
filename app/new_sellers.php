<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class new_sellers extends Authenticatable
{
	use Notifiable, HasApiTokens;
     protected $table = 'new_sellers';
}
