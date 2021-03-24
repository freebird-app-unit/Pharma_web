<?php

namespace App\SellerModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class new_pharma_logistic_employee extends Authenticatable
{
    use Notifiable, HasApiTokens;
    protected $table = 'new_pharma_logistic_employee';
}
