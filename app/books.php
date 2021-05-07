<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class books extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'books';
    
    protected $fillable = [
        'name'
    ];
}
