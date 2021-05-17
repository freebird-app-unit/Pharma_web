<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\new_users;
use App\FamilyMember;
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
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');

		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
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
		
        $response = array();
        $status = 200;
		$message = '';	

        try {
 
	        if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
					
			$login = new_users::where('mobile_number',$mobile_number)->first();
	        
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
					
			if($login->is_verify == 0){
				throw new Exception("Your account is not verify");
			}
			
				
			$profile_image = '';
			if (!empty($login->profile_image)) {

				$filename = storage_path('app/public/uploads/new_user/' . $login->profile_image);
			
				if (File::exists($filename)) {
					$profile_image = asset('storage/app/public/uploads/new_user/' . $login->profile_image);
				} else {
					$profile_image = '';
				}
			}
	
			$response['data'] = (object)array();
			$response['data']->user_id=$login->id;
			$response['data']->name=$login->name;
			$response['data']->email=$login->email;
			$response['data']->mobile_number=$login->mobile_number;
			$response['data']->profile_image=$profile_image;
			$response['data']->dob=($login->dob)?$login->dob:'';

			$data = new_users::find($login->id);
			$data->api_token = $login->createToken('MyApp')-> accessToken;
			$data->save();
			$response['data']->api_token =  $data->api_token;

			$token = new_users::find($login->id);
			$token->fcm_token = $fcm_token;
			$token->save();

            $status = 200;
            $message = 'Login Success';

		} catch (Exception $ex) {
            $message = $ex->getMessage();
            $status = 404;
        }   

        $response['status'] = $status;
		$response['message'] = $message;	

		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	public function forgotpassword(Request $request)
    {
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		
		$params = [
			'mobile_number' => $mobile_number
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required'
        ]);
 		
 		$response = array();
        $status = 200;
		$message = '';	

	 	try{

	 		if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
					
			$login = new_users::where('mobile_number',$mobile_number)->first();
	        
			if(empty($login)) 
			{
				throw new Exception("Mobile Number not found");
			}

			if($login->is_verify == 0){
				throw new Exception("Your account is not verify");
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
				$user = new_users::find($login->id);
				$user->otp = $verification_code;
				$user->otp_time = date('Y-m-d H:i:s');
				$user->save();
				$status = 200;
				$message = 'Verification code successfully sent';

	 	} catch (Exception $ex) {
            $message = $ex->getMessage();
            $status = 404;
        }  
        
		$response['status'] = $status;
		$response['message'] = $message;
		$response['data'] = (object)array();
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    } 
	public function verify_otp(Request $request) 
	{
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
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
 
       	$response = array();
        $status = 200;
		$message = '';	
		$success = false;

		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
					
			$login = new_users::where('mobile_number',$mobile_number)->first();
	        
			if(empty($login)) 
			{
				throw new Exception("Mobile Number not found");
			}

			if ($otp != $login->otp) 
			{
				throw new Exception("OTP is not valid");
			}

			$current = date("Y-m-d H:i:s");
			$otp_time = $login->otp_time;
			$diff = strtotime($current) - strtotime($otp_time);
			$days    = floor($diff / 86400);
			$hours   = floor(($diff - ($days * 86400)) / 3600);
			$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);

			if (($diff > 0) && ($minutes <= 10)) {
				$success = true;
				$status = 200;
				$message = 'Your mobile number has been verified successfully.';
			}else{
				throw new Exception("OTP expired");
			} 
			
		} catch (Exception $ex) {
            $message = $ex->getMessage();
            $status = 404;
        } 
		$response['status'] = $status;
		$response['message'] = $message;
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}

	public function resetpassword(Request $request)
    {
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
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
 		$response = array();
        $status = 200;
		$message = '';	
		
		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
					
			$login = new_users::where('mobile_number',$mobile_number)->first();
	        
			if(empty($login)) 
			{
				throw new Exception("Mobile Number not found");
			}
			
			if (Hash::check($password, $login->password)) 
			{
				throw new Exception("Old password and new password cannot be same");
			}

			$user = new_users::find($login->id);
			$user->password = Hash::make($password); 
			$user->otp = '';
			$user->api_token = '';
			$user->save();
					  
			$data = [
				'name' => $login->name
			];
			$message       = "Pharma - Your password is reset ";
			$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$login->mobile_number."'&message=" . urlencode($message);
			$sms = file_get_contents($api);
			$status = 200;
			$message = 'Your password has been successfully changed';
			
		} catch (Exception $ex) {
            $message = $ex->getMessage();
            $status = 404;
        } 
		$response['status'] = $status;
		$response['message'] = $message;
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	
	public function change_password(Request $request)
    {
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
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
 
		$response = array();
        $status = 200;
		$message = '';	
		$success = false;
		
		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
			$token =  $request->bearerToken();
			$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
			if(count($user)>0){	
				$login = new_users::where('id', $user_id)->first();
		        
				if(empty($login)) 
				{
					throw new Exception("User not found");
				}
				
				if (Hash::check($current_password, Hash::make($password)))
				{
					throw new Exception("Old password and new password cannot be same");
				}

				if(!Hash::check($current_password, $login->password))
				{
					throw new Exception("Current password doent not match");
				}

				$user = new_users::find($login->id);
				$user->password = Hash::make($password); 
				$user->otp = '';
				$user->save();
						  
				$data = [
					'name' => $login->name
				];
						
				$mobile_number = $login->mobile_number;
				$message       = "Pharma - Your password is changed";
				$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
				$sms = file_get_contents($api);
				$success = true;
				$status = 200;
				$message = 'Your password has been successfully changed';
			}else{
	    		$status = 401;
	            $message = 'Unauthenticated';
	   		}
		} catch (Exception $ex) {
            $message = $ex->getMessage();
            $status = 404;
        } 

        $response['status'] = $status;
		$response['message'] = $message;
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

	public function registration_otp(Request $request)
    {
		$response = array();
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		
		
		$params = [
			'mobile_number' => $mobile_number
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required|min:10|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,mobile_number',
        ]);
 
        if ($validator->fails()) {
           return $this->send_error($validator->errors()->first());  
        
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$login = new_users::where('mobile_number', $mobile_number)->first();
        if($login) 
		{
			if($login->is_verify == 0) {
				$verification_code = rand(1111,9999);//Str::random(6);
			
				$data = [
					'otp' => $verification_code,
				];
				$message       = "Pharma App : Registration OTP " . $verification_code;
				$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
				$sms = file_get_contents($api);
				/*$result = Mail::send('email.sendotp', $data, function ($message) use ($email) {

					$message->to($email)->subject('Pharma App : Registration OTP');

				});*/

				$user = new_users::find($login->id);
				$user->otp = $verification_code;
				$user->otp_time = date('Y-m-d H:i:s');
				$user->save();
					
				$response['status'] = 200;
				$response['data']->otp=$user->otp;
				$response['message'] = 'Verification code has been sent to your mobile number'.$mobile_number;
			
			} else {
				$response['status'] = 404;
				$response['message'] = 'Mobile Number already exist';
			}
        } 
		else 
		{
			$verification_code = rand(1111,9999);//Str::random(6);
			
			$data = [
				'otp' => $verification_code,
			];
			$message       = "Pharma App : Registration OTP " . $verification_code;
			$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
			$sms = file_get_contents($api);
			/*$result = Mail::send('email.sendotp', $data, function ($message) use ($email) {

				$message->to($email)->subject('Pharma App : Registration OTP');

			}); */


			$user = new new_users();
			$user->mobile_number = $mobile_number;
			$user->otp = $verification_code;
			$user->profile_image = '';
			$user->otp_time = date('Y-m-d H:i:s');
			$user->save();

			$response['status'] = 200;
			$response['data']->otp=$user->otp;
			$response['message'] = 'Verification code has been sent to your mobile number'.$mobile_number;
		
        }
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	public function createaccount(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$name = isset($content->name) ? $content->name : '';
		$email = isset($content->email) ? $content->email : '';
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$password = isset($content->password) ? $content->password : '';
		$dob = isset($content->dob) ? $content->dob : '';
		$fcm_token = isset($content->fcm_token) ? $content->fcm_token : '';
		$referral_code = isset($content->referral_code) ? $content->referral_code : '';
		/*$address = isset($content->address) ? $content->address : '';
		$block = isset($content->block) ? $content->block : '';
		$street = isset($content->street) ? $content->street : '';
		$country = isset($content->country) ? $content->country : '';
		$state = isset($content->state) ? $content->state : '';
		$city = isset($content->city) ? $content->city : '';
		$pincode = isset($content->pincode) ? $content->pincode : '';
		$lat = isset($content->lat) ? $content->lat : '';
		$lon = isset($content->lon) ? $content->lon : '';*/
		
		$params = [
			'name' => $name,
			'email' => $email,
			'mobile_number' => $mobile_number,
			'password' => $password,
			'dob' => $dob,
			'fcm_token' => $fcm_token
			/*'address' => $address,
			'block' => $block,
			'street' => $street,
			'country' => $country,
			'state' => $state,
			'city' => $city,
			'pincode' => $pincode,
			'lat' => $lat,
			'lon' => $lon,*/
		];
		
		$validator = Validator::make($params, [
			'name' => 'required',
            'email' => 'required',
            'mobile_number' => 'required',
            'password' => 'required',
            'dob' => 'required|date_format:Y-m-d',
            'profile_image' => 'image|max:1024',
            /*'address' => 'required',
            'block' => 'required',
            'street' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'lat' => 'required',
            'lon' => 'required',*/
        ]);
 
        if ($validator->fails()) {
         	return $this->send_error($validator->errors()->first());  
        	//return $validator->errors();
        }
		
		$response['status'] = 200; 
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$login = new_users::where('mobile_number',$mobile_number)->first();
        if($login) 
		{
				$current = date("Y-m-d H:i:s");
				$otp_time = $login->otp_time;
				$diff = strtotime($current) - strtotime($otp_time);
				$days    = floor($diff / 86400);
				$hours   = floor(($diff - ($days * 86400)) / 3600);
				$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
				if (($diff > 0) && ($minutes <= 10)) {
					
					$profile_image = '';
					if ($request->hasFile('profile_image')) {
						
						$image         = $request->file('profile_image');
						$profile_image = time() . '.' . $image->getClientOriginalExtension();

						$img = Image::make($image->getRealPath());
						$img->stream(); // <-- Key point

						Storage::disk('public')->put('uploads/new_user/'.$profile_image, $img, 'public');
					}
		
					$user = new_users::find($login->id);
					$user->name = $name;
					$user->email = $email;
					$user->is_verify = '1';
					$user->mobile_number = $mobile_number;
					$user->dob = $dob;
					$user->profile_image = $profile_image; 
					$user->password = Hash::make($password);
					$user->save();
					
					$family_member = new FamilyMember();
					$family_member->user_id = $login->id;
					$family_member->family_member_id = $login->id;
					$family_member->save();
					$profile_image = '';
					if (!empty($user->profile_image)) {

						$filename = storage_path('app/public/uploads/new_user/' . $user->profile_image);
					
						if (File::exists($filename)) {
							$profile_image = asset('storage/app/public/uploads/new_user/' . $user->profile_image);
						} else {
							$profile_image = '';
						}
					}
					$response['data']->user_id=$user->id;
					$response['data']->name=$user->name;
					$response['data']->email=$user->email;
					$response['data']->mobile_number=$user->mobile_number;
					$response['data']->profile_image=$profile_image;
					$response['data']->dob=($user->dob)?$user->dob:'';
					$data = new_users::find($login->id);
					$data->api_token = $login->createToken('MyApp')-> accessToken;
					$data->save();
					$response['data']->api_token =  $data->api_token;

					$token = new_users::find($login->id);
					$token->fcm_token = $fcm_token;
					$token->save();

					$response['status'] = 200;
					$response['message'] = 'Congratulations, your account has been successfully created.';
				} else {
					$response['status'] = 404;
					$response['message'] = 'OTP Expired';
				}
        }
		
       $response = json_encode($response);
	   $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

    public function logout(Request $request){
    	$response = array();
		
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

        $response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$user = new_users::find($user_id);
		$user->api_token = '';
		$user->fcm_token = '';
		$user->save();

		$response['status'] = 200;
		$response['message'] = 'Logged Out Successfully ';
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
	  	$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

    public function resend_otp(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		
		$params = [
			'mobile_number' => $mobile_number
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = ''; 
		$response['data'] = (object)array();
		
		$login = new_users::where('mobile_number', $mobile_number)->first();
        if($login) 
		{
			if($login->count() > 0) 
			{
				$verification_code = rand(1111,9999);//Str::random(6);
					
				$data = [
					'name' => $login->name,
					'otp' => $verification_code,
				];
				$message       = "Your OTP  is " . $verification_code;
				$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
				$sms = file_get_contents($api);
				/*$result = Mail::send('email.sendotp', $data, function ($message) use ($email) {

					$message->to($email)->subject('Forgot Password OTP');

				});
				*/
				$user = new_users::find($login->id);
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
		
       $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    } 
	
}
