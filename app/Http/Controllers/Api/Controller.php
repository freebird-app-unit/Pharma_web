<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\User;
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
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		if (empty($data)) {
            $data = (object)[];
        } 
    	$response = [
            'status' => 200,
            'data'    => $result,
            'message' => $message,
        ];

		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

    /**
     * return error response.
     * 
     * @return \Illuminate\Http\Response
     */
    public function send_error($error, $data = [], $code = 200)
    {
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
        if (empty($data)) {
            $data = (object)[];
        }
    	$response = [
            'status' => 404,
            'data'    => $data,
            'message' => $error,
        ];
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, $code);
    }
	
	/**
     * return random number.
     *
     * @return \Illuminate\Http\Response
     */
    public function random_string() 
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		
		// generate a pin based on 2 * 7 digits + a random character
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
