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
use DB;
use App\prescription_multiple_image;
use Illuminate\Support\Str;
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
				
				$images_array=[];
                                $image_data = prescription_multiple_image::where('prescription_id',$val['id'])->get();
                                foreach ($image_data as $pres) {
                                     $pres_image = '';
                                        if (!empty($pres->image)) {

                                            $filename = storage_path('app/public/uploads/prescription/' .  $pres->image);
                                        
                                            if (File::exists($filename)) {
                                                $pres_image = asset('storage/app/public/uploads/prescription/' .  $pres->image);
                                            } else {
                                                $pres_image = '';
                                            }
                                        }
                                    $images_array[] =[
                                        'id' => $pres->id,
                                        'image' => $pres_image
                                    ];
                                }
				$prescription_arr[$key]['id'] = $val['id'];
				$prescription_arr[$key]['name'] = $val['name'];
				$prescription_arr[$key]['date'] = date('d-m-Y', strtotime($val['created_at']));
				$prescription_arr[$key]['image'] = $images_array;
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
		$find_name = Prescription::where(['user_id'=>$user_id,'name'=>$name,"is_delete"=>"0"])->get();
		if(count($find_name)>0){
			$response['status'] = 404;
			$response['message'] = 'Prescription name already exists';
		}else{
			$prescriptions = new Prescription();
			$prescriptions->user_id = $user_id;
			$prescriptions->name = $name;
			//$prescriptions->image = $prescription_image;
			$prescriptions->prescription_date = $prescription_date;
			$prescriptions->save();

			$prescription_image = '';
			if ($request->hasFile('prescription')) {
				
				$destinationPath = 'storage/app/public/uploads/prescription/' ; 
				$images=array();
				if($files=$request->file('prescription')){
					
					foreach($files as $key => $file){
						$check_table_empty = multiple_prescription::all();
						$last_id = multiple_prescription::latest('multiple_prescription_id')->first();
						if(!empty($last_id)){
							$update_id = $last_id->multiple_prescription_id + 1;	
						}
						$abc= new multiple_prescription();
						$abc->multiple_prescription_id=(count($check_table_empty)==0)?1:$update_id;
						$abc->user_id = $prescriptions->user_id;
						$abc->prescription_id = $prescriptions->id;
						$abc->prescription_name = $prescriptions->name;
						$abc->image = base64_encode(file_get_contents($file));
						$abc->path = asset('storage/app/public/uploads/prescription/' . $file);
						$abc->prescription_date = $prescriptions->prescription_date;
						$abc->is_delete = "0";
						$abc->created_at = date('Y-m-d H:i:s');
						$abc->updated_at = date('Y-m-d H:i:s');				
						$abc->save();

						//restore image and PDF
						/*$image = $abc->image;  // your base64 encoded
						if(str_replace('data:image/png;base64,', '', $image)){
							$image = str_replace('data:image/png;base64,', '', $image);
						    $image = str_replace(' ', '+', $image);
						    $imageName = str::random(10) . '.png';
							Storage::disk('public')->put('uploads/prescription_restore/'.$imageName, base64_decode($image), 'public');
						}


						if(str_replace('data:pdf/pdf;base64,', '', $image)){
							 $image = str_replace('data:pdf/pdf;base64,', '', $image);
					    	 $image = str_replace(' ', '+', $image);
						     $imageName = str::random(10) . '.pdf';
							 Storage::disk('public')->put('uploads/prescription_restore/'.$imageName, base64_decode($image), 'public');
						}*/
					    

						$filename= time().'-'.$file->getClientOriginalName();
						$tesw = $file->move($destinationPath, $filename);
						$prescription_multiple_image = new prescription_multiple_image();
						$prescription_multiple_image->prescription_id = $prescriptions->id;
						$prescription_multiple_image->user_id = $prescriptions->user_id;
						$prescription_multiple_image->name = $prescriptions->name;
						$prescription_multiple_image->image = $filename;
						$prescription_multiple_image->prescription_date = $prescriptions->prescription_date;
						$prescription_multiple_image->is_delete = '0';
						$prescription_multiple_image->save();
					}
				}
			} else {
				$response['status'] = 404;
				$response['message'] = 'Please upload prescription';
				
				return response($response, 200);
			}
			

			$response['status'] = 200;
			$response['message'] = 'Your prescription has been successfully added';
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
		
		$delete_pre = multiple_prescription::where(['prescription_id'=>(int)$id])->first();
		$delete_pre->is_delete='1';
		$delete_pre->save();

		$response['status'] = 200;
		$response['message'] = 'Your prescription has been successfully deleted';    
		
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
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data); 
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$name = isset($content->name) ? $content->name : '';
		$prescription_date = isset($content->prescription_date) ? $content->prescription_date : '';
		$prescription_image = isset($content->prescription_image) ? implode(' ',$content->prescription_image) : '';

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

		/*$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){*/

		$find_name = Prescription::where(['user_id'=>$user_id,'name'=>$name,"is_delete"=>"0"])->get();
		if(count($find_name)>0){
			$response['status'] = 404;
			$response['message'] = 'Prescription name already exists';
		}elseif (empty($prescription_image)) {
			$response['status'] = 404;
			$response['message'] = 'Please upload prescription';
		}else{
			$prescriptions = new Prescription();
			$prescriptions->user_id = $user_id;
			$prescriptions->name = $name;
			//$prescriptions->image = $prescription_image;
			$prescriptions->prescription_date = date('Y-m-d H:i:s');
			$prescriptions->save();
				$code_data = explode(' ',$prescription_image);
				foreach ($code_data as $value) {
					$check_table_empty = multiple_prescription::all();
					$last_id = multiple_prescription::latest('multiple_prescription_id')->first();
					if(!empty($last_id)){
						$update_id = $last_id->multiple_prescription_id + 1;	
					}
					$abc= new multiple_prescription();
					$abc->multiple_prescription_id=(count($check_table_empty)==0)?1:$update_id;
					$abc->user_id = $prescriptions->user_id;
					$abc->prescription_id = $prescriptions->id;
					$abc->prescription_name = $prescriptions->name;
					$abc->image = $value;
					$abc->prescription_date = $prescriptions->prescription_date;
					$abc->is_delete = "0";
					$abc->created_at = date('Y-m-d H:i:s');
					$abc->updated_at = date('Y-m-d H:i:s');				
					$abc->save();
				}
			$response['status'] = 200;
			$response['message'] = 'Prescription saved successfully!';
			$response['data'] = (object)array();
		}
		/*}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}*/
        $response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($response, 200);
	
	}

    public function delete_prescription_imagedata(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data);
		
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
		
		$prescription = multiple_prescription::where(['user_id'=>(int)$user_id,'prescription_id'=>(int)$prescription_id,'multiple_prescription_id'=>(int)$id])->first();
		$prescription->is_delete='1';
		$prescription->save();
		$all_delete = multiple_prescription::where(['user_id'=>(int)$user_id,'prescription_id'=>(int)$prescription_id,'is_delete'=>'0'])->get();
		if(count($all_delete) == 0){
			$delete_pre = Prescription::where(['id'=>$prescription_id,'user_id'=>$user_id])->first();
			$delete_pre->is_delete='1';
			$delete_pre->save();
		}
		$response['status'] = 200;
		$response['message'] = 'prescription successfully deleted!';    
		
		$response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($response, 200);
    }
    public function prescription_list_imagedata(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data);
		
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
		/*$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){*/
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
				$mutiple_data = multiple_prescription::where(['prescription_id'=>$val['id'],'is_delete'=>'0'])->get();
				$mutiple_images = [];
				foreach ($mutiple_data as $value) {
						$mutiple_images[]=[
						'id'	=> $value->multiple_prescription_id,
						'image' => $value->image,
					];	
				}
				$prescription_arr[$key]['id'] = $val['id'];
				$prescription_arr[$key]['name'] = $val['name'];
				$prescription_arr[$key]['date'] = date('d-m-Y', strtotime($val['created_at']));
				$prescription_arr[$key]['image_array'] = $mutiple_images;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		} 
		$response['message'] = 'Prescription List';
		$response['data']->content = $prescription_arr;
		/*}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	            $response['data'] = [];
	   	}*/
        $response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($response, 200);
	
	}

}	
