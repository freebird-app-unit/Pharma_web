<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class new_logistics extends Authenticatable
{
    use Notifiable;
    //
    public function getAuthPassword()
    {
        return $this->password;
    }

    protected $hidden = [
        'password', 'remember_token',
    ];
}
