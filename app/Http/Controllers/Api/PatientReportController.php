<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\patient_report_image;
use App\patient_report;
use Illuminate\Validation\ValidationException;
use Validator;
use Storage;
use Image;
use File;
use DB;
use Illuminate\Support\Str;
use App\patient_report_multiple_image;
class PatientReportController extends Controller
{
    public function patient_report_add(Request $request)
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
		$date = isset($content->date) ? $content->date : '';
		$remarks = isset($content->remarks) ? $content->remarks : '';

		$params = [
			'user_id' => $user_id,
			'name' => $name,
			'date' => $date,
			'remarks' => $remarks,
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'name' => 'required',
            'date' => 'required',
            'remarks' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }	

		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){

		/*$patient_report_image = '';
		if ($request->hasFile('patient_report')) {
			
			$image         = $request->file('patient_report');
			$patient_report_image = time() . '.' . $image->getClientOriginalExtension();

			$img = Image::make($image->getRealPath());
			$img->stream(); // <-- Key point

			Storage::disk('public')->put('uploads/patient_report/'.$patient_report_image, $img, 'public');
		} else {
			$response['status'] = 404;
			$response['message'] = 'Please upload patient_report';
			
			return response($response, 200);
		}*/
		$find_name = patient_report::where(['user_id'=>$user_id,'name'=>$name,"is_delete"=>"0"])->get();
		if(count($find_name)>0){
			$response['status'] = 404;
			$response['message'] = 'Report name already exists';
		}else{
			$reports = new patient_report();
			$reports->user_id = $user_id;
			$reports->name = $name;
			//$reports->image = $patient_report_image;
			$reports->date = date('Y-m-d H:i:s');
			$reports->remarks = $remarks;
			$reports->save();
			
			$prescription_image = '';
			if ($request->hasFile('patient_report')) {
				
				$destinationPath = 'storage/app/public/uploads/patient_report/' ; 
				$images=array();
				if($files=$request->file('patient_report')){
					
					foreach($files as $key => $file){
						$check_table_empty = patient_report_image::all();
						$last_id = patient_report_image::latest('patient_report_image_id')->first();
						if(!empty($last_id)){
							$update_id = $last_id->patient_report_image_id + 1;	
						}
						$abc= new patient_report_image();
						$abc->patient_report_image_id=(count($check_table_empty)==0)?1:$update_id;
						$abc->user_id = $reports->user_id;
						$abc->patient_report_id = $reports->id;
						$abc->name = $reports->name;
						$abc->image = base64_encode(file_get_contents($file));
						$abc->mimetype =  $file->getMimeType();
						$abc->path = asset('storage/app/public/uploads/patient_report/' . $file);
						$abc->date = $reports->date;
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
						$mimetype = $file->getMimeType();
						$tesw = $file->move($destinationPath, $filename);
						$reports_multiple_image = new patient_report_multiple_image();
						$reports_multiple_image->patient_report_id = $reports->id;
						$reports_multiple_image->user_id = $reports->user_id;
						$reports_multiple_image->name = $reports->name;
						$reports_multiple_image->image = $filename;
						$reports_multiple_image->mimetype = $mimetype;
						$reports_multiple_image->date = $reports->date;
						$reports_multiple_image->is_delete = '0';
						$reports_multiple_image->save();
					}
				}
			} else {
				$response['status'] = 404;
				$response['message'] = 'Please upload prescription';
				
				return response($response, 200);
			}
				
			$response['status'] = 200;
			$response['message'] = 'Report saved successfully!';
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

    public function patient_report_delete(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id  = isset($content->user_id) ? $content->user_id : 0;
		$id  = isset($content->id) ? $content->id : 0;

		$params = [
			'user_id' => $user_id,
			'id'     => $id,
		]; 
		
		$validator = Validator::make($params, [
			'user_id' => 'required',
            'id' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }

		$delete_re = patient_report_image::where(['patient_report_id'=>(int)$id,'user_id'=>(int)$user_id])->get();
		foreach ($delete_re as $value) {
			$value->is_delete='1';
			$value->save();
		}

		$report_data = patient_report_multiple_image::where(['patient_report_id'=>$id])->get();
		foreach ($report_data as $val) {
			$val->is_delete='1';
			$val->save();	
		}
		
		$patient_report = patient_report::where(['id'=>$id,'user_id'=>$user_id])->first();
		$patient_report->is_delete='1';
		$patient_report->save();

		$response['status'] = 200;
		$response['message'] = 'Report successfully deleted!';    
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }

    public function patient_report_display(Request $request)
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
			$report = patient_report::select('id', 'name','image', 'created_at')->where('name', 'like', '%'.$searchtext.'%')->where(['user_id'=>$user_id,"is_delete"=>"0"])->orderBy('id', 'DESC');

			$total = $report->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $report->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
		} else {
			$report = patient_report::select('id', 'name','image', 'created_at')->where(['user_id'=>$user_id,"is_delete"=>"0"])->orderBy('id', 'DESC');

			$total = $report->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $report->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
		}
		
		$report_arr = array();
		if(count($data_array)>0){
			foreach($data_array as $key=>$val){
				/*$file_name = '';
				if (!empty($val['image'])) {
					if (file_exists(storage_path('app/public/uploads/patient_report/'.$val['image']))){
						$file_name = asset('storage/app/public/uploads/patient_report/' . $val['image']);
					}
				}*/
				$images_array=[];
                                $image_data = patient_report_multiple_image::where('patient_report_id',$val['id'])->get();
                                foreach ($image_data as $pres) {
                                     $pres_image = '';
                                        if (!empty($pres->image)) {

                                            $filename = storage_path('app/public/uploads/patient_report/' .  $pres->image);
                                        
                                            if (File::exists($filename)) {
                                                $pres_image = asset('storage/app/public/uploads/patient_report/' .  $pres->image);
                                            } else {
                                                $pres_image = '';
                                            }
                                        }
                                    $images_array[] =[
                                        'id' => $pres->id,
                                        'image' => $pres_image,
                                        'mimetype' => $pres->mimetype
                                    ];
                                }
				$report_arr[$key]['id'] = $val['id'];
				$report_arr[$key]['name'] = $val['name'];
				$report_arr[$key]['date'] = date('d-m-Y', strtotime($val['created_at']));
				$report_arr[$key]['image'] = $images_array;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		} 
		$response['message'] = 'Report List';
		$response['data']->content = $report_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	            $response['data'] = [];
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}

	public function patient_report_edit_name(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id  = isset($content->user_id) ? $content->user_id : 0;
		$id  = isset($content->id) ? $content->id : 0;
		$patient_report_name  = isset($content->patient_report_name) ? $content->patient_report_name : '';

		$params = [
			'user_id' => $user_id,
			'id'     => $id,
			'patient_report_name' => $patient_report_name
		]; 
		
		$validator = Validator::make($params, [
			'user_id' => 'required',
            'id' => 'required',
            'patient_report_name' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$update_pre = patient_report_image::where(['user_id'=>(int)$user_id,'patient_report_id'=>(int)$id])->get();
		foreach ($update_pre as $value) {
			$value->name=$patient_report_name;
			$value->save();
		}

		$prescription_data = patient_report_multiple_image::where(['user_id'=>$user_id,'patient_report_id'=>$id])->get();
		foreach ($prescription_data as $val) {
			$val->name=$patient_report_name;
			$val->save();
		}

		$patient_report = patient_report::where(['id'=>$id,'user_id'=>$user_id])->first();
		$patient_report->name=$patient_report_name;
		$patient_report->save();

		$response['status'] = 200;
		$response['message'] = 'Your patient report has been successfully updated';    
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
}
