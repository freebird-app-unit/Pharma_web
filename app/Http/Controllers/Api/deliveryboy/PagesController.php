<?php

namespace App\Http\Controllers\Api\deliveryboy;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\SellerModel\User;
use App\SellerModel\Pages;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class PagesController extends Controller
{
    public function privacypolicy(Request $request)
    {
		$response = array();
		// $user_id = $request->input('user_id');
		
		$data = $request->input('data');
		$content = json_decode($data);
		//$user_id = $content->user_id; 
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$pages = Pages::where('slug','privacy_policy')->first();
        if($pages) 
		{
			if($pages->count() > 0) 
			{
				$response['status'] = 200;
				$response['message'] = 'Privacy policy';
				$response['data']->title=$pages->title;
				$response['data']->description=$pages->description;
            } 
			else 
			{
				$response['status'] = 404;
				$response['message'] = 'User not found';
            }
        } 
		else 
		{
			$response['status'] = 404;
            $response['message'] = 'No data found';
        }
		
        return response($response, 200);
    }
}
