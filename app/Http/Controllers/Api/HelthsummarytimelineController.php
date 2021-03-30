<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Helthsummary;
use App\DiseaseReport;
use App\PrescriptionReport;
use App\new_users;
use App\FamilyMember; 
use App\Disease; 
use Validator;
use Mail;
use File;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class HelthsummarytimelineController extends Controller
{
	public function helthsummarytimeline(Request $request)
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
		$page = isset($content->page) ? $content->page : '';

		$user_id    = isset($content->user_id) ? $content->user_id : ''; 
		$family_member_id    = isset($content->family_member_id) ? $content->family_member_id : ''; 
		$search_text = isset($content->search_text) ? $content->search_text : '';
		$start_date = isset($content->start_date) ? $content->start_date : '';
		$end_date   = isset($content->end_date) ? $content->end_date : '';
		$disease_id = isset($content->disease_id) ? $content->disease_id : '';
		 		
		$params = [
			'user_id' => $user_id,
			'family_member_id' => $family_member_id
		];		
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'family_member_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$diseases = [];
		
		if (!empty($start_date) && !empty($end_date) && !empty($disease_id)) { 
									
			$disease_list = Helthsummary::select('helth_summary_timeline.id','helth_summary_timeline.user_id','helth_summary_timeline.disease_id', 'helth_summary_timeline.hospital_name', 'helth_summary_timeline.case_number', 'helth_summary_timeline.doctor_name', 'helth_summary_timeline.symptoms', 'helth_summary_timeline.doctor_remark', 'helth_summary_timeline.next_appointment','helth_summary_timeline.disease_date','helth_summary_timeline.report_list','d1.name','helth_summary_timeline.created_at')->whereBetween('helth_summary_timeline.created_at', [$start_date.' 00:00:00',$end_date.' 23:59:59'])->where(['helth_summary_timeline.user_id' => $family_member_id,'helth_summary_timeline.disease_id' => $disease_id])->leftJoin('disease as d1', 'd1.id', '=', 'helth_summary_timeline.disease_id');

			$total = $disease_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $disease_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];

		} else if (!empty($start_date) && !empty($end_date)) { 

		$disease_list = Helthsummary::select('helth_summary_timeline.id','helth_summary_timeline.user_id','helth_summary_timeline.disease_id', 'helth_summary_timeline.hospital_name', 'helth_summary_timeline.case_number', 'helth_summary_timeline.doctor_name', 'helth_summary_timeline.symptoms', 'helth_summary_timeline.doctor_remark', 'helth_summary_timeline.next_appointment','helth_summary_timeline.disease_date','helth_summary_timeline.report_list','d1.name','helth_summary_timeline.created_at')->whereBetween('helth_summary_timeline.created_at', [$start_date.' 00:00:00',$end_date.' 23:59:59'])->where(['helth_summary_timeline.user_id' => $family_member_id])->leftJoin('disease as d1', 'd1.id', '=', 'helth_summary_timeline.disease_id');	

			$total = $disease_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $disease_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];

		} else if (!empty($disease_id)) {

			$disease_list = Helthsummary::select('helth_summary_timeline.id','helth_summary_timeline.user_id','helth_summary_timeline.disease_id', 'helth_summary_timeline.hospital_name', 'helth_summary_timeline.case_number', 'helth_summary_timeline.doctor_name', 'helth_summary_timeline.symptoms', 'helth_summary_timeline.doctor_remark', 'helth_summary_timeline.next_appointment','helth_summary_timeline.disease_date','helth_summary_timeline.report_list','d1.name')->where(['helth_summary_timeline.user_id' => $family_member_id,'helth_summary_timeline.disease_id' => $disease_id])->leftJoin('disease as d1', 'd1.id', '=', 'helth_summary_timeline.disease_id');	

			$total = $disease_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $disease_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
		} else if ($search_text != '') {
		
			 $disease_list = Helthsummary::select('helth_summary_timeline.id','helth_summary_timeline.user_id','helth_summary_timeline.disease_id', 'helth_summary_timeline.hospital_name', 'helth_summary_timeline.case_number', 'helth_summary_timeline.doctor_name', 'helth_summary_timeline.symptoms', 'helth_summary_timeline.doctor_remark', 'helth_summary_timeline.next_appointment','helth_summary_timeline.disease_date','helth_summary_timeline.report_list','d1.name')->where('helth_summary_timeline.user_id' ,$family_member_id)->leftJoin('disease as d1', 'd1.id', '=', 'helth_summary_timeline.disease_id')->where('d1.name', 'like', $search_text.'%')->orderBy('helth_summary_timeline.id', 'DESC');	

			$total = $disease_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $disease_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
		} else {
			$disease_list = Helthsummary::select('helth_summary_timeline.id','helth_summary_timeline.user_id','helth_summary_timeline.disease_id', 'helth_summary_timeline.hospital_name', 'helth_summary_timeline.case_number', 'helth_summary_timeline.doctor_name', 'helth_summary_timeline.symptoms', 'helth_summary_timeline.doctor_remark', 'helth_summary_timeline.next_appointment','helth_summary_timeline.disease_date','helth_summary_timeline.report_list','d1.name')->leftJoin('disease as d1', 'd1.id', '=', 'helth_summary_timeline.disease_id')->where(['helth_summary_timeline.user_id' => $family_member_id])->orderBy('helth_summary_timeline.disease_date', 'DESC');

			$total = $disease_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $disease_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
		}
		
		if (count($data_array)>0) {        
			foreach($data_array as $value) {
				$diseases[] = [
					'id' => $value['id'],
					'treatement_name' => $value['name'],
					'hospital_name' => $value['hospital_name'],
					'case_number' => $value['case_number'],
					'doctor_name' => $value['doctor_name'],
					'symptoms' => $value['symptoms'],
					'doctor_remark' => $value['doctor_remark'],
					'next_appointment' => ($value['next_appointment'])?$value['next_appointment']:'',
					'report_list' => ($value['report_list'])?$value['report_list']:'',
					'date' => ($value['disease_date']!='')?$value['disease_date']: '',
					'day' => ($value['disease_date']!='') ? date('d', strtotime($value['disease_date'])) : '',
					'month' => ($value['disease_date']!='') ? date('M', strtotime($value['disease_date'])) : ''
				];
			}
			$response['status'] = 200;
		}else {
			$response['status'] = 404;
		}
		
		$response['message'] = 'Health Summary Timeline';
		$response['data']->content = $diseases;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	
	 
	public function disease_list(Request $request)
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
		$family_member_id    = isset($content->family_member_id) ? $content->family_member_id : ''; 
		 		
		$params = [
			'user_id' => $user_id,
			'family_member_id' => $family_member_id
		];		
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'family_member_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$diseases = [];
		
		$disease_list = Helthsummary::select('disease.name', 'disease.id')->join('disease', 'disease.id', '=', 'helth_summary_timeline.disease_id')->where(['helth_summary_timeline.user_id' => $family_member_id])->groupBy('disease.id')->get();
		
		
		if (!empty($disease_list)) {        
			foreach($disease_list as $value) {
				
				$diseases[] = [
					'id' => $value->id,
					'name' => isset($value->name) ? $value->name : ''
				];
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		
		$response['message'] = 'Health Summary disease';
		$response['data'] = $diseases;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	
	
	public function helthsummarytimeline_old(Request $request)
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
		$search_text = isset($content->search_text) ? $content->search_text : '';
		$start_date = isset($content->start_date) ? $content->start_date : '';
		$end_date   = isset($content->end_date) ? $content->end_date : '';
		$disease_id = isset($content->disease_id) ? $content->disease_id : '';
		 		
		$params = [
			'user_id'      => $user_id  
		];		
		
		$validator = Validator::make($params, [
            'user_id' => 'required' 
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$family_member = new_users::join('family_members', 'family_members.user_id', '=', 'users.id')->where('family_members.user_id', $user_id)->get();
		
		
		$helthsummary_arr = array();
		if(count($family_member)>0){
			foreach($family_member as $key=>$val){
				
				$diseases = [];
				
				if (!empty($start_date) && !empty($end_date) && !empty($disease_id)) { 
											
					$disease_list = Helthsummary::with(['diseases'])->whereRaw("(created_at>= ? AND created_at <= ?)", 
															[$start_date." 00:00:00", $end_date." 23:59:59"])->where('disease_id', $disease_id)->where('family_member_id', $val->family_member_id)->get();
				} else if (!empty($start_date) && !empty($end_date)) { 
													
					$family_member = Helthsummary::with(['diseases'])->whereRaw("(created_at>= ? AND created_at <= ?)", 
															[$start_date." 00:00:00", $end_date." 23:59:59"])->where('family_member_id', $val->family_member_id)->get();
				} else if (!empty($disease_id)) {
					$family_member = Helthsummary::with(['diseases'])->where('disease_id', $disease_id)->where('family_member_id', $val->family_member_id)->get();
				} else if ($search_text != '') {
					
					$family_member = Helthsummary::with(['diseases'  => function($q) use($search_text){
														return $q->where('name', 'like', '%' .$search_text . '%');
													}])->where('hospital_name', 'like', '%' .$search_text . '%')->orWhere('case_number', 'like', '%' .$search_text . '%')->orWhere('doctor_name', 'like', '%' .$search_text . '%')->where('family_member_id', $val->family_member_id)->get(); 
				}
				
				$disease_list = Helthsummary::with('disease')->where('family_member_id', $val->family_member_id)->get();
				
				if (!empty($disease_list)) {        
					foreach($disease_list as $value) {
						
						$diseases[] = [
							'id' => $value->id,
							'treatement_name' => isset($value->disease->name) ? $value->disease->name : '',
							'hospital_name' => $value->hospital_name,
							'case_number' => $value->case_number,
							'doctor_name' => $value->doctor_name,
							'symptoms' => $value->symptoms,
							'doctor_remark' => $value->doctor_remark,
							'next_appointment' => $value->next_appointment,
							'report_list' => $value->report_list,
							'date' => ($value->created_at!='')?date('Y-m-d',strtotime($value->created_at)): '',
							'day' => ($value->created_at!='') ? date('d', strtotime($value->created_at)) : '',
							'month' => ($value->created_at!='') ? date('M', strtotime($value->created_at)) : ''
						];
					}
				}
				
				$helthsummary_arr[] = [
					'id' => $val->family_member_id,
					'patient_name' => $val->name,
					'diseases' => $diseases,
				];
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Health Summary Timeline';
		$response['data'] = $helthsummary_arr;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	public function helthsummarytimelinedetail(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		// $timeline_id = $request->timeline_id; 
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText); 
		
		$user_id     = isset($content->user_id) ? $content->user_id : '';
		$timeline_id = isset($content->timeline_id) ? $content->timeline_id : '';
		
		$params = [
			'user_id'     => $user_id,
			'timeline_id' => $timeline_id
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'timeline_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$helthsummary = Helthsummary::with(['disease_report', 'prescription_report'])->where('id','=',$timeline_id)->first();
		$helthsummary_arr = array();
		if(!empty($helthsummary)){
			$diseasess = Disease::select('name')->where('id', $helthsummary->disease_id)->first();
			$family_member = new_users::select('id','name')->where('id', $helthsummary->user_id)->first();
			
			$report_list = [];
			if (count($helthsummary->disease_report) > 0) {
				foreach($helthsummary->disease_report as $val) {
					
					$image_url = '';
					if (!empty($val->image_name)) {

						$filename = storage_path('app/public/uploads/disease/' . $val->image_name);
					
						if (File::exists($filename)) {
							$image_url = asset('storage/app/public/uploads/disease/' . $val->image_name);
						}
					}
			
					$report_list[] = [
						'id' => $val->id,
						'title' => $val->title,
						'image' => $image_url
					];
				}
			}
			
			$prescription_report = [];
			if (count($helthsummary->prescription_report) > 0) {
				foreach($helthsummary->prescription_report as $val) {
					
					$image_url = '';
					if (!empty($val->image_name)) {

						$filename = storage_path('app/public/uploads/prescription_report/' . $val->image_name);
					
						if (File::exists($filename)) {
							$image_url = asset('storage/app/public/uploads/prescription_report/' . $val->image_name);
						}
					}
			
					$prescription_report[] = [
						'id' => $val->id,
						'title' => $val->title,
						'image' => $image_url
					];
				}
			}
					
			$helthsummary_arr = [
				'id' => $helthsummary->id,
				'treatement_name' => isset($diseasess->name) ? $diseasess->name : '',
				'family_member_id' => $family_member->id,
				'patient_name' => $family_member->name,
				'hospital_name' => $helthsummary->hospital_name,
				'case_number' => $helthsummary->case_number,
				'doctor_name' => $helthsummary->doctor_name,
				'symptoms' => $helthsummary->symptoms,
				'doctor_remark' => $helthsummary->doctor_remark,
				'next_appointment' => $helthsummary->next_appointment,
				'disease_date' => $helthsummary->disease_date,
				'report_list' => $report_list,
				'prescription_list' => $prescription_report,
				'date' => ($helthsummary->created_at!='')?date('Y-m-d',strtotime($helthsummary->created_at)): ''
			];
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		
		$response['message'] = 'Health Summary Timeline detail';
		$response['data'] = $helthsummary_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey); 
		
        return response($cipher, 200);
	
	}
	public function add_disease(Request $request)
    {
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();  
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		
		$user_id     = isset($content->user_id) ? $content->user_id : '';
		$patient_name = isset($content->patient_name) ? $content->patient_name : '';
		
		$family_member_id  = isset($content->family_member_id) ? $content->family_member_id : 0;
		
		$disease_id = isset($content->disease_id) ? $content->disease_id : '';
		$treatement_name = isset($content->treatement_name) ? $content->treatement_name : '';
		$hospital_name = isset($content->hospital_name) ? $content->hospital_name : '';
		$case_number = isset($content->case_number) ? $content->case_number : '';
		$disease_date = isset($content->disease_date) ? $content->disease_date : '';
		$doctor_name = isset($content->doctor_name) ? $content->doctor_name : '';
		$symptoms = isset($content->symptoms) ? $content->symptoms : '';
		$doctor_remark = isset($content->doctor_remark) ? $content->doctor_remark : '';
		$next_appointment = isset($content->next_appointment) ? date('Y-m-d',strtotime($content->next_appointment)) : '';
		$report_list = isset($content->report_list) ? $content->report_list : ''; 
		$prescription_list = isset($content->prescription_list) ? $content->prescription_list : '';
		
		$params = [
			'user_id'     => $user_id,
			'disease_id' => $disease_id
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'disease_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$updateData = [
			'user_id' => $family_member_id,
			'disease_id' => $disease_id,
			'hospital_name' => $hospital_name,
			'case_number' => $case_number,
			'disease_date' => $disease_date,
			'doctor_name' => $doctor_name,
			'symptoms' => $symptoms,
			'doctor_remark' => $doctor_remark,
			// 'next_appointment' => $next_appointment,
		];
		$data = Helthsummary::updateOrCreate(['id' => null], $updateData );
		
		if($data){
			
			$destinationPath = 'storage/app/public/uploads/disease/' ; 
			$images=array();
			if($files=$request->file('report_images')){
				
				foreach($files as $key => $file){
					
					$filename= time().'-'.$file->getClientOriginalName();
					$tesw = $file->move($destinationPath, $filename);
					$disease_report = new DiseaseReport();
					$disease_report->disease_id = $data->id;
					$disease_report->image_name = $filename;
					$disease_report->title = isset($report_list[$key]) ? $report_list[$key] : '';
					$disease_report->save();
				}
			}

			/*$files=$request->file('report_images');
			$filename= time().'-'.$files->getClientOriginalName();
			echo $filename;*/
			
			$destinationPath = 'storage/app/public/uploads/prescription_report/' ; 
			$images=array(); 
			if($files=$request->file('prescription_report_images')){
				
				foreach($files as $key => $file){
					
					$filename= time().'-'.$file->getClientOriginalName();
					$tesw = $file->move($destinationPath, $filename);
					$prescription_report = new PrescriptionReport();
					$prescription_report->disease_id = $data->id; 
					$prescription_report->image_name = $filename;
					$prescription_report->title =isset($report_list[$key]) ? $report_list[$key] : '';
					$prescription_report->save();
				}
			}

			/*$files=$request->file('prescription_report_images');
			$filename= time().'-'.$files->getClientOriginalName();
			echo $filename;*/
			
			$response['status'] = 200;
			$response['message'] = 'Disease successfully added!';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Error occured!';
		}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	public function edit_disease(Request $request)
    {
		$response = array();
				
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		// $id = $content->id;
		$id     = isset($content->id) ? $content->id : '';
		$user_id     = isset($content->user_id) ? $content->user_id : '';
		$family_member_id = isset($content->family_member_id) ? $content->family_member_id : '';
		$disease_id = isset($content->disease_id) ? $content->disease_id : '';
		$hospital_name = isset($content->hospital_name) ? $content->hospital_name : '';
		$case_number = isset($content->case_number) ? $content->case_number : '';
		$doctor_name = isset($content->doctor_name) ? $content->doctor_name : '';
		$symptoms = isset($content->symptoms) ? $content->symptoms : '';
		$doctor_remark = isset($content->doctor_remark) ? $content->doctor_remark : '';
		$next_appointment = isset($content->next_appointment) ? $content->next_appointment : '';
		$disease_date = isset($content->disease_date) ? $content->disease_date : '';
		$report_list = isset($content->report_list) ? $content->report_list : '';
		$prescription_list = isset($content->prescription_list) ? $content->prescription_list : '';
		$delete_prescription_list = isset($content->delete_prescription_list) ? $content->delete_prescription_list : '';
		$delete_report_list = isset($content->delete_report_list) ? $content->delete_report_list : '';

		$params = [
			'user_id'     => $user_id,
			'family_member_id' => $family_member_id,
			'disease_id' => $disease_id,
			// 'hospital_name' => $hospital_name,
			// 'case_number' => $case_number,
			// 'doctor_name' => $doctor_name,
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'family_member_id' => 'required',
            'disease_id' => 'required',
            // 'hospital_name' => 'required',
            // 'case_number' => 'required',
            // 'doctor_name' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$helthsummary = Helthsummary::find($id);
		if($helthsummary){
			$helthsummary->user_id = $family_member_id;
			$helthsummary->disease_id = $disease_id;
			$helthsummary->hospital_name = $hospital_name;
			$helthsummary->case_number = $case_number;
			$helthsummary->doctor_name = $doctor_name;
			$helthsummary->symptoms = $symptoms;
			$helthsummary->doctor_remark = $doctor_remark;
			$helthsummary->disease_date = $disease_date;
			// $helthsummary->next_appointment = $next_appointment;
			if($helthsummary->save()){
				
				$destinationPath = 'storage/app/public/uploads/disease/' ; 
				$images=array();
				if($files=$request->file('report_images')){
					foreach($files as $key => $file){		
						$filename= time().'-'.$file->getClientOriginalName();
						$tesw = $file->move($destinationPath, $filename);
						
						$disease_report = new DiseaseReport();
						$disease_report->disease_id = $id;
						$disease_report->image_name = $filename;
						$disease_report->title = isset($report_list[$key]) ? $report_list[$key] : '';
						$disease_report->save();
					}
				}
				
				$destinationPath = 'storage/app/public/uploads/prescription_report/' ; 
				$images=array();
				if($files=$request->file('prescription_report_images')){
					foreach($files as $key => $file){
						$filename= time().'-'.$file->getClientOriginalName();
						$tesw = $file->move($destinationPath, $filename);
						
						$prescription_report = new PrescriptionReport();
						$prescription_report->disease_id = $id;
						$prescription_report->image_name = $filename;
						$prescription_report->title = isset($prescription_list[$key]) ? $prescription_list[$key] : '';
						$prescription_report->save();
					}
				}
				
				if(!empty($delete_report_list)){
					foreach ($delete_report_list as $value) {
						$disease_report = DiseaseReport::find($value);
						$filename = storage_path('app/public/uploads/disease/' . $disease_report->image_name);
						if (File::exists($filename)) {
							File::delete($filename);
						}
						$disease_report->delete();
					}
				}
				if(!empty($delete_prescription_list)){
					foreach ($delete_prescription_list as $value) {
						$disease_report = PrescriptionReport::find($value);
						$filename = storage_path('app/public/uploads/prescription_report/' . $disease_report->image_name);
						if (File::exists($filename)) {
							File::delete($filename);
						}
						$disease_report->delete();
					}
				}
				$response['status'] = 200;
				$response['message'] = 'Disease successfully updated!';
			}else{
				$response['status'] = 404;
				$response['message'] = 'Error occured!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Record not found';
		}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	public function get_family_members(Request $request)
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
		
		$user_id      = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'user_id'      => $user_id
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
		/*$helthsummary = FamilyMember::select('users.id','users.name')->leftJoin('users', function($join) {
                $join->on('users.id', '=', 'family_members.family_member_id');
              })->where('family_members.user_id','=',$user_id)->get();*/
        $helthsummary = FamilyMember::select('new_users.id','new_users.name')
        ->leftjoin('new_users','family_members.family_member_id','=','new_users.id')
        ->where('family_members.user_id','=',$user_id)
        ->get();

		
		if(count($helthsummary)>0){
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Family Member List';
		$response['data'] = $helthsummary;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	
	public function get_disease()
	{
		$response = array();
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array(); 
		
		 
		$diseases = Disease::select('id','name')->get();
		if(count($diseases)>0){ 
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Disease List'; 
		$response['data'] = $diseases;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	
	public function delete_disease_report(Request $request)
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
		
		$disease_report = DiseaseReport::find($id);
		
		$filename = storage_path('app/public/uploads/disease/' . $disease_report->image_name);
            
		if (File::exists($filename)) {
			File::delete($filename);
		}
		
		$disease_report->delete();
		
		$response['status'] = 200;
		$response['message'] = 'Disease successfully deleted!';    
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	public function delete_presscription_report(Request $request)
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
		
		$disease_report = PrescriptionReport::find($id);
		
		$filename = storage_path('app/public/uploads/prescription_report/' . $disease_report->image_name);
            
		if (File::exists($filename)) {
			File::delete($filename);
		}
		
		$disease_report->delete();
		
		$response['status'] = 200;
		$response['message'] = 'Prescription successfully deleted!';    
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	public function check_valid_user_code(Request $request)
    {
		$response = array();
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$user_id = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'mobile_number' => $mobile_number,
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$login = new_users::where('mobile_number', $mobile_number)->first();
        if($login) 
		{
			$duplicate_member = FamilyMember::where(['family_member_id' => $login->id, 'user_id' => $user_id])->first();
				
			if (!$duplicate_member) {
					
				$verification_code = rand(1111,9999);//Str::random(6);
				$name  = $login->name;
				$email  = $login->email;
				
				$data = [
					'name' => $name,
					'otp' => $verification_code,
				];
				$message       = "Add Member OTP " . $verification_code;
				$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
				$sms = file_get_contents($api);
				/*$result = Mail::send('email.sendotp', $data, function ($message) use ($email) {

					$message->to($email)->subject('Pharma App : Add Family Member OTP');

				});*/
				$user = new_users::find($login->id);
				$user->otp = $verification_code;
				$user->otp_time = date('Y-m-d H:i:s');
				$user->save();
					
				$response['status'] = 200;
				$response['message'] = 'Verification code sent successfully';
			
				$response['data']['otp'] = $user->otp;
				$response['data']['family_member_id'] = $login->id; 
			} else {
				$response['status'] = 404; 
				$response['message'] = 'Family member already added!';
			}
        } 
		else 
		{
			$response['data'] = (object)array();
			
			$response['status'] = 404;
			$response['message'] = 'Invalid mobile number!';
        } 
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
	
	public function verify_family_member_otp(Request $request)
    {
		$response = array();
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$family_member_id = isset($content->family_member_id) ? $content->family_member_id : '';
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$otp = isset($content->otp) ? $content->otp : '';
		
		$params = [
			'family_member_id' => $family_member_id,
			'user_id' => $user_id,
			'otp' => $otp
		];
		
		$validator = Validator::make($params, [
            'family_member_id' => 'required',
            'user_id' => 'required',
            'otp' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$login = new_users::where('id', $family_member_id)->first();
        if($login) 
		{
			if ($otp == $login->otp) {
				
				$current = date("Y-m-d H:i:s");
				$otp_time = $login->otp_time;
				$diff = strtotime($current) - strtotime($otp_time);
				$days    = floor($diff / 86400);
				$hours   = floor(($diff - ($days * 86400)) / 3600);
				$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
				if (($diff > 0) && ($minutes <= 10)) {
					
					
					$user = new_users::find($login->id);
					$user->otp = ''; 
					$user->save();
					
					$family_member = new FamilyMember();
					$family_member->user_id = $user_id;
					$family_member->family_member_id = $login->id;
					$family_member->save();
					
					$response['status'] = 200;
					$response['message'] = 'Family member added successfully!';
					
					
				} else {
					$response['status'] = 404;
					$response['message'] = 'OTP Expired';
				}
			} else {
				$response['status'] = 404;
				$response['message'] = 'OTP is not valid';
			}
			
        } 
		else 
		{
			$response['status'] = 404;
            $response['message'] = 'Mobile Number already exist';
			
        }
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }


}	
