<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Helthsummary;
use App\DiseaseReport;
use App\new_users;
use App\Disease; 
use Validator;

class New_helthsummarytimelinecontroller extends Controller
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
}
