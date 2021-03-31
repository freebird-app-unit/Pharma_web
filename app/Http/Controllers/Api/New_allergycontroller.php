<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Allergy;
use Validator;

class New_allergycontroller extends Controller
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
}
