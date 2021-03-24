<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Address;
use Validator;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class AddressController extends Controller
{
	public function manageaddress(Request $request)
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
		
		$address = Address::select('id', 'address_type', 'name', 'address', 'landmark', 'latitude', 'longitude', 'locality', 'house_no')->where('user_id','=',$user_id)->get();
		$address_arr = array();
		if(count($address)>0){
			foreach($address as $key=>$val){
				$address_arr[$key]['id'] = $val->id;
				$address_arr[$key]['address_type'] = $val->address_type;
				$address_arr[$key]['name'] = $val->name;
				$address_arr[$key]['address'] = ($val->address!='')?$val->address:'';
				$address_arr[$key]['address1'] = ($val->address!='')?$val->address:'';
				$address_arr[$key]['landmark'] = ($val->landmark!='')?$val->landmark:'';
				$address_arr[$key]['latitude'] = ($val->latitude!='')?$val->latitude:'';
				$address_arr[$key]['longitude'] = ($val->longitude!='')?$val->longitude:'';
				$address_arr[$key]['house_no'] = ($val->house_no!='')?$val->house_no:'';
				$address_arr[$key]['locality'] = ($val->locality!='')?$val->locality:'';
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'User address';
		$response['data'] = $address_arr;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	public function createaddress(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id      = isset($content->user_id) ? $content->user_id : '';
		$address_type = isset($content->address_type) ? $content->address_type : ''; 
		$name         = isset($content->name) ? $content->name : ''; 
		$address      = isset($content->address_line1) ? $content->address_line1 : '';  
		$landmark     = isset($content->landmark) ? $content->landmark : ''; 
		$latitude     = isset($content->latitude) ? $content->latitude : ''; 
		$longitude     = isset($content->longitude) ? $content->longitude : ''; 
		$house_no = isset($content->house_no) ? $content->house_no: ''; 
		$locality = isset($content->locality) ? $content->locality: ''; 
		
		$params = [
			'user_id' => $user_id,
			'address_type' => $address_type,
			'name' => $name,
			'address_line1' => $address,
			'landmark' => $landmark
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'address_type' => 'required',
            'name' => 'required',
            'address_line1' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$user = new Address();
		$user->user_id = $user_id;
		$user->name = $name;
		$user->address_type = $address_type;
		$user->address = $address;
		$user->landmark = $landmark;
		$user->locality = $locality;
		$user->house_no = $house_no;
		$user->longitude = $longitude;
		$user->latitude = $latitude;
		if($user->save()){
			$response['status'] = 200;
			$response['message'] = 'Address successfully added!';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Error occured!';
		}
       
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	public function editaddress(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);;
		
		$address_id = isset($content->address_id) ? $content->address_id : ''; 
		$address_type = isset($content->address_type) ? $content->address_type : ''; 
		$name = isset($content->name) ? $content->name : ''; 
		$address = isset($content->address_line1) ? $content->address_line1 : ''; 
		$landmark = isset($content->landmark) ? $content->landmark: ''; 
		$house_no = isset($content->house_no) ? $content->house_no: ''; 
		$locality = isset($content->locality) ? $content->locality: ''; 
		$latitude = isset($content->latitude) ? $content->latitude: ''; 
		$longitude = isset($content->longitude) ? $content->longitude: ''; 
		
		$params = [
			'address_id' => $address_id,
			'address_type' => $address_type,
			'name' => $name,
			'address' => $address,
			'landmark' => $landmark
		];
		 
		$validator = Validator::make($params, [
            'address_id' => 'required',
            'address_type' => 'required',
            'name' => 'required',
            'address' => 'required',
            'landmark' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        } 
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$user = Address::find($address_id);
		$user->name = $name;
		$user->address_type = $address_type;
		$user->address = $address;
		$user->landmark = $landmark;
		$user->locality = $locality;
		$user->house_no = $house_no;
		$user->longitude = $longitude;
		$user->latitude = $latitude;
		if($user->save()){
			$response['status'] = 200;
			$response['message'] = 'Address successfully updated!';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Error occured!';
		}       
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	public function deleteaddress(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$address_id = isset($content->address_id) ? $content->address_id : ''; 
		
		$params = [
			'address_id' => $address_id 
		];
		 
		$validator = Validator::make($params, [
            'address_id' => 'required' 
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        } 
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$address = Address::find($address_id);
		
		if (!empty($address)) {
			if($address->delete()){
				$response['status'] = 200;
				$response['message'] = 'Address successfully deleted!';
			}else{
				$response['status'] = 404;
				$response['message'] = 'Error occured!';
			}
		} else {
			$response['status'] = 404;
			$response['message'] = 'Address not fount!';
		}
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

}	
