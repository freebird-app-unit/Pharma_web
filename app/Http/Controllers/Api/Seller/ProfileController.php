<?php

namespace App\Http\Controllers\Api\Seller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\SellerModel\User;
use App\SellerModel\new_pharma_logistic_employee;
use App\SellerModel\new_pharmacies;
use Storage;
use Image;
use File;
use Validator;
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
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		$token =  $request->bearerToken();
		$user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
				$login = new_pharma_logistic_employee::find($user_id);
		        if($login) 
				{
					if($login->count() > 0) 
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
						$response['message'] = 'Profile';
						$response['data']->user_id=$login->id;
						$response['data']->name=$login->name; 
						$response['data']->email=$login->email;
						$response['data']->mobile_number=$login->mobile_number;
						$response['data']->profile_image=$profile_image;
						$response['data']->pharmacy_id=$login->pharma_logistic_id;
						$pharmacy = new_pharmacies::where('id',$login->pharma_logistic_id)->first();
						$response['data']->pharmacy=$pharmacy->name;
						$response['data']->api_token=($login->api_token)?$login->api_token:'';
						$response['data']->fcm_token=($login->fcm_token)?$login->fcm_token:'';
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
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
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
            'user_id' => 'required',
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
				$login = new_pharma_logistic_employee::find($user_id);
		        if($login) 
				{
					if($login->count() > 0) 
					{
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
						$user = new_pharma_logistic_employee::find($user_id);
						$user->name = $name;
						$user->email = $email; 
						$user->mobile_number = $mobile_number;
						if (!empty($profile_image)) {
							$user->profile_image = $profile_image;
						}
						$user->save();
						$response['status'] = 200;
						$response['message'] = 'Profile updated';
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
		     }else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   		 }
		
		return decode_string($response, 200);
    }
}
