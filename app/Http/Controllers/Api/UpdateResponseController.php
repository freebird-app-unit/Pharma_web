<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
class UpdateResponseController extends Controller
{
    public function update_response_data(Request $request){
    	echo 'update_response_data show data';
        print_r($_POST);
    }
}
