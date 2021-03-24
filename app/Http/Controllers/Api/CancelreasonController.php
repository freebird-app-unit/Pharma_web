<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Cancelreason;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class CancelreasonController extends Controller
{
	public function cancelreasonlist(Request $request)
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
		
		$cancelreason = Cancelreason::get();
		$cancelreason_arr = array();
		if(count($cancelreason)>0){
			foreach($cancelreason as $key=>$val){
				$cancelreason_arr[$key]['id'] = $val->id;
				$cancelreason_arr[$key]['reason'] = $val->reason;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Cancel reason list';
		$response['data'] = $cancelreason_arr;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
}	
