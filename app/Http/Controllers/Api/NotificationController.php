<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Notification;
use App\notification_user;
use App\new_users;
use Illuminate\Support\Facades\Validator;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class NotificationController extends Controller
{
	public function notification(Request $request)
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
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$notification = Notification::where('user_id','=',$user_id)->get();
		$notification_arr = array();
		if(count($notification)>0){
			foreach($notification as $key=>$val){
				$notification_arr[$key]['id'] = $val->id;
				$notification_arr[$key]['image'] = url('/').'/uploads/1586413127.jpg';
				$notification_arr[$key]['title'] = $val->title;
				$notification_arr[$key]['description'] = ($val->description!='')?$val->description:'';
				$notification_arr[$key]['datetime'] = ($val->created_at!='')?date('Y-m-d H:i:s',strtotime($val->created_at)):'';
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		} 
		$response['message'] = 'User notification';
		$response['data'] = $notification_arr;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	public function clearallnotification(Request $request)
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
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$notification_arr = [];
		
		$notification = notification_user::where('user_id','=',$user_id)->delete();
		
		$response['status'] = 200;
		$response['message'] = 'Notification cleared';
		$response['data'] = $notification_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
}	
