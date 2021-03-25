<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\new_users;
use Auth;
use DB;
use App\SellerModel\Prescription;
use App\new_pharmacies;
use App\new_logistics;
use App\new_delivery_charges;
use App\new_orders;
use App\new_address;
use Helper;
use App\new_pharma_logistic_employee;
use App\notification_seller;
use Mail;
use App\Events\CreateNewOrder;

class Createordercontroller extends Controller
{
	public function getprescriptionList(Request $request)
    {
          $prescriptions = DB::table("prescription")
          ->where("user_id",$request->user_id)
          ->pluck("name","id");
           return response()->json($prescriptions);
    }
    public function getaddressList(Request $request)
    {
          $address = DB::table("address_new")
          ->where("user_id",$request->user_id)
          ->pluck("address","id");
           return response()->json($address);
    }
   public function create()
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Create order';
		$data['page_condition'] = 'page_createorder';
		$data['users'] = new_users::where(['is_active'=>'1','is_verify'=>'1'])->get();
		$data['pharmacies'] = new_pharmacies::where(['is_active'=>'1','is_available'=>'1','is_approve'=>'1'])->get();
		$data['logistics'] = new_logistics::where(['id'=>'29'])->first();
		$data['delivery_charges'] = new_delivery_charges::where(['is_user'=>'1'])->get();
		$data['site_title'] = 'Create order | ' . $this->data['site_title'];
		return view('createorder.create', array_merge($this->data, $data));
	}

	public function store(Request $request){
		$validate = $request->validate([
			'user_id' => 'required',
			'prescription_id' => 'required',
			'address_id' => 'required',
			'pharmacy_id' => 'required',
			'freepaid' => 'required',
			'leaved_with_neighbor' => 'required',
			'ordertype' => 'required',
		]);

		if($validate){
			$order_data = new new_orders();
			$order_data->customer_id=$request->user_id;
			$order_data->prescription_id=$request->prescription_id;
			$order_data->address_id=$request->address_id;

			$order_data->pharmacy_id=$request->pharmacy_id;
			if($request->freepaid == "free"){
				$order_data->is_external_delivery=0;
				$order_data->logistic_user_id=0;
				$order_data->delivery_charges_id=1;
				$order_data->external_delivery_initiatedby='customer';
			}else{
				$order_data->is_external_delivery=1;
				$order_data->logistic_user_id=29;
				$order_data->delivery_charges_id=$request->delivery_charges_id;
				$order_data->external_delivery_initiatedby='customer';
			}
			$order_data->create_datetime=date('Y-m-d H:i:s');
			$order_data->audio_info=date('Y-m-d H:i:s');
			$order_data->created_at = date('Y-m-d H:i:s');
			$order_data->updated_at = date('Y-m-d H:i:s');
			$order_data->order_status = 'new';
			$order_data->process_user_type ='';
			$order_data->process_user_id = 0;
			$order_data->deliveryboy_id = 0;
			$order_data->leave_neighbour=$request->leaved_with_neighbor;
			if($request->order_type == "full_order"){
				$order_data->order_type=$request->ordertype;
				$order_data->total_days=$request->total_days;
			}elseif ($request->order_type == "selected_item") {
				$order_data->order_type=$request->ordertype;
				$order_data->order_note=$request->order_note;
			}else{
				$order_data->order_type=$request->ordertype;
			}
			$order_data->is_intersection = 0;
			$order_data->save();
			$string = 'PHAR'.$order_data->id; 
			$order = new_orders::where('id',$order_data->id)->first();
			$order->order_number=$string;
			$order->payment_order_id = time().$order_data->id;
			$user = new_users::where(['id'=>$request->user_id])->first();
			if($order->save()){
					if($request->user_id > 0){
						$ids = array();
						$order_data = new_orders::where('id',$order->id)->first();
						$t_data = new_users::where('id',$order->customer_id)->first();
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
							Helper::sendNotification($ids, $message, 'Order Created', $user->id, 'user', $seller_id, 'seller', $ids);

						}
					}
				$order_data = new_orders::where('id',$order->id)->first();
				if(!empty($order_data)){
					$email_data = new_pharmacies::where('id',$order->pharmacy_id)->first();
		            $data = [
		                'name' => $email_data->name,
		                'orderno'=>$order_data->order_number
		            ];
		            $email = $email_data->email;
		            $result = Mail::send('email.createorder', $data, function ($message) use ($email) {
		                    $message->to($email)->subject('Pharma - Order Create');
		            });
				}
			}
			$neworder=new_orders::where('id',$order->id)->first();
			$prescriptions = Prescription::where('id',$neworder->prescription_id)->first();
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
			$newOrderEmit->order_time = $neworder->create_datetime;
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
		
			return redirect(route('createorder.create'))->with('success_message', trans('Added Successfully'));
		}
	}
}
