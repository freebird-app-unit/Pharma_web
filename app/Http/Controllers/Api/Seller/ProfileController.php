<?php

namespace App\Http\Controllers\Api\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\SellerModel\User;
use App\SellerModel\new_pharma_logistic_employee;
use App\SellerModel\new_pharmacies;
use App\new_sellers;
use Storage;
use Image;
use File;
use Validator;
use Exception;

//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
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
 
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
			$token =  $request->bearerToken();
			$user = new_sellers::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->first();
			if(!empty($user)){
				$login = new_sellers::select('id','password','mobile_number','profile_image','name','email','pharma_logistic_id','api_token','fcm_token')->where('id', $user_id)->first();
		        
				if(empty($login)) 
				{
					throw new Exception("User not found");
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
				$response['message'] = 'Profile';
				$response['data']->user_id=$login->id;
				$response['data']->name=$login->name; 
				$response['data']->email=$login->email;
				$response['data']->mobile_number=$login->mobile_number;
				$response['data']->profile_image=$profile_image;
				$response['data']->pharmacy_id=$login->pharma_logistic_id;
				$pharmacy = new_pharmacies::select('id','name')->where('id',$login->pharma_logistic_id)->first();
				$response['data']->pharmacy=$pharmacy->name;
				$response['data']->api_token=($login->api_token)?$login->api_token:'';
				$response['data']->fcm_token=($login->fcm_token)?$login->fcm_token:'';
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
	public function editprofile(Request $request)
    {
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$name = isset($content->name) ? $content->name : '';
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : ''; 
		$email = isset($content->email) ? $content->email : ''; 
		$profile_image = isset($content->profile_image) ? $content->profile_image : '';
		
		$params = [
			'user_id' => $user_id,
			'name' => $name,
			'mobile_number' => $mobile_number,
			'email' => $email,
		];
		
		$validator = Validator::make($params, [
            'email' =>  ['required',Rule::unique('new_sellers','email')->ignore($user_id)],
			
			'mobile_number' =>  ['required',Rule::unique('new_sellers','mobile_number')->ignore($user_id)],
        ]);
 

		if ($validator->fails()) {
           return $this->send_error($validator->errors()->first());  
        }
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		try{
			if ($validator->fails()) {
	            throw new Exception($validator->errors()->first());
	        }
			$token =  $request->bearerToken();
			$user = new_sellers::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->first();
			if(!empty($user)){
				$login = new_sellers::select('id','mobile_number','profile_image','name','email')->where('id', $user_id)->first();
		        
				if(empty($login)) 
				{
					throw new Exception("User not found");
				}
		
				$profile_image = '';
				if ($request->hasFile('profile_image')) {
							
					$filename = storage_path('app/public/uploads/new_seller/' . $login->profile_image);
		            
					if (File::exists($filename)) {
						File::delete($filename);
					}
							
					$image         = $request->file('profile_image');
					$profile_image = time() . '.' . $image->getClientOriginalExtension();

					$img = Image::make($image->getRealPath());
					$img->stream(); // <-- Key point

					Storage::disk('public')->put('uploads/new_seller/'.$profile_image, $img, 'public');
				}
				$user = new_sellers::find($user_id);
				$user->name = $name;
				$user->email = $email; 
				$user->mobile_number = $mobile_number;
				if (!empty($profile_image)) {
					$user->profile_image = $profile_image;
				}
				$user->save();
				$response['status'] = 200;
				$response['message'] = 'Profile updated';   
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
}
