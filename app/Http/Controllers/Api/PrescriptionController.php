<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Prescription;
use App\multiple_prescription;
use App\new_users;
use Validator;
use Storage;
use Image;
use File;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class PrescriptionController extends Controller
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

	public function save_prescription(Request $request)
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
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$name = isset($content->name) ? $content->name : '';
		$prescription_date = isset($content->prescription_date) ? $content->prescription_date : '';

		$params = [
			'user_id' => $user_id,
			'name' => $name,
			'prescription_date' => $prescription_date,
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'name' => 'required',
            'prescription_date' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }	

		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		
		$prescription_image = '';
		if ($request->hasFile('prescription')) {
			
			$image         = $request->file('prescription');
			$prescription_image = time() . '.' . $image->getClientOriginalExtension();

			$img = Image::make($image->getRealPath());
			$img->stream(); // <-- Key point

			Storage::disk('public')->put('uploads/prescription/'.$prescription_image, $img, 'public');
		} else {
			$response['status'] = 404;
			$response['message'] = 'Please upload prescription';
			
			return response($response, 200);
		}
		$find_name = Prescription::where(['user_id'=>$user_id,'name'=>$name,"is_delete"=>"0"])->get();
		if(count($find_name)>0){
			$response['status'] = 404;
			$response['message'] = 'Prescription name already exists';
		}else{
			$prescriptions = new Prescription();
			$prescriptions->user_id = $user_id;
			$prescriptions->name = $name;
			$prescriptions->image = $prescription_image;
			$prescriptions->save();
			$response['status'] = 200;
			$response['message'] = 'Prescription saved successfully!';
			$response['data'] = (object)array();
		}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}

	public function delete_prescription(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$id  = isset($content->id) ? $content->id : 0;
		
		$params = [
			'id'     => $id
		]; 
		
		$validator = Validator::make($params, [
            'id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$prescription = Prescription::where('id',$id)->first();
		$prescription->is_delete="1";
		$prescription->save();
		
		$response['status'] = 200;
		$response['message'] = 'prescription successfully deleted!';    
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
    public function save_prescription_imagedata(Request $request)
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
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$name = isset($content->name) ? $content->name : '';
		$prescription_date = isset($content->prescription_date) ? $content->prescription_date : '';
		$prescription_image = isset($content->prescription_image) ? implode(',',$content->prescription_image) : '';

		$params = [
			'user_id' => $user_id,
			'name' => $name,
			'prescription_date' => $prescription_date,
			'prescription_image' => $prescription_image
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'name' => 'required',
            'prescription_date' => 'required',
            'prescription_image' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }	

		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){

		$find_name = Prescription::where(['user_id'=>$user_id,'name'=>$name,"is_delete"=>"0"])->get();
		if(count($find_name)>0){
			$response['status'] = 404;
			$response['message'] = 'Prescription name already exists';
		}else{
			$prescriptions = new Prescription();
			$prescriptions->user_id = $user_id;
			$prescriptions->name = $name;
			$prescriptions->image = $prescription_image;
			$prescriptions->prescription_date = date('Y-m-d H:i:s');
			$prescriptions->save();

			$code_data = explode(',',$prescriptions->image);
			foreach ($code_data as $value) {
				$abc= new multiple_prescription();
				$abc->user_id = $prescriptions->user_id;
				$abc->prescription_id = $prescriptions->id;
				$abc->prescription_name = $prescriptions->name;
				$abc->image = $value;
				$abc->prescription_date = $prescriptions->prescription_date;				
				$abc->save();
			}
			$response['status'] = 200;
			$response['message'] = 'Prescription saved successfully!';
			$response['data'] = (object)array();
		}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}

    public function delete_prescription_imagedata(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id  = isset($content->user_id) ? $content->user_id : 0;
		$prescription_id  = isset($content->prescription_id) ? $content->prescription_id : 0;
		$id  = isset($content->id) ? $content->id : 0;
		
		$params = [
			'user_id' => $user_id,
			'prescription_id'     => $prescription_id,
			'id'     => $id
		]; 
		
		$validator = Validator::make($params, [
			'user_id' => 'required',
			'prescription_id' => 'required',
            'id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$prescription = multiple_prescription::where(['user_id'=>$user_id,'prescription_id'=>$prescription_id,'id'=>$id])->first();
		$prescription->is_delete="1";
		$prescription->save();
		
		$response['status'] = 200;
		$response['message'] = 'prescription successfully deleted!';    
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
}	
