<?php

namespace App\Http\Controllers\Api\Seller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\SellerModel\User;
//use App\Settings;

class Controller extends BaseController
{
	public function __construct() 
    {
		/* $setting = Settings::find(1);
		$this->data['site_setting'] = $setting; */
	}
	protected function buildFailedValidationResponse(Request $request, array $errors)
	{
		return response(["success"=> false , "message" => $errors],200);
	}
	
	public function send_response($result, $message)
    {
		if (empty($data)) {
            $data = (object)[];
        } 
    	$response = [
            'status' => 200,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function send_error($error, $data = [], $code = 200)
    {
        if (empty($data)) {
            $data = (object)[];
        }
    	$response = [
            'status' => 404,
            'data'    => $data,
            'message' => $error,
        ];

        return response()->json($response, $code);
    }
	
	/**
     * return random number.
     *
     * @return \Illuminate\Http\Response
     */
    public function random_string() 
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		
		$pin = mt_rand(1000, 9999)
			. mt_rand(1000, 9999)
			. $characters[rand(0, strlen($characters) - 1)];
		$string = str_shuffle($pin);
		
		$login = User::where('user_code', $string)->first();
		
		if (!empty($login)) {
			$this->random_string();
		} else {
			return $string;
		}
    }
}
