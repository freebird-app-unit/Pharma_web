<?php

namespace App\Http\Controllers\Api\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\SellerModel\User;
use App\SellerModel\new_pharma_logistic_employee;
use App\SellerModel\new_pharmacies;
use Mail;
use Validator;
use Storage;
use Image;
use File;
use Exception;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class LoginController extends Controller
{
    public function index(Request $request)
    {
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$password = isset($content->password) ? $content->password : '';
		$fcm_token = isset($content->fcm_token) ? $content->fcm_token : '';
		
		$params = [
			'mobile_number' => $mobile_number,
			'password' => $password,
			'fcm_token' => $fcm_token
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'password' => 'required'
        ]);
 
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
         try {
 
	        if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }

	        $login = new_pharma_logistic_employee::select('mobile_number','user_type','id','name','email','password','profile_image','mobile_number','pharma_logistic_id','api_token','fcm_token','is_active','is_available')->where(['mobile_number' => $mobile_number,"user_type"=>"seller"])->first();
	        
			if(empty($login)) 
			{
				throw new Exception("You have entered wrong mobileno");
			}

			if(!Hash::check($password, $login->password)) 
			{
				throw new Exception("You have entered wrong password");
			}
					
			if($login->is_active == 0){
				throw new Exception("Your account is not active please contact to adminstrator");	
			}
					
			if($login->is_available == 0){
				throw new Exception("Not available");
			}
			$profile_image = '';
			if (!empty($login->profile_image)) {

				$filename = storage_path('app/public/uploads/new_seller/' . $login->profile_image);
							
				if (File::exists($filename)) {
					$profile_image = asset('storage/app/public/uploads/new_seller/' . $login->profile_image);
				} else {
					$profile_image = '';
				}
			}
					
			$response['status'] = 200;
			$response['message'] = 'Login success';
			$response['data']->user_id=$login->id;
			$response['data']->name=$login->name;
			$response['data']->email=$login->email;
			$response['data']->mobile_number=$login->mobile_number;
			$response['data']->profile_image=$profile_image;
			$response['data']->pharmacy_id=$login->pharma_logistic_id;
			$pharmacy = new_pharmacies::select('id','name')->where('id',$login->pharma_logistic_id)->first();
			if(!empty($pharmacy)){
				$response['data']->pharmacy=$pharmacy->name;	
			}else{
				$response['data']->pharmacy='';	
			}							
			$data = new_pharma_logistic_employee::find($login->id);
			$data->api_token = $login->createToken('MyApp')-> accessToken;
			$data->save();
			$response['data']->api_token =  $data->api_token;

			$token = new_pharma_logistic_employee::find($login->id);
			$token->fcm_token = $fcm_token;
			$token->save();

			$response['data']->fcm_token= ($token->fcm_token) ? $token->fcm_token :'';
		} catch (Exception $ex) {
            $response['message'] = $ex->getMessage();
            $response['status'] = 404;
        } 
		                	
		return decode_string($response, 200);
    }
	public function forgotpassword(Request $request)
    {
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		
		$params = [
			'mobile_number' => $mobile_number
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required'
        ]);
 
		$response['status'] = 200;
		$response['message'] = ''; 
		$response['data'] = (object)array();
	
		try{

	 		if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }

			$login = new_pharma_logistic_employee::select('id','mobile_number','name','is_available','is_active','otp','otp_time')->where('mobile_number', $mobile_number)->first();		
	        
			if(empty($login)) 
			{
				throw new Exception("Mobile Number not found");
			}

			if($login->is_available == 0){
				throw new Exception("Not available");
			}

			if($login->is_active == 0){
				throw new Exception("Your account is not active please contact to adminstrator");	
			}
			$verification_code = rand(1111,9999);//Str::random(6);
					
			$data = [
				'name' => $login->name,
				'otp' => $verification_code,
			];
			$message       = "Forgot Password OTP " . $verification_code;
			$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
			$sms = file_get_contents($api);
			
			$seller = new_pharma_logistic_employee::select('id','otp','otp_time')->where('id',$login->id)->first();
			$seller->otp = $verification_code;
			$seller->otp_time = date('Y-m-d H:i:s');
			$seller->save();
				
			$response['status'] = 200;
			$response['data']->otp=$seller->otp;
			$response['message'] = 'Verification code successfully sent';

		} catch (Exception $ex) {
            $response['message'] = $ex->getMessage();
            $response['status'] = 404;
        } 
            	
        return decode_string($response, 200);
    } 
	
	public function verify_otp(Request $request) {
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$otp = isset($content->otp) ? $content->otp : '';
		
		$params = [
			'mobile_number' => $mobile_number,
			'otp' => $otp
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'otp' => 'required|numeric'
        ]);
 
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$success = false;
		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
					
			$login = new_pharma_logistic_employee::select('mobile_number','otp','otp_time','is_active','is_available')->where('mobile_number',$mobile_number)->first();
	        
			if(empty($login)) 
			{
				throw new Exception("Mobile Number not found");
			}

			if ($otp != $login->otp) 
			{
				throw new Exception("OTP is not valid");
			}
			if($login->is_available == 0){
				throw new Exception("Not available");
			}

			if($login->is_active == 0){
				throw new Exception("Your account is not active please contact to adminstrator");	
			}	
			$current = date("Y-m-d H:i:s");
			$otp_time = $login->otp_time;
			$diff = strtotime($current) - strtotime($otp_time);
			$days    = floor($diff / 86400);
			$hours   = floor(($diff - ($days * 86400)) / 3600);
			$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
			if (($diff > 0) && ($minutes <= 10)) {
				$success = true;
				$response['status'] = 200;
				$response['message'] = 'OTP verify successfully!';
			} else {
				$response['status'] = 404;
				$response['message'] = 'OTP expired';
			}
		
		} catch (Exception $ex) {
            $response['message'] = $ex->getMessage();
            $response['status'] = 404;
        }
		return decode_string($response, 200);	
	}

	public function resetpassword(Request $request)
    {
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$password = isset($content->password) ? $content->password : '';
		$confirm_password = isset($content->confirm_password) ? $content->confirm_password : '';
		
		$params = [
			'mobile_number' => $mobile_number,
			'password' => $password,
			'confirm_password' => $confirm_password,
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
 
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
	
		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
					
			$login = new_pharma_logistic_employee::select('mobile_number','password','otp','id')->where('mobile_number', $mobile_number)->first();
	        
			if(empty($login)) 
			{
				throw new Exception("Mobile Number not found");
			}
			
			if (Hash::check($password, $login->password)) 
			{
				throw new Exception("Old password and new password cannot be same");
			}
			$user = new_pharma_logistic_employee::find($login->id);
			$user->password = Hash::make($password); 
			$user->otp = '';
			$user->save();
					  
			$data = [
				'name' => $login->name
			];
			$message       = "Pharma - Your password is changed ";
			$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
			$sms = file_get_contents($api);
					
			$response['status'] = 200;
			$response['message'] = 'Your password successfully changed!';
		} catch (Exception $ex) {
            $response['message'] = $ex->getMessage();
            $response['status'] = 404;
        } 
		
        return decode_string($response, 200);
    }
	
	
	public function change_password(Request $request)
    {
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

		$user_id = isset($content->user_id) ? $content->user_id : '';
		$current_password = isset($content->current_password) ? $content->current_password : '';
		$password = isset($content->password) ? $content->password : '';
		$confirm_password = isset($content->confirm_password) ? $content->confirm_password : '';
		
		$params = [
			'user_id' => $user_id,
			'current_password' => $current_password,
			'password' => $password,
			'confirm_password' => $confirm_password,
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'current_password' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
 
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
	
		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
			$token =  $request->bearerToken();
			$user = new_pharma_logistic_employee::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->first();
			if(!empty($user)){
				$login = new_pharma_logistic_employee::select('id','password','mobile_number')->where('id', $user_id)->first();
		        
				if(empty($login)) 
				{
					throw new Exception("User not found");
				}
				if(!Hash::check($current_password, $login->password))
				{
					throw new Exception("Current password doent not match");
				}
				if (Hash::check($current_password, Hash::make($password)))
				{
					throw new Exception("Old password and new password cannot be same");
				}
					
				$user = new_pharma_logistic_employee::find($login->id);
				$user->password = Hash::make($password); 
				$user->otp = '';
				$user->save();
							  
				$data = [
					'name' => $login->name
				];
							
				$mobile_number = $login->mobile_number;
				$message       = "Pharma - Password Change " . $login->name;
				$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
				$sms = file_get_contents($api);
				$response['status'] = 200;
				$response['message']= 'Your password successfully changed!';				
			}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   		}
	   	} catch (Exception $ex) {
            $response['message'] = $ex->getMessage();
            $response['status'] = 404;
        }
		return decode_string($response, 200);
    }

	public function logout(Request $request){
    	$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
	
		$user_id = isset($content->user_id) ? $content->user_id : '';

		$params = [
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
           'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$token =  $request->bearerToken();
		$user = new_pharma_logistic_employee::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->first();
		if(!empty($user)){
			$user = new_pharma_logistic_employee::find($user_id);
			$user->api_token = '';
			$user->fcm_token = '';
			$user->save();
			$response['status'] = 200;
			$response['message'] = 'Logged Out Successfully ';
		 }else{
	    	$response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
	   	 }
			return decode_string($response, 200);
    }
	
}
