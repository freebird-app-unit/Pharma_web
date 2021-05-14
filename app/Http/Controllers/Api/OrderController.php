<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Orders;
use App\Rejectreason;
use App\Cancelreason;
use App\Address;
use App\Incompletereason;
use App\Orderfeedback;
use App\Prescription;
use App\new_address;
use App\delivery_charges;
use App\new_delivery_charges;
use App\notification_seller;
use App\new_users;
use App\new_orders;
use App\transaction;
use Validator;
use Storage;
use Image;
use File;
use DB;
use App\Events\CreateNewOrder;
use App\new_logistics;
use App\new_pharmacies;
use App\new_pharma_logistic_employee;
use App\DeliveryboyModel\new_order_history;
use App\SellerModel\Orderassign;
use Illuminate\Validation\Rule;
use App\new_countries;
use App\new_states;
use App\new_cities;
//use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\notification_user;
use Mail;
use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Str;
use Helper;
use App\multiple_prescription;
use App\prescription_multiple_image;
class OrderController extends Controller
{
	public function cancelorderlist(Request $request)
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
		$orders = new_orders::with('prescriptions')->where('customer_id',$user_id)->where('order_status','cancel')->get();
		$orders_arr = array();
		if(count($orders)>0){
			foreach($orders as $key=>$val){
				
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($val->prescriptions->image)) {

					$filename = storage_path('app/public/uploads/prescription/' . $val->prescriptions->image);
				
					if (File::exists($filename)) {
						$image_url = asset('storage/app/public/uploads/prescription/' . $val->prescriptions->image);
					}
				}
				
				if($val->cancelreason_id !== '' && $val->cancelreason_id>0){
					$cancelreason = Cancelreason::find($val->cancelreason_id);
					$cancel_reason = $cancelreason->reason;
				}else{
					$cancel_reason = '';
				}
				
				$pharmacy = new_pharmacies::where('id',$val->pharmacy_id)->first();
				$orders_arr[$key]['id'] = $val->id;
				$orders_arr[$key]['pharmacy'] = $pharmacy->name;
				$orders_arr[$key]['order_number'] = $val->order_number;
				$orders_arr[$key]['prescription'] = $image_url;
				$orders_arr[$key]['order_type'] = str_replace('_',' ',$val->order_type);
				$orders_arr[$key]['total_days'] = $val->total_days;
				$orders_arr[$key]['order_note'] = $val->order_note;
				$orders_arr[$key]['reminder_days'] = $val->reminder_days;
				$orders_arr[$key]['reason'] = $cancel_reason;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'Cancel order list';
		$response['data'] = $orders_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}

	public function generate_unique_number($orderid)
	{
		$string = 'PHAR'.$orderid; 
		$order = new_orders::where('id',$orderid)->first();
		$order->order_number=$string;
		$order->save();
		$this->passdata_neworder($orderid);
	}
	public function passdata_neworder($orderid){
                if(!empty($orderid)){
                                            $pass_data = array('orderid' => $orderid);
                                            $get_data = http_build_query($pass_data);
                                             $curl_url = 'http://159.65.145.98/pharma/api/event_neworder?'.$get_data; 
                                            $curl = curl_init($curl_url);
                                            curl_setopt_array($curl, array(
                                                CURLOPT_URL => $curl_url,
                                                CURLOPT_RETURNTRANSFER => true,
                                                CURLOPT_ENCODING => "",
                                                CURLOPT_TIMEOUT => 30000,
                                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                                CURLOPT_CUSTOMREQUEST => 'GET',//POST
                                                //CURLOPT_POSTFIELDS => json_encode($data2),
                                                CURLOPT_HTTPHEADER => array(
                                                    // Set Here Your Requesred Headers
                                                    'Content-Type: application/json',
                                                ),
                                            ));
                                            $response = curl_exec($curl);
                                            $err = curl_error($curl);
                                            curl_close($curl);
                                            return json_decode($response,true);
                                        }
    }

	public function reorder(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		// $order_id = $request->order_id;
		// $address_id = $request->address_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$order_id = isset($content->order_id) ? $content->order_id : '';
		$address_id = isset($content->address_id) ? $content->address_id : '';
		
		$params = [
			'user_id' => $user_id,
			'order_id' => $order_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$orders = new_orders::where('id',$order_id)->where('customer_id',$user_id)->first();
		$customer_name = new_users::where('id',$user_id)->get();


		$orders_arr = array();
		if($orders){
			/*$neworder = new new_orders();
			$neworder->process_user_id = 0;
			$neworder->pharmacy_id = $orders->pharmacy_id;
			$neworder->deliveryboy_id = 0;
			$neworder->customer_id = $user_id;
			$neworder->address_id = $address_id;
			$order_number = $this->generate_unique_number();
			$neworder->order_number = $order_number;
			$neworder->prescription_id = $orders->prescription_id;
			$neworder->order_type = $orders->order_type;
			$neworder->total_days = $orders->total_days;
			$neworder->order_note = $orders->order_note;
			$neworder->reminder_days = $orders->reminder_days;
			$neworder->order_status = 'new';
			$neworder->rejectreason_id = 0;
			$neworder->incompletereason_id = 0;
			$neworder->cancelreason_id = 0;
			$neworder->is_paid = $orders->is_paid;
			$neworder->leaved_with_neighbor = isset($orders->leaved_with_neighbor)?($orders->leaved_with_neighbor):'false';
			$neworder->delivery_charges_id = isset($orders->delivery_charges_id)?($orders->delivery_charges_id):1;

			$neworder->created_at = date('Y-m-d H:i:s');
			$neworder->updated_at = date('Y-m-d H:i:s');
			$neworder->save();
			$response['status'] = 200;
			$response['message'] = 'Your order successfully submited';
			
			$prescriptions = Prescription::where('id','=',$orders->prescription_id);
			$image_url = url('/').'/uploads/placeholder.png';
			if (($prescriptions->count()) !== 0) {
                $prescriptions = $prescriptions->get();
				if (file_exists(storage_path('app/public/uploads/prescription/'.$prescriptions[0]->image))){
					$image_url = asset('storage/app/public/uploads/prescription/' . $prescriptions[0]->image);
				}
			}

			$newOrderEmit = new Orders();
			$newOrderEmit->pharmacy_id = $orders->pharmacy_id;
			$newOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order_id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order_number.'</span>';
			$newOrderEmit->id = $order_id;
			$newOrderEmit->is_paid = $orders->is_paid;
			$newOrderEmit->total_days = $neworder->total_days;
			$newOrderEmit->checking_by	= '';
			$newOrderEmit->prescription_img	= $image_url;
			$newOrderEmit->order_number = $order_number;
			$newOrderEmit->number = '<a href="'.url('/orders/prescription/'.$neworder->id).'"><span>'.$neworder->order_number.'</span></a>';
			$newOrderEmit->order_note = $neworder->order_note;
			$newOrderEmit->created_at = $neworder->created_at;
			$newOrderEmit->updated_at = $neworder->updated_at;
			$newOrderEmit->order_type = $neworder->order_type;
			$newOrderEmit->customer_name = $customer_name[0]->name;
			$newOrderEmit->address = $customer_name[0]->address;
			$newOrderEmit->customer_number = $customer_name[0]->mobile_number;
			$newOrderEmit->delivery_type = delivery_charges::where('id', $neworder->delivery_charges_id)->value('delivery_type');

			$newOrderEmit->active = '<a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$neworder->id.'?home').'" title="Accept order">Accept</a>';
			
			$newOrderEmit->reject = '<a onclick="reject_order('.$neworder->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';
			event(new CreateNewOrder($newOrderEmit));*/
			$neworder = new new_orders();
			$neworder->process_user_type ='';
			$neworder->process_user_id = 0;
			$neworder->pharmacy_id = $pharmacy_id;
			$neworder->deliveryboy_id = 0;
			$neworder->customer_id = $user_id;
			$neworder->address_id = $address_id;

			$order_number = $this->generate_unique_number();
			$neworder->order_number = $order_number;
			$neworder->logistic_user_id = $logistic_id;
			/*$neworder->is_paid = (isset($content->logistic_id) && (Int)($content->logistic_id) > 0) ? 1 : 0;*/
			$neworder->is_external_delivery = ((Int)$logistic_id>0)?1:0;
			$neworder->prescription_id = $prescription;
			$neworder->order_type = $order_type;
			$neworder->total_days = $total_days;
			$neworder->reminder_days = $reminder_days;
			$neworder->order_note = $order_note;
			$neworder->order_status = 'new';
			/*$neworder->rejectreason_id = 0;
			$neworder->incompletereason_id = 0;
			$neworder->cancelreason_id = 0;*/
			$neworder->audio = $audio_name;
			$neworder->leave_neighbour = $leaved_with_neighbor;
			$neworder->delivery_charges_id = ($delivery_charges_id !== '')?($delivery_charges_id):1;
			/*$neworder->receive_date = date('Y-m-d H:i:s');*/
			$neworder->audio_info= date('Y-m-d H:i:s');
			$neworder->created_at = date('Y-m-d H:i:s');
			$neworder->updated_at = date('Y-m-d H:i:s');
			$neworder->save();
			$customer_name = new_users::where('id',$neworder->customer_id)->first();
			$address = new_address::where('id',$neworder->address_id)->first();
			$newOrderEmit = new new_orders();
			$newOrderEmit->pharmacy_id = $neworder->pharmacy_id;
			$image_url = url('/').'/uploads/placeholder.png';

			if (!empty($prescriptions->image)) {
				if (file_exists(storage_path('app/public/uploads/prescription/'.$prescriptions->image))){
					$image_url = asset('storage/app/public/uploads/prescription/' . $prescriptions->image);
				}
			}
			$newOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$neworder->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$neworder->order_number.'</span>';
			$newOrderEmit->id = $neworder->id;
			$newOrderEmit->total_days = $neworder->total_days;
			//$newOrderEmit->checking_by	= '';
			$newOrderEmit->prescription_img	= $image_url;
			$newOrderEmit->number = '<a href="'.url('/orders/prescription/'.$neworder->id).'"><span>'.$neworder->order_number.'</span></a>';
			$newOrderEmit->order_number = $neworder->order_number;
			$newOrderEmit->order_note = $neworder->order_note;
			$newOrderEmit->created_at = $neworder->created_at;
			$newOrderEmit->updated_at = $neworder->updated_at;
			$newOrderEmit->order_type = $neworder->order_type;
			$newOrderEmit->is_external_delivery = $neworder->is_external_delivery;
			$newOrderEmit->delivery_type = new_delivery_charges::where('id', $neworder->delivery_charges_id)->value('delivery_type');


			$newOrderEmit->prescription_name = $prescriptions->name;
			$newOrderEmit->customer_name = $customer_name->name;
			$newOrderEmit->address = $address->address;
			$newOrderEmit->customer_number = $customer_name->mobile_number;
			$newOrderEmit->active = '<a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$neworder->id.'?home').'" title="Accept order">Accept</a>';

			$newOrderEmit->reject = '<a onclick="reject_order('.$neworder->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';
			
			event(new CreateNewOrder($newOrderEmit));

		}else{
			$response['status'] = 404;
			$response['message'] = 'Order not found';
		}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	
	public function createorder(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = array();
		$response['data']['payment_url'] = '';
		$response['data']['payment_order_id'] = '';
		// $user_id = $request->user_id;
		// $pharmacy_id = $request->pharmacy_id;
		// $address_id = $request->address_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id       = isset($content->user_id) ? $content->user_id : '';
		$pharmacy_id   = isset($content->pharmacy_id) ? $content->pharmacy_id : '';
		$address_id    = isset($content->address_id) ? $content->address_id : '';
		$order_type    = isset($content->order_type) ? $content->order_type : '' ;
		$total_days    = isset($content->total_days) ? $content->total_days : '';
		$prescription_id  = isset($content->prescription_id) ? $content->prescription_id : '';
		$prescription_name  = isset($content->prescription_name) ? $content->prescription_name : '';
		$reminder_days = isset($content->reminder_days) ? $content->reminder_days : '';
		$order_note = isset($content->order_note) ? $content->order_note : '';
		$audio = isset($content->audio) ? $content->audio : '';
		$leaved_with_neighbor = isset($content->leaved_with_neighbor)?($content->leaved_with_neighbor):'false';
		$delivery_charges_id = isset($content->delivery_charges_id) ? $content->delivery_charges_id : '';
		$logistic_id = isset($content->logistic_id) ? $content->logistic_id : 0;
		$is_external_delivery = isset($content->is_external_delivery) ? $content->is_external_delivery : '';
		$is_intersection = isset($content->is_intersection) ? $content->is_intersection : '';
		$params = [
			'user_id' => $user_id,
			'pharmacy_id' => $pharmacy_id,
			//'address_id' => $address_id,
			'order_type' => $order_type,
			'total_days' => $total_days,
			//'prescription_id' => $prescription_id,
			'reminder_days' => $reminder_days
		];
		 
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'pharmacy_id' => 'required',
            'order_type' => 'required',
            //'total_days' => 'required',
            //'prescription_id' => 'required',
            //'reminder_days' => 'required'
        ]);
		
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->first();
		if(!empty($user)){
			$userdata = new_users::find($user_id);
			
			if($userdata){
				
				$pre = Prescription::where('id','=',$prescription_id);
				$prescriptions = new Prescription();
				
				if (($pre->count()) == 0) {
					
					$find_name = Prescription::where(['user_id'=>$user_id,'name'=>$prescription_name,"is_delete"=>"0"])->get();
					if(count($find_name)>0){
						$response['status'] = 404;
						$response['message'] = 'Prescription name already exists';
						$response = json_encode($response);
						$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
						
						return response($cipher, 200);
					}else{
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
							
							$response = json_encode($response);
							$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
							
							return response($cipher, 200);
						}

						$prescriptions->user_id = $user_id;
						$prescriptions->name = $prescription_name;
						$prescriptions->image = $prescription_image;
						$prescriptions->save();
						$prescription = $prescriptions->id;

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
						$abc->image = base64_encode(file_get_contents($request->file('prescription')));
						$abc->path = asset('storage/app/public/uploads/prescription/' . $prescription_image);
						$abc->prescription_date = $prescriptions->prescription_date;
						$abc->is_delete = "0";
						$abc->created_at = date('Y-m-d H:i:s');
						$abc->updated_at = date('Y-m-d H:i:s');				
						$abc->save();

						//restore image
						$image = $abc->image;  // your base64 encoded
					    $image = str_replace('data:image/png;base64,', '', $image);
					    $image = str_replace(' ', '+', $image);
					    $imageName = str::random(10) . '.png';
						Storage::disk('public')->put('uploads/prescription_restore/'.$imageName, base64_decode($image), 'public');
					}
				} else {
					$pre = $pre->get();
					$prescription = $prescription_id;
					$prescriptions->image = $pre[0]->image;
				} 
				
				$audio_name = '';
				$music_file = $request->file('audio'); 
				if(isset($music_file)) { 
					$filename= time().'-'.$music_file->getClientOriginalName();
					Storage::disk('public')->put('uploads/audio/'.$filename, file_get_contents($music_file));
					$audio_name = $filename; 
				}
				$neworder = new new_orders();
				$neworder->process_user_type ='';
				$neworder->process_user_id = 0;
				$neworder->pharmacy_id = $pharmacy_id;
				$neworder->deliveryboy_id = 0;
				$neworder->customer_id = $user_id;
				$neworder->address_id = $address_id;
				$neworder->logistic_user_id = $logistic_id;
				$neworder->order_status = 'new';

				/*$neworder->is_paid = (isset($content->logistic_id) && (Int)($content->logistic_id) > 0) ? 1 : 0;*/
				$neworder->is_external_delivery = $is_external_delivery;
				$neworder->is_intersection = $is_intersection;
				if($neworder->is_external_delivery){
					$neworder->external_delivery_initiatedby = 'customer';
					$neworder->order_status = 'payment_pending';
				}
				$neworder->prescription_id = $prescription;
				$neworder->order_type = $order_type;
				$neworder->total_days = $total_days;
				$neworder->reminder_days = $reminder_days;
				$neworder->order_note = $order_note;
				/*$neworder->rejectreason_id = 0;
				$neworder->incompletereason_id = 0;
				$neworder->cancelreason_id = 0;*/
				$neworder->audio = $audio_name;
				$neworder->leave_neighbour = $leaved_with_neighbor;
				$neworder->delivery_charges_id = ($delivery_charges_id)?$delivery_charges_id:'1';
				/*$neworder->receive_date = date('Y-m-d H:i:s');*/
				$neworder->audio_info= date('Y-m-d H:i:s');
				$neworder->create_datetime = date('Y-m-d H:i:s');
				$neworder->created_at = date('Y-m-d H:i:s');
				$neworder->updated_at = date('Y-m-d H:i:s');
				if($neworder->save()){
					$order_data = new_orders::where('id',$neworder->id)->first();
					$pharmacy_name = new_pharmacies::where('id',$neworder->pharmacy_id)->first();

					if($neworder->is_external_delivery == 0){
						if($user_id > 0){
							$ids = array();
							$t_data = new_users::where('id',$neworder->customer_id)->first();
							$sellerdetails = new_pharma_logistic_employee::where(['pharma_logistic_id'=>$order_data->pharmacy_id,'user_type'=>'seller'])->get();
							
							$message = ' Order Create '. $order_data->order_number.' User Name : '. $t_data->name;
							$seller_id = [];
							foreach ($sellerdetails as $sellerdetail) {
								if($sellerdetail->fcm_token!=''){
									$ids[] = $sellerdetail->fcm_token;
									$seller_id[] = $sellerdetail->id;
								}
								$msg = array
								(
									'body'   => ' Order Create '. $order_data->order_number.' User Name : '. $t_data->name ,
									'title'     => 'Order Created'
								);
								
								$notification = new notification_seller();
								$notification->user_id=$sellerdetail->id;
								$notification->order_id=$order_data->id;
								$notification->order_status=$order_data->order_status;
								$notification->subtitle=$msg['body'];
								$notification->title=$msg['title'];
								$notification->created_at=date('Y-m-d H:i:s');
								$notification->save();
							}
							
							/*if (count($ids) > 0) {					
								Helper::sendNotification($ids, $message, 'Order Created', $user->id, 'user', $seller_id, 'seller', $ids);

							}*/
						}
					}

					if(!empty($order_data)){
						$data = [
							'name' => $pharmacy_name->name,
							'orderno'=>$order_data->order_number
						];
						$email = $pharmacy_name->email;
						Mail::send('email.createorder', $data, function ($message) use ($email) {
								$message->to($email)->subject('Pharma - Order Create');
						});
					}

					$update_payment_id = new_orders::where('id',$neworder->id)->first();
					$update_payment_id->payment_order_id = time().$neworder->id;
					$update_payment_id->save();
					$pharmacy_name = new_pharmacies::where('id',$neworder->pharmacy_id)->first();
					$response['data']['payment_order_id'] = $update_payment_id->payment_order_id;
					$response['data']['order_id'] = $update_payment_id->id;
					$response['data']['order_message'] ='Your order '.$update_payment_id->order_number.' has been placed successfully.\n'.$pharmacy_name->name.' will accept your order soon.';
					
					
					$response['status'] = 200;
					$response['message'] = 'Your order has been successfully placed';

				} else {
					$response['status'] = 404;
					$response['message'] = 'Something went wrong';
				}			
			}else{
				$response['status'] = 404;
				$response['message'] = 'User not found';
			}

			if($neworder->is_external_delivery){
				$response['data']['payment_url'] = 'create_transaction/'.$neworder->id;
			}

		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}
	  
	public function cancelorder(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		// $order_id = $request->order_id;
		// $cancelreason_id = $request->cancelreason_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$order_id = isset($content->order_id) ? $content->order_id : '';
		$cancelreason_id = isset($content->cancelreason_id) ? $content->cancelreason_id : '';
		
		$params = [
			'user_id' => $user_id,
			'order_id' => $order_id,
			'cancelreason_id' => $user_id,
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'cancelreason_id' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(!empty($user)){
		$orders = new_orders::where('id',$order_id)->where('customer_id',$user_id)->first();
		$orders_arr = array();

		if($orders){
			$order_data = new_orders::where('id',$order_id)->first();
			$order_assign = Orderassign::where('order_id',$order_id)->first();
			if(!empty($order_assign)){
				if($order_assign->logistic_id == NULL){
						$email_data = new_pharmacies::where('id',$order_data->pharmacy_id)->first();
			            $data = [
			                'name' => $email_data->name,
			                'orderno'=>$order_data->order_number
			            ];
			            $email = $email_data->email;
			            $result = Mail::send('email.cancel', $data, function ($message) use ($email) {
			                $message->to($email)->subject('Pharma - Order Cancel');
			            });
				}else{
					$logistic_data = new_logistics::where('id',$order_data->logistic_user_id)->first();
                            $data = [
                                'name' => $logistic_data->name,
                                'orderno'=>$order_data->order_number
                            ];
                            $email = $logistic_data->email;
                            $result = Mail::send('email.cancel', $data, function ($message) use ($email) {
                                    $message->to($email)->subject('Pharma - Order Cancel');
                            });
				}
			}	 
			
         

			if($user_id > 0){
				$ids = array();
				$order_data = new_orders::where('id',$order_id)->first();
				$t_data = new_users::where('id',$order_data->customer_id)->first();
				$sellerdetails = new_pharma_logistic_employee::where(['pharma_logistic_id'=>$order_data->pharmacy_id,'user_type'=>'seller'])->get();
				
				$message = ' Order Cancelled '. $order_data->order_number;
				$seller_id = [];
				foreach ($sellerdetails as $sellerdetail) {
					if($sellerdetail->fcm_token!=''){
						$ids[] = $sellerdetail->fcm_token;
						$seller_id[] = $sellerdetail->id;
					}
					$msg = array
					(
						'body'   => ' Order Cancelled '. $order_data->order_number,
						'title'     => 'Order Cancelled From User'
					);
					if(count($ids)>0){
						$fields = array(
							'to' => $sellerdetail->fcm_token,
							'notification' => $msg
						);
						// $this->sendPushNotificationSeller($fields);
					}
					$notification = new notification_seller();
					$notification->user_id=$sellerdetail->id;
					$notification->order_id=$order_data->id;
					$notification->order_status=$order_data->order_status;
					$notification->subtitle=$msg['body'];
					$notification->title=$msg['title'];
					$notification->created_at=date('Y-m-d H:i:s');
					$notification->save();
				}
				
				if (count($ids) > 0) {					
					Helper::sendNotification($ids, $message, 'Order Created', $user_id, 'user', $seller_id, 'seller', $ids);

				}
			}
			$orders = new_orders::find($order_id);
			$orders->order_status = 'cancel';
			$orders->reject_cancel_reason = $cancelreason_id;
			$orders->cancel_datetime = date('Y-m-d H:i:s');
			$orders->updated_at = date('Y-m-d H:i:s');
			$orders->save();
			$order = new_orders::where('id',$order_id)->first();
         	$order_history = new new_order_history();
            $order_history->order_id = $order->id;
            $order_history->customer_id = $order->customer_id;
            $order_history->prescription_id = $order->prescription_id;
            $order_history->order_number = $order->order_number;
            $order_history->order_status = $order->order_status;
            $order_history->order_note = $order->order_note;
            $order_history->address_id = $order->address_id;
            $order_history->audio = $order->audio;
            $order_history->audio_info = $order->audio_info;
            $order_history->order_type = $order->order_type;
            $order_history->total_days = $order->total_days;
            $order_history->reminder_days = $order->reminder_days;
            $order_history->pharmacy_id = $order->pharmacy_id;
            $order_history->process_user_type = $order->process_user_type;
            $order_history->process_user_id = $order->process_user_id;
            $order_history->logistic_user_id = $order->logistic_user_id;
            $order_history->deliveryboy_id = $order->deliveryboy_id;
            $order_history->second_attempt_delivery_id = $order->second_attempt_delivery_id;
            $order_history->create_datetime  = $order->create_datetime;
            $order_history->accept_datetime  = $order->accept_datetime;
            $order_history->assign_datetime  = $order->assign_datetime;
            $order_history->pickup_datetime  = $order->pickup_datetime;
            $order_history->deliver_datetime = $order->deliver_datetime;
            $order_history->second_attempt_delivery_datetime = $order->second_attempt_delivery_datetime;
            $order_history->return_datetime  = $order->return_datetime;
            $order_history->cancel_datetime  = $order->cancel_datetime;
            $order_history->rejectby_user  = $order->rejectby_user;
            $order_history->reject_user_id  = $order->reject_user_id;
            $order_history->reject_cancel_reason  = $order->reject_cancel_reason;
            $order_history->leave_neighbour  = $order->leave_neighbour;
            $order_history->neighbour_info  = $order->neighbour_info;
            $order_history->is_external_delivery  = $order->is_external_delivery;
            $order_history->external_delivery_initiatedby  = $order->external_delivery_initiatedby;
            $order_history->order_amount  = $order->order_amount;
            $order_history->delivery_charges_id   = $order->delivery_charges_id;
            $order_history->is_delivery_charge_collect  = $order->is_delivery_charge_collect;
            $order_history->is_amount_collect  = $order->is_amount_collect;
            $order_history->is_refund_intiated  = $order->is_refund_intiated;
            $order_history->refund_datetime  = $order->refund_datetime;
            /*$order_history->refund_info  = $order->refund_info;
            $order_history->is_admin_amount_collect  = $order->is_admin_amount_collect;
            $order_history->is_pharmacy_amount_collect  = $order->is_pharmacy_amount_collect;
            $order_history->is_logistic_charge_collect  = $order->is_logistic_charge_collect;
            $order_history->is_admin_delivery_charge_collect  = $order->is_admin_delivery_charge_collect;
            $order_history->is_logistic_amount_collect  = $order->is_logistic_amount_collect;*/
            $order_history->created_at    = $order->created_at;
            $order_history->updated_at  = $order->updated_at;
            $order_history->save();
            $order_delete = new_orders::find($order->id);
            $order_delete->delete();
			$response['status'] = 200;
			$response['message'] = 'Your order has been successfully cancelled';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Order not found';
		}
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
		$response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}
	public function mycartlist(Request $request)
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
		$is_completed = isset($content->is_completed) ? $content->is_completed : '';
		$page = isset($content->page) ? $content->page : '';
		
		$params = [
			'user_id' => $user_id,
			'is_completed'=>$is_completed
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'is_completed' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
		}
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$orders_arr_data1 = array();

		if ($is_completed == 0) {
			$raw_query = new_orders::query()->select('new_orders.order_status','new_orders.prescription_id','new_orders.create_datetime','new_orders.external_delivery_initiatedby','new_orders.delivery_charges_id','new_orders.is_external_delivery', 'new_orders.pharmacy_id','new_orders.id AS ID', 'new_orders.order_number')
				->with(
				[
					'prescriptions' => function($query) {
						$query->select('id','image', 'name');
					},
					'pharmacy' => function($query) {
						$query->select('id','name','address', 'discount', 'mobile_number');
					},
				])
				->where('customer_id', $user_id)->orderby('new_orders.id','desc');

			$total = $raw_query->count();
			$page = $page;
			if($total > ($page*10)){
				$is_record_available = 1;
			}else{
				$is_record_available = 0;
			}
			$per_page = 10;
			$response['data']->currentPageIndex = $page;
			$response['data']->totalPage = ceil($total/$per_page);
			$data_array = $raw_query->paginate($per_page,'','',$page);
			
		}else{
			$raw_query = new_order_history::query()->select('new_order_history.order_status','new_order_history.prescription_id','new_order_history.create_datetime','new_order_history.external_delivery_initiatedby','new_order_history.delivery_charges_id','new_order_history.is_external_delivery', 'new_order_history.pharmacy_id','new_order_history.id AS ID', 'new_order_history.order_number')
				->with(
				[
					'prescriptions' => function($query) {
						$query->select('id','image');
					},
					'pharmacy' => function($query) {
						$query->select('id','name','address', 'discount', 'mobile_number');
					},
				])
			->where('customer_id', $user_id)->orderby('new_order_history.id','desc');

			$total = $raw_query->count();
			$page = $page;
			if($total > ($page*10)){
				$is_record_available = 1;
			}else{
				$is_record_available = 0;
			}
			$per_page = 10;
			$response['data']->currentPageIndex = $page;
			$response['data']->totalPage = ceil($total/$per_page);
			$data_array = $raw_query->paginate($per_page,'','',$page);
			// $data_array = $orders_data1->toArray();
			// $data_array = $orders_data1->data;
		}
		if(count($data_array)>0){
			
			$order_status_array = array(
				'payment_pending'=>'Payment Pending',
				'new'=>'Pending', // USER
				'accept'=>'Accepted', // PHARMACY, SELLER
				'reject'=>'Rejected', // PHARMACY, SELLER
				'assign'=>'Ready For Pickup', // PHARMACY, SELLER, LOGISTIC
				'pickup'=>'Out For Delivery', // DELIVERY BOY
				'complete'=>'Delivered', // DELIVERY BOY
				'incomplete'=>'Delivery Attempted', // DELIVERY BOY
				'cancel'=>'Cancelled', // USER
			);

			foreach($data_array as $key=>$val){
				//old code
				/*$image_url = '';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($val->prescriptions->image)) {

					$filename = storage_path('app/public/uploads/prescription/' . $val->prescriptions->image);
				
					if (File::exists($filename)) {
						$image_url = asset('storage/app/public/uploads/prescription/' . $val->prescriptions->image);
					}
				}*/	
				//new code
				$images_array=[];
                    $image_data = prescription_multiple_image::where('prescription_id',$val->prescription_id)->get();
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
				$orders_arr_data1[$key]['id'] = $val->ID;
				$orders_arr_data1[$key]['pharmacy_id'] = $val->pharmacy_id;
				$orders_arr_data1[$key]['prescription_name'] = !empty($val->prescriptions->name) ? $val->prescriptions->name : '';
				$orders_arr_data1[$key]['order_number'] = ($val->order_number)?$val->order_number:'';
				$orders_arr_data1[$key]['prescription'] = $images_array;
				$orders_arr_data1[$key]['pharmacy'] = isset($val->pharmacy->name) ? $val->pharmacy->name : '';
				$orders_arr_data1[$key]['pharmacy_address'] = isset($val->pharmacy->address) ? $val->pharmacy->address : '';
				/*$orders_arr[$key]['logistic_id'] = 	$val->logistic_user_id;*/
				$orders_arr_data1[$key]['mobile_number'] = isset($val->pharmacy->mobile_number)?$val->pharmacy->mobile_number:'';
				$orders_arr_data1[$key]['discount'] = isset($val->pharmacy->discount)?$val->pharmacy->discount.'% off on your order ':'';
				$orders_arr_data1[$key]['order_status'] = (isset($order_status_array[$val->order_status]))?$order_status_array[$val->order_status]:'';
				$orders_arr_data1[$key]['order_date'] = date('d-m-Y h:i A',strtotime($val->create_datetime));

				if($val->external_delivery_initiatedby == 'customer'){
					if(!empty($val->delivery_charges_id)){
						$d = new_delivery_charges::where('id',$val->delivery_charges_id)->first();
						$orders_arr_data1[$key]['delivery_type'] = isset($d->delivery_type) ? $d->delivery_type : '';	
					}	
				}else{
					$orders_arr_data1[$key]['delivery_type'] = 'free';
				}			
				if($val->is_external_delivery==1){
					$orders_arr_data1[$key]['is_paid'] = 'True';
				}else{
					$orders_arr_data1[$key]['is_paid'] = 'False';
				}
			}
			$response['status'] = 200;
		}  else {
			$response['status'] = 404;
		}
		$response['data']->content = $orders_arr_data1;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}

	public function mycartdetail(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		// $order_id = $request->order_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($plainText);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$order_id = isset($content->order_id) ? $content->order_id : '';
		
		$params = [
			'user_id' => $user_id,
			'order_id' => $order_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$orders_data1 = new_orders::with('prescriptions')->where(['customer_id'=>$user_id,'id'=> $order_id])->get();
		$orders_data2 = new_order_history::with('prescriptions')->where(['customer_id'=>$user_id,'id'=> $order_id])->get();

		$orders_arr1 = array();
		$orders = $orders_data1->merge($orders_data2);

		$audio_url = '';
		if(count($orders)>0){
			//$order = new_orders::with('prescriptions')->find($order_id);
			$image_url = '';
			$image_url = url('/').'/uploads/placeholder.png';
			if (!empty($orders[0]->prescriptions->image)) {

				$filename = storage_path('app/public/uploads/prescription/' . $orders[0]->prescriptions->image);
			
				if (File::exists($filename)) {
					$image_url = asset('storage/app/public/uploads/prescription/' . $orders[0]->prescriptions->image);
				}
			}

			if (!empty($orders[0]->audio)) {
				$filename = storage_path('app/public/uploads/audio/' . $orders[0]->audio);
				if (File::exists($filename)) {
					$audio_url = asset('storage/app/public/uploads/audio/' . $orders[0]->audio);
				}
			}
				
			$order_status_array = array(
				'accept'=>'Accepted',
				'assign'=>'Ready For Pickup',
				'complete'=>'Delivered',
				'incomplete'=>'Delivery Attempted',
				'pickup' => 'Out For Delivery',
				'reject'=>'Rejected',
				'cancel'=>'Cancelled',
				'new'=>'Pending',
				'payment_pending'=>'Payment Pending',
			);
			/*$reject_reason = '';
			if($order->order_status=='incomplete'){
				if($order->incompletereason_id > 0){
					$incomplete = Incompletereason::find($order->incompletereason_id);
					if($incomplete){
						$reject_reason = $incomplete->reason;
					}
				}
			}
			
			if($order->order_status=='reject'){
				if($order->rejectreason_id > 0){
					$reject = Rejectreason::find($order->rejectreason_id);
					if($reject){
						$reject_reason = $reject->reason;
					}
				}
			}
			
			if($order->order_status=='cancel'){
				if($order->cancelreason_id > 0){
					$cancel = Cancelreason::find($order->cancelreason_id);
					if($cancel){
						$reject_reason = $cancel->reason;
					}
				}
			}*/
			//$destination_address[] = [];
			$address_data = new_address::where('id',$orders[0]->address_id)->get();
			foreach ($address_data as $key => $val) {
				$destination_address[] = [
					'address_id' => $val->id,
					'user_id' => $val->user_id,
					'locality'=>$val->locality,
					'address' =>$val->address,
					'name' => $val->name,
					'mobileno' =>$val->mobileno,
					'blockno' =>$val->blockno,
					'streetname'=>$val->streetname,
					'city'=>$val->city,
					'pincode'=>$val->pincode,
					'latitude'=>$val->latitude,
					'longitude'=>$val->longitude
				];
			}
					
			$order_feedback = Orderfeedback::where('order_id',$orders[0]->id)->where('user_id',$user_id)->get();
			if(count($order_feedback)>0){
				$ord_feedback = 1;
			}else{
				$ord_feedback = 0;
			}
			$pharmacy = new_pharmacies::where('id',$orders[0]->pharmacy_id)->first();

			$userdata = new_users::where('id',$user_id)->first();

			$orders_arr1[0]['id'] = $orders[0]->id;
			$orders_arr1[0]['payment_url'] = '';
			$orders_arr1[0]['pharmacy_id'] = $orders[0]->pharmacy_id;
			$orders_arr1[0]['order_number'] = $orders[0]->order_number;
			$orders_arr1[0]['order_note'] = $orders[0]->order_note;
			$orders_arr1[0]['origin_user_name'] = isset($pharmacy->name) ? $pharmacy->name : '';
			$orders_arr1[0]['origin_address'] = isset($pharmacy->address) ? $pharmacy->address : '';
			$orders_arr1[0]['destination_user_name'] = $userdata->name;
			$orders_arr1[0]['destination_address'] = ($destination_address)?$destination_address:'';
			
			$orders_arr1[0]['order_detail_image'] = $image_url;
			$orders_arr1[0]['audio'] = $audio_url;
			$orders_arr1[0]['audio_info'] = date('d-m-Y h:i A',strtotime($orders[0]->audio_info));
			$orders_arr1[0]['order_type'] = ($orders[0]->order_type!='')?$orders[0]->order_type:'';
			$orders_arr1[0]['total_days'] = ($orders[0]->total_days!='')?str_replace('_',' ',$orders[0]->total_days):'';
			$orders_arr1[0]['order_note'] = ($orders[0]->order_note!='')?str_replace('_',' ',$orders[0]->order_note):'';
			$orders_arr1[0]['reminder_days'] = ($orders[0]->reminder_days!='')?str_replace('_',' ',$orders[0]->reminder_days):'';
			$orders_arr1[0]['reject_reason'] = ($orders[0]->reject_cancel_reason)?$orders[0]->reject_cancel_reason:'';
			$orders_arr1[0]['order_feedback'] = $ord_feedback;
			$orders_arr1[0]['order_date'] = date('d-m-Y h:i A',strtotime($orders[0]->created_at));
			$orders_arr1[0]['prescription_id'] = $orders[0]->prescription_id;
			$orders_arr1[0]['leaved_with_neighbor'] = $orders[0]->leave_neighbour;
			$orders_arr1[0]['neighbour_info'] = ($orders[0]->neighbour_info)?$orders[0]->neighbour_info:'';
			$orders_arr1[0]['refund_reference_no'] = '';
			$orders_arr1[0]['reference_code'] = '';
			$orders_arr1[0]['mobile_number'] = isset($pharmacy->mobile_number)?$pharmacy->mobile_number:'';
			$orders_arr1[0]['payment_order_id'] = $orders[0]->payment_order_id;
			if($orders[0]->order_status == 'cancel' && !empty($orders[0]->delivery_charges_id)){
				$orders_arr1[0]['refund_string'] = '';
				$orders_arr1[0]['order_status'] = (isset($order_status_array[$orders[0]->order_status]))?$order_status_array[$orders[0]->order_status]:'';
			}else{
				$orders_arr1[0]['order_status'] = (isset($order_status_array[$orders[0]->order_status]))?$order_status_array[$orders[0]->order_status]:'';
				$orders_arr1[0]['refund_string'] = '';
			}
			if($orders[0]->external_delivery_initiatedby == 'customer'){
				if(!empty($orders[0]->delivery_charges_id)){
					$dev_data = new_delivery_charges::where('id',$orders[0]->delivery_charges_id)->get();
					foreach ($dev_data as $dev) {
							$orders_arr1[0]['delivery_type'] = $dev->delivery_type;	
							$orders_arr1[0]['unit'] = 'Rs';	
							$orders_arr1[0]['delivery_price'] = $dev->delivery_price;
					}
				}
			}else{
				$orders_arr1[0]['delivery_type'] = 'free';
			}	
			if($orders[0]->is_external_delivery==1){
				$orders_arr1[0]['is_paid'] = 'True';
			}else{
				$orders_arr1[0]['is_paid'] = 'False';
			}
			if($orders[0]->is_external_delivery==1 && $orders[0]->external_delivery_initiatedby == 'customer' && $orders[0]->order_status == 'payment_pending'){
				$orders_arr1[0]['payment_url'] = 'create_transaction/'.$orders[0]->id;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'My cart detail';
		$response['data'] = $orders_arr1;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	
	}

	public function delivery_charges(Request $request)
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

        /*$logistic_id = isset($content->logistic_id) ? $content->logistic_id : '';
		
		$params = [
			'logistic_id' => $logistic_id,
		];
		
		$validator = Validator::make($params, [
            'logistic_id' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }*/

        $delivery_data = new_delivery_charges::all();
        if(!empty($delivery_data)){
                 foreach($delivery_data as $value) {
	                 	if($value->is_user == 1){
	                 		if($value->delivery_type != "free"){
		                 		$delivery[] = [
		                            'delivery_charges_id' => $value->id,
		                            'delivery_type' => $value->delivery_type,
		                            'delivery_price' => $value->delivery_price,
		                            'unit' => 'Rs',
		                            'time'=> $value->delivery_approx_time,
		                        ];
	                 		}
	                 	}
                    }
                $response['status'] = 200;
        } else {
                $response['status'] = 404;
        }

        $response['message'] = 'Delivery Charges';
        $response['data'] = $delivery;
        
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}

	public function notification_user(Request $request)
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
        $page = isset($content->page) ? $content->page : '';

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
        $notification =[];
        $token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
        $notification_data = notification_user::select('id','user_id','title','subtitle','order_id','created_at')->where('user_id',$user_id)->orderBy('id','DESC');
        $total = $notification_data->count();
        $page = $page;
        if($total > ($page*10)){
          $is_record_available = 1;
        }else{
          $is_record_available = 0;
        }
        $per_page = 10;
        $response['data']->currentPageIndex = $page;
        $response['data']->totalPage = ceil($total/$per_page);
        $orders = $notification_data->paginate($per_page,'','',$page);
        $data_array = $orders->toArray();
        $data_array = $data_array['data']; 

        if(count($data_array)>0){
                 foreach($data_array as $value) {
	                 		$notification[] = [
	                            'id' => $value['id'],
	                            'user_id' => $value['user_id'],
	                            'title' => $value['title'],
	                            'subtitle'=> $value['subtitle'],
	                            'order_id'=> (string)$value['order_id'],
	                            'created_at'=> date('h:i A', strtotime($value['created_at'])),
	                            'date'=> (date_format(new DateTime($value['created_at']),"Y-m-d"))
	                        ];
                    }
                $response['status'] = 200;
                $response['message'] = 'Notification For Users';
        } else {
                $response['status'] = 404;
                $response['message'] = 'Notification For Users';
        }
        $response['data']->content = $notification;
        }else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($cipher, 200);
	}
	 public function sendPushNotificationSeller($fields) {
        //firebase server url to send the curl request
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=AAAAKIqNu8Q:APA91bEJSvjmr9TiUjAtQRc1PosKmb3nqRqQULAFUXHnujLmTw4zLmiSLD27gFffQeqxSR7U75JXUO-V65WIcMKorV7OjZ2boepBanPFwPFnxBEyCp7Uv0OwMVjnhMHp1ib_GtFiEwI8',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
          return $result; 
    }

    public function update_transaction(Request $request)
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
        $order_id = isset($content->order_id) ? $content->order_id : '';
		$transaction_id = isset($content->transaction_id) ? $content->transaction_id : '';

		$params = [
			'user_id' => $user_id,
			'order_id' => $order_id,
			'transaction_id' => $transaction_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'transaction_id' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
        		new_orders::where('payment_order_id', $order_id)->update(array('order_status' => 'new'));
				$transaction_data = new transaction();
				$transaction_data->transaction_id = $transaction_id;
				$transaction_data->order_id = $order_id;
				$transaction_data->user_id = $user_id;

				$user_data  =  new_users::where('id',$user_id)->first();
				$transaction_data->email = $user_data->email;
				$transaction_data->mobile_number = $user_data->mobile_number;

				$order_data = new_orders::where('payment_order_id',$order_id)->first();
				$transaction_data->order_number = $order_data->order_number;
				$transaction_data->order_amount = $order_data->order_amount;
				$transaction_data->order_status = $order_data->order_status;

				$transaction_data->payment_status = 'pending_payment';
				$transaction_data->transaction_date = date('Y-m-d H:i:s');
				$transaction_data->save();

				$response['status'] = 200;
	            $response['message'] = 'Transaction successfully!!';
        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		return response($cipher, 200);
    }
	
	public function get_order_status(Request $request)
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

        $order_id = isset($content->order_id) ? $content->order_id : '';

		$params = [
			'order_id' => $order_id,
		];
		
		$validator = Validator::make($params, [
            'order_id' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }

        $order_data = [];
        $order_status = new_orders::where('id',$order_id)->first();
        if(!empty($order_status)){
        	$pharmacy_data = new_pharmacies::where('id', $order_status->pharmacy_id)->first();
        	$order_data[] = [
        			'order_number' => $order_status->order_number,
        			'order_status' => $order_status->order_status,
        			'pharmacy_name' => $pharmacy_data->name
        	];
        	$response['status'] = 200;
	        $response['message'] = 'Order status';
	        $response['data'] = $order_data;
        }else{
        	$response['status'] = 404;
	        $response['message'] = 'Order status';
	        $response['data'] = [];
        }

        $response = json_encode($response);
		$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		return response($cipher, 200);
	}
	public function add_records(Request $request)
	{
		for ($i=1; $i<=5000; $i++) { 
			$order_data =  new new_orders();
			$order_data->customer_id= '103';
			$order_data->prescription_id='226';
			$order_data->address_id='117';
			$order_data->pharmacy_id='32';
			$order_data->is_external_delivery=0;
			$order_data->logistic_user_id=-1;
			$order_data->delivery_charges_id=1;
			$order_data->external_delivery_initiatedby='customer';
			$order_data->create_datetime=date('Y-m-d H:i:s');
			$order_data->audio_info=date('Y-m-d H:i:s');
			$order_data->created_at = date('Y-m-d H:i:s');
			$order_data->updated_at = date('Y-m-d H:i:s');
			$order_data->order_status = 'accept';
			$order_data->process_user_type ='seller';
			$order_data->process_user_id = 37;
			$order_data->deliveryboy_id = 0;
			$order_data->leave_neighbour='true';
			$order_data->order_type='as_per_prescription';
			$order_data->is_intersection = 1;
			$order_data->save();
			$string = 'PHAR'.$order_data->id; 
			$order = new_orders::where('id',$order_data->id)->first();
			$order->order_number=$string;
			$order->save();
		}
		echo "Order Added successfully..";
	}
	public function mycartlist_imagedata(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$is_completed = isset($content->is_completed) ? $content->is_completed : '';
		$page = isset($content->page) ? $content->page : '';
		
		$params = [
			'user_id' => $user_id,
			'is_completed'=>$is_completed
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'is_completed' => 'required',
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
		}
		/*$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){*/
		$orders_arr_data1 = array();

		if ($is_completed == 0) {
			$raw_query = new_orders::query()->select('new_orders.order_status','new_orders.create_datetime','new_orders.external_delivery_initiatedby','new_orders.delivery_charges_id','new_orders.is_external_delivery', 'new_orders.pharmacy_id','new_orders.id AS ID', 'new_orders.order_number','new_orders.prescription_id')
				->with(
				[
					'prescriptions' => function($query) {
						$query->select('id','image', 'name');
					},
					'pharmacy' => function($query) {
						$query->select('id','name','address', 'discount', 'mobile_number');
					},
				])
				->where('customer_id', $user_id)->orderby('new_orders.id','desc');

			$total = $raw_query->count();
			$page = $page;
			if($total > ($page*10)){
				$is_record_available = 1;
			}else{
				$is_record_available = 0;
			}
			$per_page = 10;
			$response['data']->currentPageIndex = $page;
			$response['data']->totalPage = ceil($total/$per_page);
			$data_array = $raw_query->paginate($per_page,'','',$page);
			
		}else{
			$raw_query = new_order_history::query()->select('new_order_history.order_status','new_order_history.create_datetime','new_order_history.external_delivery_initiatedby','new_order_history.delivery_charges_id','new_order_history.is_external_delivery', 'new_order_history.pharmacy_id','new_order_history.id AS ID', 'new_order_history.order_number','new_orders.prescription_id')
				->with(
				[
					'prescriptions' => function($query) {
						$query->select('id','image');
					},
					'pharmacy' => function($query) {
						$query->select('id','name','address', 'discount', 'mobile_number');
					},
				])
			->where('customer_id', $user_id)->orderby('new_order_history.id','desc');

			$total = $raw_query->count();
			$page = $page;
			if($total > ($page*10)){
				$is_record_available = 1;
			}else{
				$is_record_available = 0;
			}
			$per_page = 10;
			$response['data']->currentPageIndex = $page;
			$response['data']->totalPage = ceil($total/$per_page);
			$data_array = $raw_query->paginate($per_page,'','',$page);
			// $data_array = $orders_data1->toArray();
			// $data_array = $orders_data1->data;
		}
		if(count($data_array)>0){
			
			$order_status_array = array(
				'payment_pending'=>'Payment Pending',
				'new'=>'Pending', // USER
				'accept'=>'Accepted', // PHARMACY, SELLER
				'reject'=>'Rejected', // PHARMACY, SELLER
				'assign'=>'Ready For Pickup', // PHARMACY, SELLER, LOGISTIC
				'pickup'=>'Out For Delivery', // DELIVERY BOY
				'complete'=>'Delivered', // DELIVERY BOY
				'incomplete'=>'Delivery Attempted', // DELIVERY BOY
				'cancel'=>'Cancelled', // USER
			);

			foreach($data_array as $key=>$val){
				$mutiple_data = multiple_prescription::where(['prescription_id'=>$val->prescription_id,'is_delete'=>'0'])->get();
				$mutiple_images = [];
				foreach ($mutiple_data as $value) {
						$mutiple_images[]=[
						'id'	=> $value->multiple_prescription_id,
						'image' => $value->image,
					];	
				}
				
				$orders_arr_data1[$key]['id'] = $val->ID;
				$orders_arr_data1[$key]['pharmacy_id'] = $val->pharmacy_id;
				$orders_arr_data1[$key]['prescription_name'] = !empty($val->prescriptions->name) ? $val->prescriptions->name : '';
				$orders_arr_data1[$key]['order_number'] = ($val->order_number)?$val->order_number:'';
				$orders_arr_data1[$key]['prescription'] = $mutiple_images;
				$orders_arr_data1[$key]['pharmacy'] = isset($val->pharmacy->name) ? $val->pharmacy->name : '';
				$orders_arr_data1[$key]['pharmacy_address'] = isset($val->pharmacy->address) ? $val->pharmacy->address : '';
				/*$orders_arr[$key]['logistic_id'] = 	$val->logistic_user_id;*/
				$orders_arr_data1[$key]['mobile_number'] = isset($val->pharmacy->mobile_number)?$val->pharmacy->mobile_number:'';
				$orders_arr_data1[$key]['discount'] = isset($val->pharmacy->discount)?$val->pharmacy->discount.'% off on your order ':'';
				$orders_arr_data1[$key]['order_status'] = (isset($order_status_array[$val->order_status]))?$order_status_array[$val->order_status]:'';
				$orders_arr_data1[$key]['order_date'] = date('d-m-Y h:i A',strtotime($val->create_datetime));

				if($val->external_delivery_initiatedby == 'customer'){
					if(!empty($val->delivery_charges_id)){
						$d = new_delivery_charges::where('id',$val->delivery_charges_id)->first();
						$orders_arr_data1[$key]['delivery_type'] = isset($d->delivery_type) ? $d->delivery_type : '';	
					}	
				}else{
					$orders_arr_data1[$key]['delivery_type'] = 'free';
				}			
				if($val->is_external_delivery==1){
					$orders_arr_data1[$key]['is_paid'] = 'True';
				}else{
					$orders_arr_data1[$key]['is_paid'] = 'False';
				}
			}
			$response['status'] = 200;
		}  else {
			$response['status'] = 404;
		}
		$response['data']->content = $orders_arr_data1;
		/*}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}*/
        $response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($response, 200);
	}
	public function mycartdetail_imagedata(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();
		// $user_id = $request->user_id;
		// $order_id = $request->order_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		$order_id = isset($content->order_id) ? $content->order_id : '';
		
		$params = [
			'user_id' => $user_id,
			'order_id' => $order_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		/*$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){*/
		$orders_data1 = new_orders::with('prescriptions')->where(['customer_id'=>$user_id,'id'=> $order_id])->get();
		$orders_data2 = new_order_history::with('prescriptions')->where(['customer_id'=>$user_id,'id'=> $order_id])->get();

		$orders_arr1 = array();
		$orders = $orders_data1->merge($orders_data2);

		$audio_url = '';
		if(count($orders)>0){
			//$order = new_orders::with('prescriptions')->find($order_id);
			$mutiple_data = multiple_prescription::where(['prescription_id'=>$orders[0]->prescription_id,'is_delete'=>'0'])->get();
				$mutiple_images = [];
				foreach ($mutiple_data as $value) {
						$mutiple_images[]=[
						'id'	=> $value->multiple_prescription_id,
						'image' => $value->image,
					];	
				}	

			if (!empty($orders[0]->audio)) {
				$filename = storage_path('app/public/uploads/audio/' . $orders[0]->audio);
				if (File::exists($filename)) {
					$audio_url = asset('storage/app/public/uploads/audio/' . $orders[0]->audio);
				}
			}
				
			$order_status_array = array(
				'accept'=>'Accepted',
				'assign'=>'Ready For Pickup',
				'complete'=>'Delivered',
				'incomplete'=>'Delivery Attempted',
				'pickup' => 'Out For Delivery',
				'reject'=>'Rejected',
				'cancel'=>'Cancelled',
				'new'=>'Pending',
				'payment_pending'=>'Payment Pending',
			);
			
			//$destination_address[] = [];
			$address_data = new_address::where('id',$orders[0]->address_id)->get();
			foreach ($address_data as $key => $val) {
				$destination_address[] = [
					'address_id' => $val->id,
					'user_id' => $val->user_id,
					'locality'=>$val->locality,
					'address' =>$val->address,
					'name' => $val->name,
					'mobileno' =>$val->mobileno,
					'blockno' =>$val->blockno,
					'streetname'=>$val->streetname,
					'city'=>$val->city,
					'pincode'=>$val->pincode,
					'latitude'=>$val->latitude,
					'longitude'=>$val->longitude
				];
			}	
			$order_feedback = Orderfeedback::where('order_id',$orders[0]->id)->where('user_id',$user_id)->get();
			if(count($order_feedback)>0){
				$ord_feedback = 1;
			}else{
				$ord_feedback = 0;
			}
			$pharmacy = new_pharmacies::where('id',$orders[0]->pharmacy_id)->first();

			$userdata = new_users::where('id',$user_id)->first();

			$orders_arr1[0]['id'] = $orders[0]->id;
			$orders_arr1[0]['payment_url'] = '';
			$orders_arr1[0]['pharmacy_id'] = $orders[0]->pharmacy_id;
			$orders_arr1[0]['order_number'] = $orders[0]->order_number;
			$orders_arr1[0]['order_note'] = $orders[0]->order_note;
			$orders_arr1[0]['origin_user_name'] = isset($pharmacy->name) ? $pharmacy->name : '';
			$orders_arr1[0]['origin_address'] = isset($pharmacy->address) ? $pharmacy->address : '';
			$orders_arr1[0]['destination_user_name'] = $userdata->name;
			$orders_arr1[0]['destination_address'] = ($destination_address)?$destination_address:'';
			
			$orders_arr1[0]['order_detail_image'] = $mutiple_images;
			$orders_arr1[0]['audio'] = $audio_url;
			$orders_arr1[0]['audio_info'] = date('d-m-Y h:i A',strtotime($orders[0]->audio_info));
			$orders_arr1[0]['order_type'] = ($orders[0]->order_type!='')?$orders[0]->order_type:'';
			$orders_arr1[0]['total_days'] = ($orders[0]->total_days!='')?str_replace('_',' ',$orders[0]->total_days):'';
			$orders_arr1[0]['order_note'] = ($orders[0]->order_note!='')?str_replace('_',' ',$orders[0]->order_note):'';
			$orders_arr1[0]['reminder_days'] = ($orders[0]->reminder_days!='')?str_replace('_',' ',$orders[0]->reminder_days):'';
			$orders_arr1[0]['reject_reason'] = ($orders[0]->reject_cancel_reason)?$orders[0]->reject_cancel_reason:'';
			$orders_arr1[0]['order_feedback'] = $ord_feedback;
			$orders_arr1[0]['order_date'] = date('d-m-Y h:i A',strtotime($orders[0]->created_at));
			$orders_arr1[0]['prescription_id'] = $orders[0]->prescription_id;
			$orders_arr1[0]['leaved_with_neighbor'] = $orders[0]->leave_neighbour;
			$orders_arr1[0]['neighbour_info'] = ($orders[0]->neighbour_info)?$orders[0]->neighbour_info:'';
			$orders_arr1[0]['refund_reference_no'] = '';
			$orders_arr1[0]['reference_code'] = '';
			$orders_arr1[0]['mobile_number'] = isset($pharmacy->mobile_number)?$pharmacy->mobile_number:'';
			$orders_arr1[0]['payment_order_id'] = $orders[0]->payment_order_id;
			if($orders[0]->order_status == 'cancel' && !empty($orders[0]->delivery_charges_id)){
				$orders_arr1[0]['refund_string'] = '';
				$orders_arr1[0]['order_status'] = (isset($order_status_array[$orders[0]->order_status]))?$order_status_array[$orders[0]->order_status]:'';
			}else{
				$orders_arr1[0]['order_status'] = (isset($order_status_array[$orders[0]->order_status]))?$order_status_array[$orders[0]->order_status]:'';
				$orders_arr1[0]['refund_string'] = '';
			}
			if($orders[0]->external_delivery_initiatedby == 'customer'){
				if(!empty($orders[0]->delivery_charges_id)){
					$dev_data = new_delivery_charges::where('id',$orders[0]->delivery_charges_id)->get();
					foreach ($dev_data as $dev) {
							$orders_arr1[0]['delivery_type'] = $dev->delivery_type;	
							$orders_arr1[0]['unit'] = 'Rs';	
							$orders_arr1[0]['delivery_price'] = $dev->delivery_price;
					}
				}
			}else{
				$orders_arr1[0]['delivery_type'] = 'free';
			}	
			if($orders[0]->is_external_delivery==1){
				$orders_arr1[0]['is_paid'] = 'True';
			}else{
				$orders_arr1[0]['is_paid'] = 'False';
			}
			if($orders[0]->is_external_delivery==1 && $orders[0]->external_delivery_initiatedby == 'customer' && $orders[0]->order_status == 'payment_pending'){
				$orders_arr1[0]['payment_url'] = 'create_transaction/'.$orders[0]->id;
			}
			$response['status'] = 200;
		} else {
			$response['status'] = 404;
		}
		$response['message'] = 'My cart detail';
		$response['data'] = $orders_arr1;
		/*}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}*/
        $response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($response, 200);	
	}
	public function createorder_imagedata(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = array();
		$response['data']['payment_url'] = '';
		$response['data']['payment_order_id'] = '';
		// $user_id = $request->user_id;
		// $pharmacy_id = $request->pharmacy_id;
		// $address_id = $request->address_id;
		
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$data = $request->input('data');
		//$plainText = $encryption->decryptCipherTextWithRandomIV($data, $secretyKey);
		$content = json_decode($data);
		
		$user_id       = isset($content->user_id) ? $content->user_id : '';
		$pharmacy_id   = isset($content->pharmacy_id) ? $content->pharmacy_id : '';
		$address_id    = isset($content->address_id) ? $content->address_id : '';
		$order_type    = isset($content->order_type) ? $content->order_type : '' ;
		$total_days    = isset($content->total_days) ? $content->total_days : '';
		$prescription_id  = isset($content->prescription_id) ? $content->prescription_id : '';
		$prescription_name  = isset($content->prescription_name) ? $content->prescription_name : '';
		$reminder_days = isset($content->reminder_days) ? $content->reminder_days : '';
		$order_note = isset($content->order_note) ? $content->order_note : '';
		$audio = isset($content->audio) ? $content->audio : '';
		$leaved_with_neighbor = isset($content->leaved_with_neighbor)?($content->leaved_with_neighbor):'false';
		$delivery_charges_id = isset($content->delivery_charges_id) ? $content->delivery_charges_id : '';
		$logistic_id = isset($content->logistic_id) ? $content->logistic_id : 0;
		$is_external_delivery = isset($content->is_external_delivery) ? $content->is_external_delivery : '';
		$is_intersection = isset($content->is_intersection) ? $content->is_intersection : '';
		$prescription = isset($content->prescription) ? implode(' ',$content->prescription) : '';

		$params = [
			'user_id' => $user_id,
			'pharmacy_id' => $pharmacy_id,
			//'address_id' => $address_id,
			'order_type' => $order_type,
			'total_days' => $total_days,
			//'prescription_id' => $prescription_id,
			'reminder_days' => $reminder_days
		];
		 
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'pharmacy_id' => 'required',
            'order_type' => 'required',
            //'total_days' => 'required',
            //'prescription_id' => 'required',
            //'reminder_days' => 'required'
        ]);
		
        if ($validator->fails()) {
            return $this->send_error($validator->errors()->first());  
        }
		/*
		$token =  $request->bearerToken();
		$user = new_users::where(['id'=>$user_id,'api_token'=>$token])->first();
		if(!empty($user)){*/
			$userdata = new_users::find($user_id);
			
			if($userdata){
				
				$pre = Prescription::where('id','=',$prescription_id);
				$prescriptions = new Prescription();
				if (($pre->count()) == 0) {
					$find_name = Prescription::where(['user_id'=>$user_id,'name'=>$prescription_name,"is_delete"=>"0"])->get();
					if(count($find_name)>0){
						$response['status'] = 404;
						$response['message'] = 'Prescription name already exists';
						$response = json_encode($response);
						//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
						
						return response($response, 200);
					}else{
						if (!empty($prescription)) {
							$prescriptions = new Prescription();
							$prescriptions->user_id = $user_id;
							$prescriptions->name = $prescription_name;
							//$prescriptions->image = $prescription;
							$prescriptions->prescription_date = date('Y-m-d H:i:s');
							$prescriptions->save();
							$prescription_id = $prescriptions->id;
							$code_data = explode(' ',$prescription);
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
						} else {
							$response['status'] = 404;
							$response['message'] = 'Please upload prescription';
							
							$response = json_encode($response);
							//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
							
							return response($response, 200);
						}
					}
				} else {
					$pre = $pre->get();
					$prescription_id = $prescription_id;

					//$prescriptions->image = $pre[0]->image;
				} 
				
				$audio_name = '';
				$music_file = $request->file('audio'); 
				if(isset($music_file)) { 
					$filename= time().'-'.$music_file->getClientOriginalName();
					Storage::disk('public')->put('uploads/audio/'.$filename, file_get_contents($music_file));
					$audio_name = $filename; 
				}
				$neworder = new new_orders();
				$neworder->process_user_type ='';
				$neworder->process_user_id = 0;
				$neworder->pharmacy_id = $pharmacy_id;
				$neworder->deliveryboy_id = 0;
				$neworder->customer_id = $user_id;
				$neworder->address_id = $address_id;
				$neworder->logistic_user_id = $logistic_id;
				$neworder->order_status = 'new';

				/*$neworder->is_paid = (isset($content->logistic_id) && (Int)($content->logistic_id) > 0) ? 1 : 0;*/
				$neworder->is_external_delivery = $is_external_delivery;
				$neworder->is_intersection = $is_intersection;
				if($neworder->is_external_delivery){
					$neworder->external_delivery_initiatedby = 'customer';
					$neworder->order_status = 'payment_pending';
				}
				$neworder->prescription_id = $prescription_id;
				$neworder->order_type = $order_type;
				$neworder->total_days = $total_days;
				$neworder->reminder_days = $reminder_days;
				$neworder->order_note = $order_note;
				/*$neworder->rejectreason_id = 0;
				$neworder->incompletereason_id = 0;
				$neworder->cancelreason_id = 0;*/
				$neworder->audio = $audio_name;
				$neworder->leave_neighbour = $leaved_with_neighbor;
				$neworder->delivery_charges_id = ($delivery_charges_id)?$delivery_charges_id:'1';
				/*$neworder->receive_date = date('Y-m-d H:i:s');*/
				$neworder->audio_info= date('Y-m-d H:i:s');
				$neworder->create_datetime = date('Y-m-d H:i:s');
				$neworder->created_at = date('Y-m-d H:i:s');
				$neworder->updated_at = date('Y-m-d H:i:s');
				if($neworder->save()){
					$order_data = new_orders::where('id',$neworder->id)->first();
					$pharmacy_name = new_pharmacies::where('id',$neworder->pharmacy_id)->first();

					if($neworder->is_external_delivery == 0){
						if($user_id > 0){
							$ids = array();
							$t_data = new_users::where('id',$neworder->customer_id)->first();
							$sellerdetails = new_pharma_logistic_employee::where(['pharma_logistic_id'=>$order_data->pharmacy_id,'user_type'=>'seller'])->get();
							
							$message = ' Order Create '. $order_data->order_number.' User Name : '. $t_data->name;
							$seller_id = [];
							foreach ($sellerdetails as $sellerdetail) {
								if($sellerdetail->fcm_token!=''){
									$ids[] = $sellerdetail->fcm_token;
									$seller_id[] = $sellerdetail->id;
								}
								$msg = array
								(
									'body'   => ' Order Create '. $order_data->order_number.' User Name : '. $t_data->name ,
									'title'     => 'Order Created'
								);
								
								$notification = new notification_seller();
								$notification->user_id=$sellerdetail->id;
								$notification->order_id=$order_data->id;
								$notification->order_status=$order_data->order_status;
								$notification->subtitle=$msg['body'];
								$notification->title=$msg['title'];
								$notification->created_at=date('Y-m-d H:i:s');
								$notification->save();
							}
							
							/*if (count($ids) > 0) {					
								Helper::sendNotification($ids, $message, 'Order Created', $user->id, 'user', $seller_id, 'seller', $ids);

							}*/
						}
					}

					if(!empty($order_data)){
						$data = [
							'name' => $pharmacy_name->name,
							'orderno'=>$order_data->order_number
						];
						$email = $pharmacy_name->email;
						Mail::send('email.createorder', $data, function ($message) use ($email) {
								$message->to($email)->subject('Pharma - Order Create');
						});
					}

					$update_payment_id = new_orders::where('id',$neworder->id)->first();
					$update_payment_id->payment_order_id = time().$neworder->id;
					$update_payment_id->save();
					$pharmacy_name = new_pharmacies::where('id',$neworder->pharmacy_id)->first();
					$response['data']['payment_order_id'] = $update_payment_id->payment_order_id;
					$response['data']['order_id'] = $update_payment_id->id;
					$response['data']['order_message'] ='Your order '.$update_payment_id->order_number.' has been placed successfully.\n'.$pharmacy_name->name.' will accept your order soon.';
					
					
					$response['status'] = 200;
					$response['message'] = 'Your order successfully submitted';

				} else {
					$response['status'] = 404;
					$response['message'] = 'Something went wrong';
				}			
			}else{
				$response['status'] = 404;
				$response['message'] = 'User not found';
			}

			if($neworder->is_external_delivery){
				$response['data']['payment_url'] = 'create_transaction/'.$neworder->id;
			}

		/*}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}*/
		
        $response = json_encode($response);
		//$cipher  = $encryption->encryptPlainTextWithRandomIV($response, $secretyKey);
		
        return response($response, 200);
	}
	// public function sendNotification($reg_ids, $message, $title) {
		
		// $serverKey = 'AAAAKIqNu8Q:APA91bEJSvjmr9TiUjAtQRc1PosKmb3nqRqQULAFUXHnujLmTw4zLmiSLD27gFffQeqxSR7U75JXUO-V65WIcMKorV7OjZ2boepBanPFwPFnxBEyCp7Uv0OwMVjnhMHp1ib_GtFiEwI8';
		// $body = $message;
		// $notification = array('title' =>$title , 'text' => $body, 'sound' => 'default');
		// $arrayToSend = array('registration_ids' => $reg_ids, 'notification' => $notification,'priority'=>'high');
		// $json = json_encode($arrayToSend);
		
		// $url = 'https://fcm.googleapis.com/fcm/send';
        // $headers = array(
            // 'Authorization: key=AAAAKIqNu8Q:APA91bEJSvjmr9TiUjAtQRc1PosKmb3nqRqQULAFUXHnujLmTw4zLmiSLD27gFffQeqxSR7U75JXUO-V65WIcMKorV7OjZ2boepBanPFwPFnxBEyCp7Uv0OwMVjnhMHp1ib_GtFiEwI8',
            // 'Content-Type: application/json'
        // );

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        // $result = curl_exec($ch);
		
	

        // if ($result === FALSE) {
            // die('Curl failed: ' . curl_error($ch));
        // }
        // curl_close($ch);
          // return $result; 
	// }
   
}	
