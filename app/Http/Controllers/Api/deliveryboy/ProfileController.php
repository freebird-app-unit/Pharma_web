<?php

namespace App\Http\Controllers\Api\deliveryboy;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\SellerModel\User;
use App\SellerModel\new_pharma_logistic_employee;
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
			$user = new_pharma_logistic_employee::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->first();
			if(!empty($user)){
				$login = new_pharma_logistic_employee::select('id','password','mobile_number','profile_image','name','email','api_token','fcm_token')->where('id',$user_id)->first();
		        
				$profile_image = '';
				if (!empty($login->profile_image)) {

					$filename = storage_path('app/public/uploads/new_delivery_boy/' . $login->profile_image);
						
					if (File::exists($filename)) {
						$profile_image = asset('storage/app/public/uploads/new_delivery_boy/' . $login->profile_image);
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

				if($login->parent_type=='logistic'){
					$response['data']->delivery_service_type="1";	
				}else{
					$response['data']->delivery_service_type="0";	
				}
				$response['data']->api_token=($login->api_token)?$login->api_token:'';
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
		$email = isset($content->email) ? $content->email : '';
		$profile_image = isset($content->profile_image) ? $content->profile_image : '';
		
		$params = [
			'user_id' => $user_id,
			'name' => $name,
			'email' => $email
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
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
				$login = new_pharma_logistic_employee::select('id','mobile_number','profile_image','name','email')->where('id', $user_id)->first();

		       	$profile_image = '';
				if ($request->hasFile('profile_image')) {
							
					$filename = storage_path('app/public/uploads/new_delivery_boy/' . $login->profile_image);
		            
					if (File::exists($filename)) {
						File::delete($filename);
					}
							
					$image         = $request->file('profile_image');
					$profile_image = time() . '.' . $image->getClientOriginalExtension();

					$img = Image::make($image->getRealPath());
					$img->stream(); // <-- Key point

					Storage::disk('public')->put('uploads/new_delivery_boy/'.$profile_image, $img, 'public');
				}
						
				$user = new_pharma_logistic_employee::find($user_id);
				$user->name = $name;
				$user->email = $email; 
				if (!empty($profile_image)) {
					$user->profile_image = $profile_image;
				}
				$user->save();

				$response['status'] = 200;
				$response['message'] = 'Profile updated';

				$response['data']->user_id=$user->id;
				$response['data']->name=$user->name;
				$response['data']->email=$user->email;
				$response['data']->mobile_number=$user->mobile_number;
				$response['data']->profile_image=asset('storage/app/public/uploads/new_delivery_boy/' . $user->profile_image);

				if($login->parent_type=='logistic'){
					$response['data']->delivery_service_type="1";	
				}else{
					$response['data']->delivery_service_type="0";	
				}
				$response['data']->api_token=($user->api_token)?$user->api_token:'';      
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

