<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\new_users;
use Illuminate\Validation\Rule;
use Storage;
use Image;
use File;
use Validator;
use App\referralcode;
use App\new_pharmacies;
use App\referralcode_delivery;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
		$response = array();
		// $user_id = $request->input('user_id');
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
		
		$login = new_users::find($user_id);
        if($login) 
		{
			if($login->count() > 0) 
			{
				$profile_image = '';
				if (!empty($login->profile_image)) {

					$filename = storage_path('app/public/uploads/new_user/' . $login->profile_image);
				
					if (File::exists($filename)) {
						$profile_image = asset('storage/app/public/uploads/new_user/' . $login->profile_image);
					} else {
						$profile_image = '';
					}
				}
				
				$response['status'] = 200;
				$response['message'] = 'Profile';
				$response['data']->user_id=$login->id;
				$response['data']->name=$login->name;
				$response['data']->email=$login->email;
				$response['data']->mobile_number=$login->mobile_number;
				$response['data']->profile_image=$profile_image;
				$response['data']->referral_code=$login->referral_code;
				$response['data']->dob=($login->dob)?$login->dob:'';
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
            $response['message'] = 'User not found';
        }
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	public function editprofile(Request $request)
    {
		$response = array();
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$name = isset($content->name) ? $content->name : '';
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : ''; 
		$email = isset($content->email) ? $content->email : ''; 
		$dob = isset($content->dob) ? $content->dob : ''; 
		$referral_code = isset($content->referral_code) ? $content->referral_code : '';
		$params = [
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required'
        ]);
 	
 		if(!empty($mobile_number)){
 				$params = [
					'mobile_number' => $mobile_number
				];
				
				$validator = Validator::make($params, [
		            'mobile_number' =>  [
					    'required',
					    Rule::unique('new_pharma_logistic_employee','mobile_number')->ignore($user_id),Rule::unique('new_users','mobile_number')->ignore($user_id),Rule::unique('new_pharmacies','mobile_number')->ignore($user_id),Rule::unique('new_logistics','mobile_number')->ignore($user_id)
					],
		        ]);

		        if ($validator->fails()) {
            		return $this->send_error($validator->errors()->first());  
        		}
 		}
 		if(!empty($email)){
 				$params = [
					'email' => $email
				];
				
				$validator = Validator::make($params, [
		            'email' =>  [
					    'required',
					    Rule::unique('new_pharma_logistic_employee','email')->ignore($user_id),Rule::unique('new_users','email')->ignore($user_id),Rule::unique('new_pharmacies','email')->ignore($user_id),Rule::unique('new_logistics','email')->ignore($user_id)
					],
		        ]);

		        if ($validator->fails()) {
            		return $this->send_error($validator->errors()->first());  
        		}
 		}
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$login = new_users::find($user_id);
         if($login) 
		{
			if(!empty($referral_code)){
				$pharmacy_code =  new_pharmacies::where('referral_code',$referral_code)->first();
			    if(empty($pharmacy_code)){
					$response['status'] = 404;
					$response['message'] = 'Referral Code is not available';
				}else{
					if($login->count() > 0) 
					{
						$profile_image = '';
						if ($request->hasFile('profile_image')) {
							
							$filename = storage_path('app/public/uploads/new_user/' . $login->profile_image);
		            
							if (File::exists($filename)) {
								File::delete($filename);
							}
							
							$image         = $request->file('profile_image');
							$profile_image = time() . '.' . $image->getClientOriginalExtension();

							$img = Image::make($image->getRealPath());
							$img->stream(); // <-- Key point

							Storage::disk('public')->put('uploads/new_user/'.$profile_image, $img, 'public');
						}
						$user = new_users::find($user_id);
						$user_data = new_users::where('id',$user_id)->get();
						foreach ($user_data as $u_data) {
							$user->mobile_number = ($mobile_number)?$mobile_number:$u_data->mobile_number;
						}
						$user->name = $name;
						$email_data = new_users::where('id',$user_id)->first();
						$user->email = ($email)?$email:$email_data->email; 
						$user->dob = $dob; 
						if (!empty($profile_image)) {
							$user->profile_image = $profile_image;
						}
						$user->referral_code = $referral_code;
						$user->save();

						$check_referral_onoff = referralcode::where('id','1')->first();
						$pharmacy_code = new_pharmacies::where('referral_code',$referral_code)->first();
						if($check_referral_onoff->toggle == 'false'){
							$entry_code = new referralcode_delivery();
							$entry_code->pharmacy_id = $pharmacy_code->id;
							$entry_code->pharmacy_name = $pharmacy_code->name;
							$entry_code->pharmacy_referralcode = $pharmacy_code->referral_code;
							$entry_code->user_id = $user->id;
							$entry_code->by_referral_freedelivery = 0;
							$entry_code->created_at = date('Y-m-d H:i:s');
							$entry_code->updated_at = date('Y-m-d H:i:s');
							$entry_code->save();
						}else{
							$entry_code = new referralcode_delivery();
							$entry_code->pharmacy_id = $pharmacy_code->id;
							$entry_code->pharmacy_name = $pharmacy_code->name;
							$entry_code->pharmacy_referralcode = $pharmacy_code->referral_code;
							$entry_code->user_id = $user->id;
							$entry_code->by_referral_freedelivery = 1;
							$entry_code->created_at = date('Y-m-d H:i:s');
							$entry_code->updated_at = date('Y-m-d H:i:s');
							$entry_code->save();

							$remain_delivery_increase = new_pharmacies::where('id',$entry_code->pharmacy_id)->first();
							$remain_delivery_increase->remining_standard_paid_deliveries = $remain_delivery_increase->remining_standard_paid_deliveries +1;
							$remain_delivery_increase->save();
						}
						$response['status'] = 200;
						$response['message'] = 'Your profile has been successfully updated';
		            } else {
						$response['status'] = 404;
						$response['message'] = 'User not found';
		            }
				}
			}else{
				if($login->count() > 0) 
					{
						$profile_image = '';
						if ($request->hasFile('profile_image')) {
							
							$filename = storage_path('app/public/uploads/new_user/' . $login->profile_image);
		            
							if (File::exists($filename)) {
								File::delete($filename);
							}
							
							$image         = $request->file('profile_image');
							$profile_image = time() . '.' . $image->getClientOriginalExtension();

							$img = Image::make($image->getRealPath());
							$img->stream(); // <-- Key point

							Storage::disk('public')->put('uploads/new_user/'.$profile_image, $img, 'public');
						}
						$user = new_users::find($user_id);
						$user_data = new_users::where('id',$user_id)->get();
						foreach ($user_data as $u_data) {
							$user->mobile_number = ($mobile_number)?$mobile_number:$u_data->mobile_number;
						}
						$user->name = $name;
						$email_data = new_users::where('id',$user_id)->first();
						$user->email = ($email)?$email:$email_data->email; 
						$user->dob = $dob; 
						if (!empty($profile_image)) {
							$user->profile_image = $profile_image;
						}
						$user->referral_code = $email_data->referral_code; 
						$user->save();
						$response['status'] = 200;
						$response['message'] = 'Your profile has been successfully updated';
		            } else {
						$response['status'] = 404;
						$response['message'] = 'User not found';
		            }
			}
		} else {
			$response['status'] = 404;
            $response['message'] = 'User not found';
        }
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

     public function profile_otp(Request $request)
    {
		$response = array();		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$user_id = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'mobile_number' => $mobile_number,
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$login = new_users::where('id', $user_id)->first();
        if($login) 
		{		
			$duplicate_number = new_users::where('mobile_number',$mobile_number)->first();
			if(!empty($duplicate_number)){
					$response['status'] = 404;
					$response['message'] = 'Mobile Number Already Exists';
			}else{
				$verification_code = rand(1111,9999);//Str::random(6);
			
				$data = [
					'otp' => $verification_code
				];
				$message       = "Pharma App : Profile OTP" .  $verification_code;
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
				$response['message'] = 'verification code successfully sent';
			}
        }else{
        	$response['status'] = 404;
			$response['message'] = 'Mobile Number does not exist';
        } 
        
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

    public function profile_verify_otp(Request $request) {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$otp = isset($content->otp) ? $content->otp : '';
		$user_id = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'mobile_number' => $mobile_number,
			'otp' => $otp,
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'otp' => 'required|numeric',
            'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$success = false;
		$login = new_users::where('id', $user_id)->first();
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
						$user = new_users::find($user_id);
						$user->mobile_number = $mobile_number;
						$user->save();
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

		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}
}
