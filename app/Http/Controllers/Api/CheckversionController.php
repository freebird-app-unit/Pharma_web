<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Address;
use Validator;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class CheckversionController extends Controller
{
	public function index(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$version = isset($content->version) ? $content->version : '';
		$app_type = isset($content->app_type) ? $content->app_type : '';
		$device_type = isset($content->device_type) ? $content->device_type : '';
		
		$params = [
			'version' => $version
		]; 
		
		$validator = Validator::make($params, [
            'version' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$conf_version = config('app.app_version_user');
		if($device_type==1){
			if($app_type==1){
				$conf_version = config('app.app_version_user_ios');
			}else if($app_type==2){
				$conf_version = config('app.app_version_seller_ios');
			}else if($app_type==3){
				$conf_version = config('app.app_version_delivery_ios');
			}
		}else if($device_type==2){
			if($app_type==1){
				$conf_version = config('app.app_version_user_and');
			}else if($app_type==2){
				$conf_version = config('app.app_version_seller_and');
			}else if($app_type==3){
				$conf_version = config('app.app_version_delivery_and');
			}
		}
		
		
		if($conf_version == $version){
			$response['status'] = 200;
			$response['message'] = '';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Your app is outdated please update';
		}
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
}	
