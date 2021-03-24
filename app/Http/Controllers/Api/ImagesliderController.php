<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Imageslider;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class ImagesliderController extends Controller
{
	public function dashboardimageslider(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		// $user_id = $content->user_id;
		
		$imageslider = Imageslider::get();
		$imageslider_arr = array();
		if(count($imageslider)>0){
			foreach($imageslider as $key=>$val){
				$file_name = '';
				if (!empty($val->image)) {
					if (file_exists(storage_path('app/public/uploads/slider/'.$val->image))){
						$imageslider_arr[$key]['image'] = asset('storage/app/public/uploads/slider/' . $val->image);
					}
				}
				
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Dashboard slider';
		$response['data'] = $imageslider_arr;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
}	
