<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\patient_report_image;
use App\patient_report;
use Illuminate\Validation\ValidationException;
use Validator;

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
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data); 
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$name = isset($content->name) ? $content->name : '';
		$date = isset($content->date) ? $content->date : '';
		$remarks = isset($content->remarks) ? $content->remarks : '';
		$image = isset($content->image) ? implode(' ',$content->image) : '';

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

		/*$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){*/

		$find_name = patient_report::where(['user_id'=>$user_id,'name'=>$name,"is_delete"=>"0"])->get();
		if(count($find_name)>0){
			$response['status'] = 404;
			$response['message'] = 'Report name already exists';
		}elseif (empty($image)) {
			$response['status'] = 404;
			$response['message'] = 'Please upload report';
		}else{
			$reports = new patient_report();
			$reports->user_id = $user_id;
			$reports->name = $name;
			$reports->date = date('Y-m-d H:i:s');
			$reports->remarks = $remarks;
			$reports->save();
				$code_data = explode(' ',$image);
				foreach ($code_data as $value) {
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
					$abc->image = $value;
					$abc->date = $reports->date;
					$abc->is_delete = "0";
					$abc->created_at = date('Y-m-d H:i:s');
					$abc->updated_at = date('Y-m-d H:i:s');				
					$abc->save();
				}
			$response['status'] = 200;
			$response['message'] = 'Report saved successfully!';
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

    public function patient_report_delete(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data);
		
		$user_id  = isset($content->user_id) ? $content->user_id : 0;
		$patient_report_id  = isset($content->patient_report_id) ? $content->patient_report_id : 0;
		$id  = isset($content->id) ? $content->id : 0;
		
		$params = [
			'user_id' => $user_id,
			'patient_report_id'     => $patient_report_id,
			'id'     => $id
		]; 
		
		$validator = Validator::make($params, [
			'user_id' => 'required',
			'patient_report_id' => 'required',
            'id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$report = patient_report_image::where(['user_id'=>(int)$user_id,'patient_report_id'=>(int)$patient_report_id,'patient_report_image_id'=>(int)$id])->first();
		$report->is_delete='1';
		$report->save();
		$all_delete = patient_report_image::where(['user_id'=>(int)$user_id,'patient_report_id'=>(int)$patient_report_id,'is_delete'=>'0'])->get();
		if(count($all_delete) == 0){
			$delete_pre = patient_report::where(['id'=>$patient_report_id,'user_id'=>$user_id])->first();
			$delete_pre->is_delete='1';
			$delete_pre->save();
		}
		$response['status'] = 200;
		$response['message'] = 'Report successfully deleted!';    
		
		$response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($response, 200);
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
			$report = patient_report::select('id', 'name', 'created_at')->where('name', 'like', '%'.$searchtext.'%')->where(['user_id'=>$user_id,"is_delete"=>"0"])->orderBy('id', 'DESC');

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
			$report = patient_report::select('id', 'name', 'created_at')->where(['user_id'=>$user_id,"is_delete"=>"0"])->orderBy('id', 'DESC');

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
				$mutiple_data = patient_report_image::where(['patient_report_id'=>$val['id'],'is_delete'=>'0'])->get();
				$mutiple_images = [];
				foreach ($mutiple_data as $value) {
						$mutiple_images[]=[
						'id'	=> $value->patient_report_image_id,
						'image' => $value->image,
					];	
				}
				$report_arr[$key]['id'] = $val['id'];
				$report_arr[$key]['name'] = $val['name'];
				$report_arr[$key]['date'] = date('d-m-Y', strtotime($val['created_at']));
				$report_arr[$key]['image_array'] = $mutiple_images;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		} 
		$response['message'] = 'Report List';
		$response['data']->content = $report_arr;
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
