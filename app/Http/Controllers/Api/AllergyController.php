<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\new_users;
use App\Allergy;
use App\FamilyMember;
use Validator;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class AllergyController extends Controller
{
	public function healthsummaryallergies(Request $request)
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
		
		$user_id          = isset($content->user_id) ? $content->user_id : '';
		$family_member_id = isset($content->family_member_id) ? $content->family_member_id : '';
		$page = isset($content->page) ? $content->page : '';
		
		$params = [
			'user_id' => $user_id,
			'family_member_id' => $family_member_id,
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'family_member_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		// $family_mamber = FamilyMember::with('allergy')->where('user_id', $user_id)->get();
		
		$allergy_list = Allergy::select('user_id','allergy_date','id','allergy_name')->where(['user_id' => $family_member_id])->orderBy('allergy_date', 'DESC');

		$total = $allergy_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $allergy_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
		
		$allergys = [];
		if (!empty($data_array)) {
			foreach($data_array as $value) {
				
				$allergys[] = [
					'id' => $value['id'],
					'date' => $value['allergy_date'],
					'day' => date('d', strtotime($value['allergy_date'])),
					'month' => date('M', strtotime($value['allergy_date'])),
					'allergy_name' => $value['allergy_name']
				];
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		
		$response['message'] = 'Health Summary Allergies';
		$response['data']->content = $allergys;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey); 
		
        return response($cipher, 200);
	
	}
	public function healthsummaryallergies_old(Request $request)
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
		
		$user_id      = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'user_id' => $user_id
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		// $family_mamber = FamilyMember::with('allergy')->where('user_id', $user_id)->get();
		
		$family_member = new_users::join('family_members', 'family_members.user_id', '=', 'new_users.id')->where('family_members.user_id', $user_id)->orderBy('allergy_date', 'DESC')->get();
		
		$allergy_arr = array();
		if(count($family_member)>0){
			foreach($family_member as $key=>$val){
				
				$allergy_list = Allergy::where('family_member_id', $val->family_member_id)->get();
				
				$allergys = [];
				if (!empty($allergy_list)) {
					foreach($allergy_list as $value) {
						
						$allergys[] = [
							'id' => $value->id,
							'date' => date('d/m/Y', strtotime($value->allergy_date)),
							'day' => date('d', strtotime($value->allergy_date)),
							'month' => date('M', strtotime($value->allergy_date)),
							'allergy_name' => $value->allergy_name
						];
					}
				}
				
				$allergy_arr[] = [
					'id' => $val->family_member_id,
					'patient_name' => $val->name,
					'allergy' => $allergys,
				];
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		
		$response['message'] = 'Health Summary Allergies';
		$response['data'] = $allergy_arr;
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	public function createallergy(Request $request)
    {
		$response = array();
		// $user_id = $request->input('user_id');
		// $user_name = $request->input('user_name');
		// $allergy_name = $request->input('allergy_name');
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id      = isset($content->user_id) ? $content->user_id : '';
		$family_member_id      = isset($content->family_member_id) ? $content->family_member_id : '';
		$allergy_name      = isset($content->allergy_name) ? $content->allergy_name : '';
		$allergy_date      = isset($content->allergy_date) ? $content->allergy_date : '';
		
		$params = [
			'user_id'      => $user_id,
			'family_member_id'    => $family_member_id,
			'allergy_name' => $allergy_name,
			'allergy_date' => $allergy_date,
		]; 
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'family_member_id' => 'required',
            'allergy_name' => 'required',
            'allergy_date' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		
		$allergy = new Allergy();
		$allergy->user_id = $family_member_id;
		$allergy->allergy_name = $allergy_name;
		$allergy->allergy_date = $allergy_date;
		$allergy->created_at = date('Y-m-d H:i:s');
		$allergy->updated_at = date('Y-m-d H:i:s');
		
		if($allergy->save()){
			$response['status'] = 200;
			$response['message'] = 'Allergy successfully added!';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Error occured!';
		}
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
    }
}	
