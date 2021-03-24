<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\new_users;
use App\Pillreminder;
use App\Timeschedule;
use App\Filled;
use App\Day;
use App\Dose;
use App\MedicineName;
use App\doseType;
use App\toBeToken;
use App\Drinkwith;
use App\Misseddose;
use App\PillShape;
use App\PillColor;
use DB;
use Validator;
use File;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class PillreminderController extends Controller
{
	public function pillreminder(Request $request)
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
		
		$params = [
			'user_id' => $user_id,
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }

		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$reminders = Pillreminder::where('user_id',$user_id)->get();
		
		$reminders_arr = array();
		if(count($reminders)>0){
			foreach($reminders as $key=>$val){
				
				$reminders_arr[$key]['pill_reminder_id'] = $val->id;
				$reminders_arr[$key]['user_id'] = $val->user_id;
				$reminders_arr[$key]['reminder_name'] = $val->reminder_name;
				$reminders_arr[$key]['day_schedule'] = $val->day_schedule;
				$reminders_arr[$key]['date_schedule'] = $val->date_schedule;
				$reminders_arr[$key]['time_schedule'] = $val->time_schedule;
				$reminders_arr[$key]['days'] = explode(',',$val->days);
				$medicines = array();
				if($val->medicine_name!='' && $val->dose!='' && $val->doseType!='' && $val->toBeTaken!=''){

				$dose_data = Pillreminder::leftjoin('dose','pill_reminder.id','=','dose.pill_reminder_id')
				->where('pill_reminder.id',$val->id)
        		->get();

        		$medicines_data = Pillreminder::leftjoin('medicine_name','pill_reminder.id','=','medicine_name.pill_reminder_id')
				->where('pill_reminder.id',$val->id)
        		->get();

        		$dosetype_data = Pillreminder::leftjoin('dosetype','pill_reminder.id','=','dosetype.pill_reminder_id')
				->where('pill_reminder.id',$val->id)
        		->get();

        		$tobetaken_data = Pillreminder::leftjoin('tobetoken','pill_reminder.id','=','tobetoken.pill_reminder_id')
				->where('pill_reminder.id',$val->id)
        		->get();
        		
					$ii=0;
					foreach ($medicines_data as $m) {
						$medicines[$ii]['medicine_name'] = $m->medicine_name;
						$ii++;
					}
					$ii=0;
					foreach ($dose_data as $d) {
						$medicines[$ii]['dose'] = $d->dose;
						$ii++;
					}
					$ii=0;
					foreach ($dosetype_data as $dt) {
						$medicines[$ii]['doseType'] = $dt->doseType;
						$ii++;
					}
					$ii=0;
					foreach ($tobetaken_data as $tbt) {
						$medicines[$ii]['toBeTaken'] = $tbt->toBeToken;
						$ii++;
					}
				}
				$reminders_arr[$key]['medicines'] = $medicines;
			}
			$response['status'] = 200;
			$response['data']= $reminders_arr;
		}else{
			$response['data']= [];
		} 
		$response['message'] = 'Reminder list';
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        return response($cipher, 200);
    }
	public function pillreminderdetail(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$pill_reminder_id = isset($content->pill_reminder_id) ? $content->pill_reminder_id : '';
		
		$params = [
			'user_id' => $user_id,
			'pill_reminder_id' => $pill_reminder_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'pill_reminder_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$reminders = Pillreminder::where('user_id',$user_id);
		$reminders = $reminders->where('id',$pill_reminder_id);
		$reminders = $reminders->get();
		$reminders_arr = array();
		$medicines = array();
		if(count($reminders)>0){
			foreach($reminders as $key=>$val){
				$reminders_arr[$key]['pill_reminder_id'] = $val->id;
				$reminders_arr[$key]['user_id'] = $val->user_id;
				$reminders_arr[$key]['reminder_name'] = $val->reminder_name;
				$reminders_arr[$key]['day_schedule'] = $val->day_schedule;
				$reminders_arr[$key]['date_schedule'] = $val->date_schedule;
				$reminders_arr[$key]['time_schedule'] = $val->time_schedule;
				$reminders_arr[$key]['days'] = explode(',',$val->days);
				$medicines = array();
				if($val->medicine_name!='' && $val->dose!='' && $val->doseType!='' && $val->toBeTaken!=''){

				$dose_data = Pillreminder::leftjoin('dose','pill_reminder.id','=','dose.pill_reminder_id')
				->where('pill_reminder.id',$pill_reminder_id)
        		->get();

        		$medicines_data = Pillreminder::leftjoin('medicine_name','pill_reminder.id','=','medicine_name.pill_reminder_id')
				->where('pill_reminder.id',$pill_reminder_id)
        		->get();

        		$dosetype_data = Pillreminder::leftjoin('dosetype','pill_reminder.id','=','dosetype.pill_reminder_id')
				->where('pill_reminder.id',$pill_reminder_id)
        		->get();

        		$tobetaken_data = Pillreminder::leftjoin('tobetoken','pill_reminder.id','=','tobetoken.pill_reminder_id')
				->where('pill_reminder.id',$pill_reminder_id)
        		->get();
        		
					$ii=0;
					foreach ($medicines_data as $m) {
						$medicines[$ii]['medicine_name'] = $m->medicine_name;
						$ii++;
					}
					$ii=0;
					foreach ($dose_data as $d) {
						$medicines[$ii]['dose'] = $d->dose;
						$ii++;
					}
					$ii=0;
					foreach ($dosetype_data as $dt) {
						$medicines[$ii]['doseType'] = $dt->doseType;
						$ii++;
					}
					$ii=0;
					foreach ($tobetaken_data as $tbt) {
						$medicines[$ii]['toBeTaken'] = $tbt->toBeToken;
						$ii++;
					}
				}
				$reminders_arr[$key]['medicines'] = $medicines;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Pill reminder detail';
		$response['data'] = isset($reminders_arr[0])?$reminders_arr[0]:(object)[];
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        return response($cipher, 200);
	}
	public function removedose(Request $request)
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
		$pill_reminder_id = isset($content->pill_reminder_id) ? $content->pill_reminder_id : '';
		
		$params = [
			'user_id' => $user_id,
			'pill_reminder_id' => $pill_reminder_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'pill_reminder_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
        $token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
			$reminder = Pillreminder::where(['id'=>$pill_reminder_id,'user_id'=>$user_id]);
			$reminder->delete();
			$day = Day::where(['pill_reminder_id'=>$pill_reminder_id,'user_id'=>$user_id]);
			$day->delete();
			$dose = Dose::where(['pill_reminder_id'=>$pill_reminder_id,'user_id'=>$user_id]);
			$dose->delete();
			$medicine_name = MedicineName::where(['pill_reminder_id'=>$pill_reminder_id,'user_id'=>$user_id]);
			$medicine_name->delete();
			$doseType = doseType::where(['pill_reminder_id'=>$pill_reminder_id,'user_id'=>$user_id]);
			$doseType->delete();
			$toBeToken = toBeToken::where(['pill_reminder_id'=>$pill_reminder_id,'user_id'=>$user_id]);
			$toBeToken->delete();
		$response['status'] = 200;
		$response['message'] = 'Dose successfully removed';
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		return response($cipher, 200);
	}
	public function misseddoselist(Request $request)
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
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'user_id' => $user_id,
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$reminders = Pillreminder::where('customer_id',$user_id)->get();
		$missdose = Misseddose::where('customer_id',$user_id)->where('taken','0')->get();

		if(count($missdose)>0){
			if($missdose[0]->date < date('Y-m-d H:i:s')){
			$reminders_arr = array();
			$image_url = '';
				if($reminders[0]->medicine_image!=''){
					$destinationPath = base_path() . '/uploads/reminder/'.$reminders[0]->medicine_image;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/uploads/reminder/'.$reminders[0]->medicine_image;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				$reminders_arr['pillreminder_id'] = $reminders[0]->id;
				$reminders_arr['medicine_image'] = $image_url;
				$reminders_arr['medicine_name'] = $reminders[0]->medicine_name;
				$reminders_arr['schedule'] = $reminders[0]->schedule;
				$reminders_arr['time_schedule'] = $reminders[0]->time_schedule;
				$reminders_arr['reminder_date'] = $reminders[0]->reminder_date;
			
			$response['status'] = 200;
			$response['data'] = $reminders_arr;
		}else{
			$response['status'] = 404;
		}
				$response['message'] = 'Reminder list';
				return response($response, 200);
	}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response['status'] = 404;
		$response['message'] = 'Reminder list';
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		return response($cipher, 200);
	}
	public function takennow(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		// $pillreminder_id = $request->pillreminder_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');	
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		// $user_id = $content->user_id;
		// $pillreminder_id = $content->pillreminder_id;
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$pillreminder_id = isset($content->pillreminder_id) ? $content->pillreminder_id : '';
		
		$params = [
			'user_id' => $user_id,
			'pillreminder_id' => $pillreminder_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'pillreminder_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$missdose = Misseddose::where('customer_id',$user_id);
		$missdose = $missdose->where('pillreminder_id',$pillreminder_id);
		$missdose = $missdose->get();
		if(count($missdose)>0){
			$reminder = Misseddose::find($missdose[0]->id);
			$reminder->taken = '1';
			$reminder->save();
		}
		$response['status'] = 200;
		$response['message'] = 'Dose successfully taken';
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        return response($cipher, 200);
	}
	
	public function create_pillreminder(Request $request)
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
		
		$user_id       = isset($content->user_id) ? $content->user_id : '';
		$reminder_name = isset($content->reminder_name) ? $content->reminder_name : '';
		$day_schedule  = isset($content->day_schedule) ? $content->day_schedule : '';
		$date_schedule = isset($content->date_schedule) ? $content->date_schedule : '';
		$time_schedule = isset($content->time_schedule) ? $content->time_schedule : '';
		$days          = isset($content->days) ? implode(',',$content->days) : '';
		$medicine_name = isset($content->medicine_name) ? implode(',',$content->medicine_name) : '';
		$dose          = isset($content->dose) ? implode(',',$content->dose) : '';
		$doseType      = isset($content->doseType) ? implode(',',$content->doseType) : '';
		$toBeTaken     = isset($content->toBeTaken) ? implode(',',$content->toBeTaken) : '';
		
		$params = [
			'user_id' 		=> $user_id,
			'reminder_name' => $reminder_name,
			'day_schedule'  => $day_schedule,
			'date_schedule' => $date_schedule,
			'time_schedule' => $time_schedule,
			'days' 			=> $days,
			'medicine_name' => $medicine_name,
			'dose'			=> $dose,
			'doseType'		=> $doseType,
			'toBeTaken' 	=> $toBeTaken,
		];
		 
		$validator = Validator::make($params, [
            'user_id'       => 'required',
            'reminder_name' => 'required',
            'day_schedule'  => 'required',
            'medicine_name' => 'required',
            'dose'          => 'required',
            'doseType'      => 'required',
            'toBeTaken'     => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        } 
        $token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
        		$pillreminder = new Pillreminder();
				$pillreminder->user_id = $user_id;
				$pillreminder->reminder_name = $reminder_name;
				$pillreminder->day_schedule = $day_schedule;
				$pillreminder->date_schedule = $date_schedule;
				$pillreminder->time_schedule = $time_schedule;
				$pillreminder->days = $days;
				$pillreminder->medicine_name = $medicine_name;
				$pillreminder->dose = $dose;
				$pillreminder->doseType = $doseType;
				$pillreminder->toBeTaken = $toBeTaken;
				$pillreminder->created_at = date('Y-m-d H:i:s');
				$pillreminder->updated_at = date('Y-m-d H:i:s');
				$pillreminder->save();
				if($pillreminder->day_schedule == 'weekly'){
					$day_data = explode(',',$pillreminder->days);
					foreach ($day_data as $d_data) {
						$day = new Day();
						if($d_data=='mon'){
							$day->day = "mon";
						}elseif($d_data=='tue'){
							$day->day = "tue";
						}elseif ($d_data=='wed'){
							$day->day = "wed";
						}elseif ($d_data=='thu'){
							$day->day = "thu";
						}elseif ($d_data=='fri') {
							$day->day = "fri";
						}elseif ($d_data=='sat') {
							$day->day = "sat";
						}elseif ($d_data=='sun') {
							$day->day = "sun";
						}
						$day->user_id =$user_id;
						$day->pill_reminder_id =$pillreminder->id;
					    $day->created_at =date('Y-m-d H:i:s');
						$day->updated_at = date('Y-m-d H:i:s');
					    $day->save();	
					}
				}
				$dose_data = explode(',',$pillreminder->dose);
				foreach ($dose_data as $do_data) {
					$dose = new Dose();
					if($do_data=='0.5'){
						$dose->dose = "0.5";
					}elseif($do_data=='1'){
						$dose->dose = "1";
					}elseif ($do_data=='2') {
						$dose->dose = "2";
					}
					$dose->user_id =$user_id;
					$dose->pill_reminder_id =$pillreminder->id;
				    $dose->created_at =date('Y-m-d H:i:s');
					$dose->updated_at = date('Y-m-d H:i:s');
				    $dose->save();	
				}
				$medicine_name_data = explode(',',$pillreminder->medicine_name);
				foreach ($medicine_name_data as $medicine_data) {
					$medicine = new MedicineName();
					$medicine->medicine_name = $medicine_data;
					$medicine->user_id =$user_id;
					$medicine->pill_reminder_id =$pillreminder->id;
				    $medicine->created_at =date('Y-m-d H:i:s');
					$medicine->updated_at = date('Y-m-d H:i:s');
				    $medicine->save();	
				}
				$doseType_data = explode(',',$pillreminder->doseType);
				foreach ($doseType_data as $dt_data) {
					$doseType = new doseType();
					$doseType->doseType = $dt_data;
					$doseType->user_id =$user_id;
					$doseType->pill_reminder_id =$pillreminder->id;
				    $doseType->created_at =date('Y-m-d H:i:s');
					$doseType->updated_at = date('Y-m-d H:i:s');
				    $doseType->save();	
				}
				$toBeTaken_data = explode(',',$pillreminder->toBeTaken);
				foreach ($toBeTaken_data as $tbt_data) {
					$toBeTaken = new toBeToken();
					$toBeTaken->toBeToken = $tbt_data;
					$toBeTaken->user_id =$user_id;
					$toBeTaken->pill_reminder_id =$pillreminder->id;
				    $toBeTaken->created_at =date('Y-m-d H:i:s');
					$toBeTaken->updated_at = date('Y-m-d H:i:s');
				    $toBeTaken->save();	
				}
				$response['status'] = 200;
				$response['message'] = 'Pill Reminder successfully saved';
				
				$pill = [];

				$pill[] = [	
						'pill_reminder_id' =>$pillreminder->id,
						'user_id'		   =>$pillreminder->user_id,
						'reminder_name'	   =>$pillreminder->reminder_name,
						'day_schedule'	   =>$pillreminder->day_schedule,
						'date_schedule'	   =>$pillreminder->date_schedule,
						'time_schedule'	   =>$pillreminder->time_schedule,
						'days'			   =>explode(',',$pillreminder->days),
						'medicine_name'	   =>explode(',',$pillreminder->medicine_name),
						'dose'             =>explode(',',$pillreminder->dose),
						'doseType'         =>explode(',',$pillreminder->doseType),
						'toBeTaken'        =>explode(',',$pillreminder->toBeTaken),
				];
				$response['data']=$pill;
				}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   			}
		        $response = json_encode($response);
				$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
        		return response($cipher, 200); 
	}
	
	public function edit_pillreminder(Request $request)
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
		
		$pill_reminder_id       = isset($content->pill_reminder_id) ? $content->pill_reminder_id : '';
		$user_id       = isset($content->user_id) ? $content->user_id : '';
		$reminder_name = isset($content->reminder_name) ? $content->reminder_name : '';
		$day_schedule  = isset($content->day_schedule) ? $content->day_schedule : '';
		$date_schedule = isset($content->date_schedule) ? $content->date_schedule : '';
		$time_schedule = isset($content->time_schedule) ? $content->time_schedule : '';
		$days          = isset($content->days) ? implode(',',$content->days) : '';
		$medicine_name = isset($content->medicine_name) ? implode(',',$content->medicine_name) : '';
		$dose          = isset($content->dose) ? implode(',',$content->dose) : '';
		$doseType      = isset($content->doseType) ? implode(',',$content->doseType) : '';
		$toBeTaken     = isset($content->toBeTaken) ? implode(',',$content->toBeTaken) : '';
		
		$params = [
			'pill_reminder_id'  => $pill_reminder_id,
			'user_id' 			=> $user_id,
			'reminder_name' 	=> $reminder_name,
			'day_schedule'  	=> $day_schedule,
			'date_schedule' 	=> $date_schedule,
			'time_schedule' 	=> $time_schedule,
			'days' 				=> $days,
			'medicine_name' 	=> $medicine_name,
			'dose'				=> $dose,
			'doseType'			=> $doseType,
			'toBeTaken' 		=> $toBeTaken,
		];
		 
		$validator = Validator::make($params, [
			'pill_reminder_id'  => 'required',
            'user_id'       	=> 'required',
            'reminder_name' 	=> 'required',
            'day_schedule'  	=> 'required',
            'medicine_name' 	=> 'required',
            'dose'          	=> 'required',
            'doseType'      	=> 'required',
            'toBeTaken'     	=> 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        } 
        $token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		
				$pillreminder = Pillreminder::find($pill_reminder_id);
				$pillreminder->user_id = $user_id;
				$pillreminder->reminder_name = $reminder_name;
				$pillreminder->day_schedule = $day_schedule;
				$pillreminder->date_schedule = $date_schedule;
				$pillreminder->time_schedule = $time_schedule;
				$pillreminder->days = $days;
				$pillreminder->medicine_name = $medicine_name;
				$pillreminder->dose = $dose;
				$pillreminder->doseType = $doseType;
				$pillreminder->toBeTaken = $toBeTaken;
				$pillreminder->created_at = date('Y-m-d H:i:s');
				$pillreminder->updated_at = date('Y-m-d H:i:s');
				$pillreminder->save();
				$day = Day::where('pill_reminder_id', $pill_reminder_id);
				$day->delete();
				$dose = Dose::where('pill_reminder_id', $pill_reminder_id);
				$dose->delete();
				$medicine_name = MedicineName::where('pill_reminder_id', $pill_reminder_id);
				$medicine_name->delete();
				$doseType = doseType::where('pill_reminder_id', $pill_reminder_id);
				$doseType->delete();
				$toBeTaken = toBeToken::where('pill_reminder_id', $pill_reminder_id);
				$toBeTaken->delete();
				if($pillreminder->day_schedule == 'weekly'){
					$day_data = explode(',',$pillreminder->days);
					foreach ($day_data as $d_data) {
						$day = new Day();
						if($d_data=='mon'){
							$day->day = "mon";
						}elseif($d_data=='tue'){
							$day->day = "tue";
						}elseif ($d_data=='wed'){
							$day->day = "wed";
						}elseif ($d_data=='thu'){
							$day->day = "thu";
						}elseif ($d_data=='fri') {
							$day->day = "fri";
						}elseif ($d_data=='sat') {
							$day->day = "sat";
						}elseif ($d_data=='sun') {
							$day->day = "sun";
						}
						$day->user_id =$user_id;
						$day->pill_reminder_id =$pillreminder->id;
					    $day->created_at =date('Y-m-d H:i:s');
						$day->updated_at = date('Y-m-d H:i:s');
					    $day->save();	
					}
				}
				$dose_data = explode(',',$pillreminder->dose);
				foreach ($dose_data as $do_data) {
					$dose = new Dose();
					if($do_data=='0.5'){
						$dose->dose = "0.5";
					}elseif($do_data=='1'){
						$dose->dose = "1";
					}elseif ($do_data=='2') {
						$dose->dose = "2";
					}
					$dose->user_id =$user_id;
					$dose->pill_reminder_id =$pillreminder->id;
				    $dose->created_at =date('Y-m-d H:i:s');
					$dose->updated_at = date('Y-m-d H:i:s');
				    $dose->save();	
				}
				$doseType_data = explode(',',$pillreminder->doseType);
				foreach ($doseType_data as $dt_data) {
					$doseType = new doseType();
					$doseType->doseType = $dt_data;
					$doseType->user_id =$user_id;
					$doseType->pill_reminder_id =$pillreminder->id;
				    $doseType->created_at =date('Y-m-d H:i:s');
					$doseType->updated_at = date('Y-m-d H:i:s');
				    $doseType->save();	
				}
				$toBeTaken_data = explode(',',$pillreminder->toBeTaken);
				foreach ($toBeTaken_data as $tbt_data) {
					$toBeTaken = new toBeToken();
					$toBeTaken->toBeToken = $tbt_data;
					$toBeTaken->user_id =$user_id;
					$toBeTaken->pill_reminder_id =$pillreminder->id;
				    $toBeTaken->created_at =date('Y-m-d H:i:s');
					$toBeTaken->updated_at = date('Y-m-d H:i:s');
				    $toBeTaken->save();	
				}
				$medicine_name_data = explode(',',$pillreminder->medicine_name);
				foreach ($medicine_name_data as $medicine_data) {
					$medicine = new MedicineName();
					$medicine->medicine_name = $medicine_data;
					$medicine->user_id =$user_id;
					$medicine->pill_reminder_id =$pillreminder->id;
				    $medicine->created_at =date('Y-m-d H:i:s');
					$medicine->updated_at = date('Y-m-d H:i:s');
				    $medicine->save();	
				}

				$response['status'] = 200;
				$response['message'] = 'Pill Reminder successfully updated';
				$pill = [];

				$pill[] = [	
						'pill_reminder_id' =>$pillreminder->id,
						'user_id'		   =>(int)$pillreminder->user_id,
						'reminder_name'	   =>$pillreminder->reminder_name,
						'day_schedule'	   =>$pillreminder->day_schedule,
						'date_schedule'	   =>$pillreminder->date_schedule,
						'time_schedule'	   =>$pillreminder->time_schedule,
						'days'			   =>explode(',',$pillreminder->days),
						'medicine_name'	   =>explode(',',$pillreminder->medicine_name),
						'dose'             =>explode(',',$pillreminder->dose),
						'doseType'         =>explode(',',$pillreminder->doseType),
						'toBeTaken'        =>explode(',',$pillreminder->toBeTaken),
				];
				$response['data']=$pill;
				}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   			}
		        $response = json_encode($response);
				$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		        return response($cipher, 200);
	}
	
	public function get_pill_shape() {
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$shape_list = PillShape::get();
		
		$data = [];
		if (!empty($shape_list)) {
			foreach($shape_list as $val) {
				
				$file_name = '';
				if (!empty($val->image)) {
					if (file_exists(storage_path('app/public/uploads/piil_shape/'.$val->image))){
						$file_name = asset('storage/app/public/uploads/piil_shape/' . $val->image); 
					}
				}
				
				$data[] = [
					'id' => $val->id,
					'name' => $val->name,
					'image' => $file_name
				];
			}
			
		}
		
		$response['status'] = 200;
		$response['message'] = 'Shape list';
		$response['data'] = $data;
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}
	
	public function get_pill_color() {
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$shape_list = PillColor::get();
		
		$response['status'] = 200;
		$response['message'] = 'Color list';
		$response['data'] = $shape_list;
		
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}
}	
