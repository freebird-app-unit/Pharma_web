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
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$login = new_pharma_logistic_employee::where(['mobile_number' => $mobile_number,"user_type"=>"seller"])->first();
        if($login) 
		{
			if($login->count() > 0) 
			{
				$active = new_pharma_logistic_employee::where(['mobile_number'=>$mobile_number,"user_type"=>"seller",'is_active'=>'1'])->first();
				if(!empty($active)){
						if(Hash::check($password, $login->password)) 
						{
							
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
							$pharmacy = new_pharmacies::where('id',$login->pharma_logistic_id)->first();
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
		                } 
						else 
						{
		                    $response['status'] = 404;
							$response['message'] = 'You entered wrong Mobile Number or password';
		                }
		            }else{
		            		$response['status'] = 404;	
							$response['message'] = 'Your account is not active please contact to adminstrator';
		            }
            } 
			else 
			{
				$response['status'] = 404;
				$response['message'] = 'You entered wrong Mobile Number or password';
            }
        } 
		else 
		{
			$response['status'] = 404;
            $response['message'] = 'You entered wrong Mobile Number or password';
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
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = ''; 
		$response['data'] = (object)array();
		
		$login = new_pharma_logistic_employee::where('mobile_number', $mobile_number)->first();
        if($login) 
		{
			if($login->count() > 0) 
			{
				/*if ($login->is_verify == 0) {
					
					$response['status'] = 401;
					$response['message'] = 'Please verify your account first';
					return response($response, 200);
				}*/
				$verification_code = rand(1111,9999);//Str::random(6);
					
				$data = [
					'name' => $login->name,
					'otp' => $verification_code,
				];
				$message       = "Forgot Password OTP " . $verification_code;
				$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
				$sms = file_get_contents($api);
				/*$result = Mail::send('email.sendotp', $data, function ($message) use ($email) {

					$message->to($email)->subject('Forgot Password OTP');

				});*/
				
				$user = new_pharma_logistic_employee::find($login->id);
				$user->otp = $verification_code;
				$user->otp_time = date('Y-m-d H:i:s');
				$user->save();
				
				$response['status'] = 200;
				$response['data']->otp=$user->otp;
				$response['message'] = 'Verification code successfully sent';
            } 
			else 
			{
				$response['status'] = 404;
				$response['message'] = 'Mobile Number not found';
            }
        } 
		else 
		{
			$response['status'] = 404;
            $response['message'] = 'Mobile Number not found';
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
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$success = false;
		$login = new_pharma_logistic_employee::where('mobile_number', $mobile_number)->first();
		if($login) {
			if($login->count() > 0) {
				if ($otp == $login->otp) {
				
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
				} else {
					$response['status'] = 404;
					$response['message'] = 'OTP is not valid';
				}
			} else {
				$response['status'] = 404;
				$response['message'] = 'Mobile Number not found';
			}
		} else {
			$response['status'] = 404;
			$response['message'] = 'Mobile Number not found';
		} 
		
		/*if ($success) {
			return $this->send_response([], $msg);
		} else {
			return $this->send_error($msg, []);
		}*/
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
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        } 
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		
		$login = new_pharma_logistic_employee::where('mobile_number', $mobile_number)->first();
		if($login) 
		{
			if($login->count() > 0) 
			{	

				$check_a =Hash::check($password, $login->password);
				if($check_a){
					$response['status'] = 404;
					$response['message'] = 'Old password and new password cannot be same';
				}else{
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
					/*$result = Mail::send('email.change_password', $data, function ($message) use ($email) {

						$message->to($email)->subject('Pharma - Password Change');

					});*/
					$response['status'] = 200;
					$response['message'] = 'Your password successfully changed!';
				}	
			} else {
				$response['status'] = 404;
				$response['message'] = 'Mobile Number not found';
			}
		} else {
			$response['status'] = 404;
			$response['message'] = 'Mobile Number not found';
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
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        } 
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$success = false;
		$msg = '';
		$token =  $request->bearerToken();
		$user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
				$login = new_pharma_logistic_employee::where('id', $user_id)->first();
				if($login) 
				{
					if($login->count() > 0) 
					{	
						$check_a =Hash::check($current_password, Hash::make($password));
						if($check_a){
							$response['status'] = 404;
							$response['message'] = 'Old password and new password cannot be same';
						}elseif (Hash::check($current_password, $login->password)) { 
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
							/*$result = Mail::send('email.change_password', $data, function ($message) use ($email) {

								$message->to($email)->subject('Pharma - Password Change');

							});*/
							//$success = true;
							$response['status'] = 200;
							$response['message']= 'Your password successfully changed!';
							
						} else {
							$response['status'] = 404;
							$response['message']= 'Current password doent not match';
						}
					
					} else {
						$response['status'] = 404;
						$response['message']= 'User not found';
					}

				} else {
					$response['status'] = 404;
					$response['message']= 'User not found';
				}
			 }else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   		 }
		return decode_string($response, 200);
        /*if ($success) {
			return $this->send_response([], $response);
		} else {
			return $this->send_error($response, []);
		}*/
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
		$user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
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
