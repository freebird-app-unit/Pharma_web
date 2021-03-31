<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Prescription;
use App\new_users;
use Validator;
use Storage;
use Image;
use File;

class New_prescriptioncontroller extends Controller
{
    public function prescription_list(Request $request)
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
		
		$user_id    = isset($content->user_id) ? $content->user_id : '';
		$searchtext = isset($content->searchtext) ? trim($content->searchtext) : ''; 
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
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		if (!empty($searchtext)) {
			$prescription = Prescription::select('id', 'name', 'image', 'created_at')->where('name', 'like', '%'.$searchtext.'%')->where(['user_id'=>$user_id,"is_delete"=>"0"])->orderBy('id', 'DESC');

			$total = $prescription->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $prescription->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
		} else {
			$prescription = Prescription::select('id', 'name', 'image', 'created_at')->where(['user_id'=>$user_id,"is_delete"=>"0"])->orderBy('id', 'DESC');

			$total = $prescription->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $prescription->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
		}
		
		$prescription_arr = array();
		if(count($data_array)>0){
			foreach($data_array as $key=>$val){
				$file_name = '';
				if (!empty($val['image'])) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$val['image']))){
						$file_name = asset('storage/app/public/uploads/prescription/' . $val['image']);
					}
				}
				$prescription_arr[$key]['id'] = $val['id'];
				$prescription_arr[$key]['name'] = $val['name'];
				$prescription_arr[$key]['image'] = $file_name;
				$prescription_arr[$key]['date'] = date('d-m-Y', strtotime($val['created_at']));
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		} 
		$response['message'] = 'Prescription List';
		$response['data']->content = $prescription_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	            $response['data'] = [];
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
}
