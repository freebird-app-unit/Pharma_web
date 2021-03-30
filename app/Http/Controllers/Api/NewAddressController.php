<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\country;
use App\state;
use App\city;
use App\new_address;
use App\phone_number;
use App\new_users;
use App\User;
use Validator;

class NewAddressController extends Controller
{
    public function country(Request $request)
    {
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);

        $country=[];
        $country_data = country::all();
        if(!empty($country_data)){
                 foreach($country_data as $value) {
                    $country[] = [
                            'id' => $value->id,
                            'name' => $value->name
                        ];
                    }
                $response['status'] = 200;
                $response['message'] = 'Countries';
        } else {
                $response['status'] = 404;
        }
        $response['data'] = $country;
        $response = json_encode($response);
        $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
        return response($cipher, 200);
    }

    public function state(Request $request){
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);
        
        $country_id = isset($content->country_id) ? $content->country_id : '';

        $params = [
            'country_id' => $country_id
        ];
        
        $validator = Validator::make($params, [
            'country_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
        $state = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $data=state::where('country_id',$country_id)->get();
        if(!empty($data)){
                 foreach($data as $value) {
                    $state[] = [
                            'id' => $value->id,
                            'country_id' => $value->country_id,
                            'name' => $value->name
                        ];
                    }
                $response['status'] = 200;
                $response['message'] = 'States';
        } else {
                $response['status'] = 404;
        }
        $response['data'] = $state;
        $response = json_encode($response);
        $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
        return response($cipher, 200);
    }

    public function city(Request $request){
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);
        
        $state_id = isset($content->state_id) ? $content->state_id : '';

        $params = [
            'state_id' => $state_id
        ];
        
        $validator = Validator::make($params, [
            'state_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
        $city = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $data=city::where('state_id',$state_id)->get();
        if(!empty($data)){
                 foreach($data as $value) {
                    $city[] = [
                            'id' => $value->id,
                            'state_id' => $value->state_id,
                            'name' => $value->name
                        ];
                    }
                $response['status'] = 200;
                $response['message'] = 'Cities';
        } else {
                $response['status'] = 404;
        }
        $response['data'] = $city;
        $response = json_encode($response);
        $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
        return response($cipher, 200);
    }
    public function add_address(Request $request){
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);

        $locality		= isset($content->locality) ? $content->locality : '';
        $address		= isset($content->address) ? $content->address : '';
        $user_id        = isset($content->user_id) ? $content->user_id : '';
        $name           = isset($content->name) ? $content->name : '';
        $mobileno       = isset($content->mobileno) ? $content->mobileno : '';
        $blockno        = isset($content->blockno) ? $content->blockno : '';
        $streetname     = isset($content->streetname) ? $content->streetname : '';
       /* $country_id     = isset($content->country_id) ? $content->country_id : '';
        $state_id       = isset($content->state_id) ? $content->state_id : '';*/
        $city           = isset($content->city) ? $content->city : '';
        $pincode        = isset($content->pincode) ? $content->pincode : '';
        $latitude       = isset($content->latitude) ? $content->latitude : '';
        $longitude      = isset($content->longitude) ? $content->longitude : '';
        
       
        $params = [
        	'locality'  => $locality,
        	'address'   => $address,
            'user_id'   => $user_id,
            'name'      => $name,
            'mobileno'  => $mobileno,
            'blockno'   => $blockno,
            'streetname'=> $streetname,
           /* 'country_id'=> $country_id,
            'state_id'  => $state_id,*/
            'city'      => $city,
            'pincode'   => $pincode,
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ];

        $validator = Validator::make($params, [
        	'locality'  => 'required',
        	'address'   => 'required',
            'user_id'   => 'required',
            //'name'      => 'required',
            //'mobileno'  => 'required',
            'blockno'   => 'required',
            'streetname'=> 'required',
            /*'country_id'=> 'required',
            'state_id'  => 'required',*/
            'city'      => 'required',
            'pincode'   => 'required',
            'latitude'  => 'required',
            'longitude' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
            //  return $validator->messages();
        } 
        $token =  $request->bearerToken();
        $user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
        $data = new_users::where('id',$user_id)->get();
        $users= new_users::where('mobile_number',$mobileno)->where('id',$user_id)->get();
                $address_data = new new_address();
                $address_data->locality = $locality;
                $address_data->address = $address;
                $address_data->user_id = $user_id;
                $address_data->name = ($name)?$name:$data[0]->name;
                if(count($users)>0){
                    if($users[0]->mobile_number == $mobileno){
                        $address_data->mobileno = $users[0]->mobile_number;
                    }
                }
                $address_data->mobileno = $mobileno;
                $address_data->blockno = $blockno;
                $address_data->streetname = $streetname;
               /* $address->country_id = $country_id;
                $address->state_id = $state_id;*/
                $address_data->city = $city;
                $address_data->pincode = $pincode;
                $address_data->latitude = $latitude;
                $address_data->longitude = $longitude;
                $address_data->created_at = date('Y-m-d H:i:s');
                $address_data->updated_at = date('Y-m-d H:i:s');
                $address_data->save();

                $number = new phone_number();
                $number->user_id = $address_data->user_id;
                if(count($users)>0){
                    if($users[0]->mobile_number == $mobileno){
                        
                    }
                }else{
                    $number->mobile_number = $address_data->mobileno;
                    $number->save();    
                }
                

                $add = array();
                $add['locality']   = $address_data->locality;
                $add['address']    = $address_data->address;
                $add['user_id']    = $address_data->user_id;
                $add['name']       = $address_data->name;
                $add['mobileno']   = $address_data->mobileno;
                $add['blockno']    = $address_data->blockno;
                $add['streetname'] = $address_data->streetname;
               /* $add['country_id'] = $address->country_id;
                $add['state_id']   = $address->state_id;*/
                $add['city']       = $address_data->city;
                $add['pincode']    = $address_data->pincode;
                $add['latitude']   = $address_data->latitude;
                $add['longitude']  = $address_data->longitude;
                $response['data']=$add;
                $response['message'] = 'Address Added Successfully';
            }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
            }
            $response = json_encode($response);
           $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
            return response($cipher, 200);
    }

    public function number_list(Request $request){
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);

        $user_id        = isset($content->user_id) ? $content->user_id : '';

        $params = [
            'user_id'   => $user_id
        ];

        $validator = Validator::make($params, [
            'user_id'   => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        } 

        $data = [];
        $user = phone_number::where('user_id',$user_id)->get();
        if(!empty($user)){
                 foreach($user as $value) {
                    $data[] = [
                            'mobileno' => $value->mobile_number
                        ];
                    }
                $response['status'] = 200;
                $response['message'] = 'Mobileno';
        } else {
                $response['status'] = 404;
        }
        $response['data'] = $data;
        $response = json_encode($response);
        $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
        return response($cipher, 200);
    }

    public function get_address(Request $request){
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $encryption = new \MrShan0\CryptoLib\CryptoLib();
        $secretyKey = env('ENC_KEY');
        
        $data = $request->input('data');
        $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
        $content = json_decode($plainText);
        
        $user_id = isset($content->user_id) ? $content->user_id : '';
        $page = isset($content->page) ? $content->page : '';
        
        $params = [
            'user_id' => $user_id
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());
        }

        $address_arr = [];
        $token =  $request->bearerToken();
        $user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
        $address = new_address::select('id','locality','address','user_id','name','mobileno','blockno','streetname','city','pincode','latitude','longitude')->where(['user_id'=> $user_id,'is_delete' => '1']);
            $total = $address->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $address->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 

        if(count($data_array)>0){
            foreach ($data_array as  $value) {
                 $address_arr[] = [
                            'id' => $value['id'],
                            'locality' => $value['locality'],
                            'address' => $value['address'],
                            'user_id' => $value['user_id'],
                            'name' => $value['name'],
                            'mobileno' =>  $value['mobileno'],
                            'blockno' =>  $value['blockno'],
                            'streetname' => $value['streetname'],
                            'city' =>  $value['city'],
                            'pincode' =>  $value['pincode'],
                            'latitude' => $value['latitude'],
                            'longitude' => $value['longitude']
                        ];
            }
            $response['status'] = 200;
            $response['message'] = 'Address Detail';
            }else{
           		$response['status'] = 404;
           		$response['message'] = 'Address not found';
            }
            $response['data']->content = $address_arr;
            }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
                $response['data'] = [];
            }
            $response = json_encode($response);
            $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
            return response($cipher, 200);
        }

    public function edit_address(Request $request){
            $response = array();
            $response['status'] = 200;
            $response['message'] = '';
            $response['data'] = (object)array();

            $encryption = new \MrShan0\CryptoLib\CryptoLib();
            $secretyKey = env('ENC_KEY');
            
            $data = $request->input('data');
            $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
            $content = json_decode($plainText);

            $address_id         = isset($content->address_id) ? $content->address_id : '';
            $locality		    = isset($content->locality) ? $content->locality : '';
            $address		    = isset($content->address) ? $content->address : '';
            $user_id            = isset($content->user_id) ? $content->user_id : '';
            $name               = isset($content->name) ? $content->name : '';
            $mobileno           = isset($content->mobileno) ? $content->mobileno : '';
            $blockno            = isset($content->blockno) ? $content->blockno : '';
            $streetname         = isset($content->streetname) ? $content->streetname : '';
            /*$country_id         = isset($content->country_id) ? $content->country_id : '';
            $state_id           = isset($content->state_id) ? $content->state_id : '';*/
            $city               = isset($content->city) ? $content->city : '';
            $pincode            = isset($content->pincode) ? $content->pincode : '';
            $latitude           = isset($content->latitude) ? $content->latitude : '';
            $longitude          = isset($content->longitude) ? $content->longitude : '';
            $params = [
                'address_id'=> $address_id,
                'locality'  => $locality,
                'address'   => $address,
                'user_id'   => $user_id,
                'name'      => $name,
                'mobileno'  => $mobileno,
                'blockno'   => $blockno,
                'streetname'=> $streetname,
               /* 'country_id'=> $country_id,
                'state_id'  => $state_id,*/
                'city'      => $city,
                'pincode'   => $pincode,
                'latitude'  => $latitude,
                'longitude' => $longitude
            ];

            $validator = Validator::make($params, [
                'address_id'=> 'required'
            ]);
     
            if ($validator->fails()) {
               return $this->send_error($validator->errors()->first());  
            	// return $validator->messages();
            } 
            $token =  $request->bearerToken();
            $user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
            if(count($user)>0){
                $data = new_address::where('id',$address_id)->get();

                if(count($data)>0){
	                $users= new_users::where('mobile_number',$mobileno)->where('id',$user_id)->get();
	                $address_data = new_address::find($address_id);
	                $address_data->locality = ($locality)?$locality:$data[0]->locality;
	                $address_data->address = ($address)?$address:$data[0]->address;
	                $address_data->user_id = ($user_id)?$user_id:$data[0]->user_id;
	                $address_data->name = ($name)?$name:$data[0]->name;
	                $address_data->mobileno = ($mobileno)?$mobileno:$data[0]->mobileno;
	                $address_data->blockno = ($blockno)?$blockno:$data[0]->blockno;
	                $address_data->streetname = ($streetname)?$streetname:$data[0]->streetname;
	               /* $address->country_id = ($country_id)?$country_id:$data[0]->country_id;
	                $address->state_id = ($state_id)?$state_id:$data[0]->state_id;*/
	                $address_data->city = ($city)?$city:$data[0]->city;
	                $address_data->pincode = ($pincode)?$pincode:$data[0]->pincode;
	                $address_data->latitude = ($latitude)?$latitude:$data[0]->latitude;
	                $address_data->longitude = ($longitude)?$longitude:$data[0]->longitude;
	                $address_data->created_at = date('Y-m-d H:i:s');
	                $address_data->updated_at = date('Y-m-d H:i:s');
	                $address_data->save();

	                $add = array();
	                $add['locality']   = $address_data->locality;
	                $add['address']   = $address_data->address;
	                $add['user_id']    = $address_data->user_id;
	                $add['name']       = $address_data->name;
	                $add['mobileno']   = $address_data->mobileno;
	                $add['blockno']    = $address_data->blockno;
	                $add['streetname'] = $address_data->streetname;
	                /*$add['country_id'] = $address->country_id;
	                $add['state_id']   = $address->state_id;*/
	                $add['city']       = $address_data->city;
	                $add['pincode']    = $address_data->pincode;
	                $add['latitude']   = $address_data->latitude;
	                $add['longitude']  = $address_data->longitude;
	                $response['data']=$add;
	                $response['message'] = 'Address Updated Successfully';
	                $response['status'] = 200;
	            }else{
	            	$response['message'] = 'Address id not found';
	                $response['status'] = 404;
	            }
                
                }else{
                    $response['status'] = 401;
                    $response['message'] = 'Unauthenticated';
                }
                $response = json_encode($response);
                $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
                return response($cipher, 200);
    }

    public function delete_address(Request $request){
            $response = array();
            $response['status'] = 200;
            $response['message'] = '';
            $response['data'] = (object)array();

            $encryption = new \MrShan0\CryptoLib\CryptoLib();
            $secretyKey = env('ENC_KEY');
            
            $data = $request->input('data');
            $plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
            $content = json_decode($plainText);

            $address_id         = isset($content->address_id) ? $content->address_id : '';

            $params = [
                'address_id'=> $address_id
            ];

             $validator = Validator::make($params, [
                'address_id'=> 'required'
            ]);
     
            if ($validator->fails()) {
                return $this->send_error($validator->errors()->first());  
            } 

            $data = new_address::find($address_id);
            $data->is_delete = '0';
            $data->save();

            $response['message'] = 'Address Deleted Successfully';
           
            $response = json_encode($response);
            $cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        
            return response($cipher, 200);
    }
}
