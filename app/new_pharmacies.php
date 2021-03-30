<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;


class new_pharmacies extends Authenticatable
{
    protected $table = 'new_pharmacies';

    protected $guard = 'new_pharmacies';

    protected $primaryKey = 'id';
}
