<?php

namespace App\Http\Controllers\Api\deliveryboy;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\Orders;
use App\Address;
use App\delivery_charges;
use App\Incompletereason;
use App\SellerModel\invoice;
use App\DeliveryboyModel\new_pharma_logistic_employee;
use App\DeliveryboyModel\new_orders;
use App\DeliveryboyModel\new_pharmacies;
use App\SellerModel\new_users;
use App\DeliveryboyModel\new_order_images;
use App\DeliveryboyModel\new_order_history;
use App\new_delivery_charges;
use App\new_logistics;
use App\SellerModel\Orderassign;
use App\notification_user;
use App\notification_seller;
use App\notification_deliveryboy;
use Validator;
use Storage;
use Image;
use File;
use DB;
use Mail;
use DateTime;
use DatePeriod;
use DateInterval;
use Helper;

class DeliveryOrderController extends Controller
{
	public function deliveryorderlist(Request $request)
	{
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $search_text = isset($content->search_text) ? $content->search_text : '';
        $page = isset($content->page) ? $content->page : '';

        $params = [
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
        $response['message'] = '';
        $response['data']['currentPageIndex'] = '';
        $response['data']['totalPage']='';
        $response['data']['content'] = array();

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->get();
        
		if(count($user)>0){
            $order_list = new_orders::select('new_orders.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number','u2.lat AS pickup_lat','u2.lon AS pickup_lon', 'u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark','u1add.latitude AS latitude','u1add.longitude AS longitude', 'ordAssign.order_status AS ordAssign_status')
            ->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'pickup', 'ordAssign.order_status' => 'accept'])
            ->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')
            ->leftJoin('order_assign as ordAssign', function($join) {
                $join->on('ordAssign.order_id', '=', 'new_orders.id')
                ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
            })
            ->leftJoin('address_new as u1add', 'new_orders.address_id', '=', 'u1add.id')
            ->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_orders.pharmacy_id');
            if($search_text !== ''){
                $order_list = $order_list->where('new_orders.id', 'like', $searchtxt.'%');
            }
            $order_list = $order_list->orderBy('new_orders.pickup_datetime', 'DESC');

            $total = $order_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']['currentPageIndex'] = $page;
            $response['data']['totalPage'] = ceil($total/$per_page);
            $orders = $order_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 


            if(count($data_array)){
                foreach($data_array as $order) { 
                    $object = (object)[];

                    $object->id = $order['id'];
                    $object->order_id = isset($order['order_number'])?($order['order_number']):'';
                    $object->price = isset($order['order_amount'])?($order['order_amount']):0;
                    $object->remain_time = '3600000';
                   
                    $delivery_type = new_delivery_charges::where('id', $order['delivery_charges_id'])->value('delivery_type');
                    $object->delivery_type = isset($delivery_type)?$delivery_type:'';
                    
                    $object->invoice = array();
                    $invoice_data = invoice::where('order_id', $order['id'])->get();

                    if(count($invoice_data)>0){
                        $invoice_images=[];
                        foreach ($invoice_data as $invoice) {
                                $invoice_image = '';
                                if (!empty($invoice->invoice)) {
                                    $filename = storage_path('app/public/uploads/invoice/' .  $invoice->invoice);
                                    if (File::exists($filename)) {
                                        $invoice_image = asset('storage/app/public/uploads/invoice/' .  $invoice->invoice);
                                        array_push($invoice_images, $invoice_image);
                                    } 
                                }
                        }
                        $object->invoice = $invoice_images;
                    }

                    $pickup_info = (object)[];
                    $pickup_info->name = isset($order['pickup_name'])?($order['pickup_name']):'';
                    $pickup_info->address = isset($order['pickup_address'])?($order['pickup_address']):'';
                    $pickup_info->mobile_number = isset($order['pickup_mobile_number'])?($order['pickup_mobile_number']):'';
                    $pickup_info->locality = isset($order['pickup_locality'])?$order['pickup_locality']:'';
                    $pickup_info->landmark = isset($order['pickup_landmark'])?$order['pickup_landmark']:'';
                    $pickup_info->lat = isset($order['pickup_lat'])?$order['pickup_lat']:'';
                    $pickup_info->lon = isset($order['pickup_lon'])?$order['pickup_lon']:'';
                    $object->pickup_location = $pickup_info;

                    $delivery_info = (object)[];
                    $delivery_info->name = isset($order['delivery_name'])?($order['delivery_name']):'';
                    $delivery_info->address = isset($order['delivery_address'])?($order['delivery_address']):'';
                    $delivery_info->mobile_number = isset($order['delivery_mobile_number'])?($order['delivery_mobile_number']):'';
                    $delivery_info->locality = isset($order['delivery_locality'])?$order['delivery_locality']:'';
                    $delivery_info->landmark = isset($order['delivery_landmark'])?$order['delivery_landmark']:'';
                    $delivery_info->latitude = isset($order['latitude'])?(string)$order['latitude']:'';
                    $delivery_info->longitude = isset($order['longitude'])?(string)$order['longitude']:'';
                    $object->delivery_location = $delivery_info;
                    $object->leave_with_neighbour = isset($order['leaved_with_neighbor'])?$order['leaved_with_neighbor']:'false';
                    array_push($response['data']['content'], $object);
                }
            }
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

    /*public function deliveryorderdetail(Request $request)
	{
        $response = array();
		$response['status'] = 200;
		$response['message'] = '';
        $response['data'] = (object)array();
        
        $data = $request->input('data');
        $content = json_decode($data);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $search_text = isset($content->search_text) ? $content->search_text : '';

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
        $user = User::where(['id'=>$user_id, 'api_token'=>$token, 'user_type'=>'delivery_boy'])->get();

        if(count($user)>0){
            $order_list = Orders::select('orders.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number', 'u1.name AS delivery_name', 'u1.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.landmark AS delivery_landmark', 'ordAssign.order_status AS ordAssign_status')
            ->where(['orders.deliveryboy_id' => $user_id, 'orders.order_status' => 'pickup', 'ordAssign.order_status' => 'accept', 'orders.id' => $order_id])
            ->leftJoin('users as u1', 'u1.id', '=', 'orders.customer_id')
            ->leftJoin('order_assign as ordAssign', function($join) {
                $join->on('ordAssign.order_id', '=', 'orders.id')
                ->on('ordAssign.deliveryboy_id', '=', 'orders.deliveryboy_id');
            })
            ->leftJoin('address as u1add', 'orders.address_id', '=', 'u1add.id')
            ->leftJoin('users as u2', 'u2.id', '=', 'orders.pharmacy_id');
            if($search_text !== ''){
                $order_list = $order_list->Where('orders.id', 'like', $searchtxt.'%');
            }
            $order_list = $order_list->orderBy('orders.deliver_date', 'DESC')->get();

            if(count($order_list)){
                foreach($order_list as $order) { 
                    $object = (object)[];

                    $object->order_id = $order->id;
                    $object->order_id = isset($order->order_number)?($order->order_number):'';
                    $object->remain_time = '3600000';
                    $delivery_type = delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                    $object->delivery_type = isset($delivery_type)?$delivery_type:'';
                    
                    $object->invoice = array();
                    $invoice_data = invoice::where('order_id', $order->id)->get();

                    if(count($invoice_data)>0){
                        $invoice_images=[];
                        foreach ($invoice_data as $invoice) {
                                $invoice_image = '';
                                if (!empty($invoice->invoice)) {
                                    $filename = storage_path('app/public/uploads/invoice/' .  $invoice->invoice);
                                    if (File::exists($filename)) {
                                        $invoice_image = asset('storage/app/public/uploads/invoice/' .  $invoice->invoice);
                                        array_push($invoice_images, $invoice_image);
                                    } 
                                }
                        }
                        $object->invoice = $invoice_images;
                    }

                    $pickup_info = (object)[];
                    $pickup_info->name = isset($order->pickup_name)?($order->pickup_name):'';
                    $pickup_info->address = isset($order->pickup_address)?($order->pickup_address):'';
                    $pickup_info->mobile_number = isset($order->pickup_mobile_number)?($order->pickup_mobile_number):'';
                    $pickup_info->locality = isset($order->pickup_locality)?$order->pickup_locality:'';
                    $pickup_info->landmark = isset($order->pickup_landmark)?$order->pickup_landmark:'';
                    $object->pickup_location = $pickup_info;

                    $delivery_info = (object)[];
                    $delivery_info->name = isset($order->delivery_name)?($order->delivery_name):'';
                    $delivery_info->address = isset($order->delivery_address)?($order->delivery_address):'';
                    $delivery_info->mobile_number = isset($order->delivery_mobile_number)?($order->delivery_mobile_number):'';
                    $delivery_info->locality = isset($order->delivery_locality)?$order->delivery_locality:'';
                    $delivery_info->landmark = isset($order->delivery_landmark)?$order->delivery_landmark:'';
                    $object->delivery_location = $delivery_info;

                    $object->pickup_images = [];
                    if (!empty($order->pickup_image)) {
                        $splitImage = explode(', ', $order->pickup_image);
                        $object->pickup_images = array();

                        foreach($splitImage as $key => $file){
                            $filename = storage_path('app/public/uploads/users/' . $file);
                            
                            if (File::exists($filename)) {
                                array_push($object->pickup_images, asset('storage/app/public/uploads/users/' . $filename));
                            }
                        }
                    }

                    $object->order_receive = isset($order->receive_date)?($order->receive_date):'';
                    $object->order_accept = isset($order->accept_date)?($order->accept_date):'';
                    $object->order_assign = isset($order->assign_date)?($order->assign_date):'';
                    $object->pickup_date = isset($order->pickup_date)?($order->pickup_date):'';
                    $object->leave_with_neighbour = isset($order->leaved_with_neighbor)?$order->leaved_with_neighbor:'false';
                    $response['data'] = $object;
                }
            }
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return response($response, 200);
    }*/

    public function orderdelivered(Request $request)
	{
	    $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

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
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
		$response['message'] = '';
        $response['data'] = (object)array();

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->first();

        if(!empty($user)){
            $order = new_orders::where('id',$order_id)->first();
            if(!empty($order)){
                    $order->order_status = 'complete';
                    $order->neighbour_info = isset($content->neighbour_name)?($content->neighbour_name):($order->neighbour_info);
                    $order->deliver_datetime = date('Y-m-d H:i:s');
                    $order->updated_at = date('Y-m-d H:i:s');
                    $order->leave_neighbour = isset($content->leaved_with_neighbor)?($content->leaved_with_neighbor):($order->leave_neighbour);
                    $destinationPath = 'storage/app/public/uploads/deliver/'; 
					if($files=$request->file('images')){
						foreach($files as $key => $file){
							$filename = time().'-'.$file->getClientOriginalName();
							$tesw = $file->move($destinationPath, $filename);
							$deliver_data = new new_order_images();
							$deliver_data->order_id = $order_id;
							$deliver_data->image_type = 'deliver';
							$deliver_data->image_name = $filename;
							$deliver_data->created_at = date('Y-m-d H:i:s');
							$deliver_data->updated_at = date('Y-m-d H:i:s');
							$deliver_data->save();
						}
					}
                    if($order->save()){
                         if($user_id > 0){
                            $ids = array();
                            $order_data = new_orders::where('id',$order_id)->first();
                            $customerdetail =  new_users::where('id',$order_data->customer_id)->first();
                                if($customerdetail->fcm_token!=''){
                                    $ids[] = $customerdetail->fcm_token;
                                }
                             $seller_name =  new_pharma_logistic_employee::where('id',$user_id)->first();
                             $msg = array
                            (
                                'body'   => ' Order number '. $order_data->order_number,
                                'title'     => 'Your order is delivered'
                            );
                            // if(count($ids)>0){
                                // $fields = array(
                                    // 'to' => $customerdetail->fcm_token,
                                    // 'notification' => $msg
                                // );
                                // $this->sendPushNotification($fields);   
                            // }
                            
                            if (count($ids) > 0) {   
                                
                                Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Your Order is delivered', $user->id, 'delivery_boy', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                            }
                        
                            $notification = new notification_user();
                            $notification->user_id=$customerdetail->id;
                            $notification->order_id=$order_data->id;
                            $notification->subtitle=$msg['body'];
                            $notification->title=$msg['title'];
                            $notification->created_at=date('Y-m-d H:i:s');
                            $notification->save();
                            
                            $sellerdetail = new_pharma_logistic_employee::where('id',$order_data->process_user_id)->first();
                            $deliveryboydetail = new_pharma_logistic_employee::where('id',$order_data->deliveryboy_id)->first();
                            $sids = [];
                            $seller_id = [];
                            if($sellerdetail->fcm_token!=''){
                                $ids[] = $sellerdetail->fcm_token;
                                $sids[] = $sellerdetail->fcm_token;
                                $seller_id[] = $sellerdetail->id;
                            }
                            $msg = array
                            (
                                'body'   => ' Order Delivered '. $order_data->order_number .' From '.$deliveryboydetail->name,
                                'title'     => 'Order Delivered'
                            );
                            // if(count($ids)>0){
                                // $fields = array(
                                    // 'to' => $sellerdetail->fcm_token,
                                    // 'notification' => $msg
                                // );
                                // $this->sendPushNotificationSeller($fields);   
                            // }
                            
                            if (count($sids) > 0) {                 
                                Helper::sendNotification($sids, 'Order Delivered '. $order_data->order_number .' from '.$deliveryboydetail->name, 'Order Delivered', $user->id, 'delivery_boy', $seller_id, 'seller', $sids);
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
                                $result = Mail::send('email.deliver', $data, function ($message) use ($email) {
                                        $message->to($email)->subject('Pharma - Order Deliver');
                                });   
                            }else{
                                 $logistic_data = new_logistics::where('id',$order_data->logistic_user_id)->first();
                                $data = [
                                    'name' => $logistic_data->name,
                                    'orderno'=>$order_data->order_number
                                ];
                                $email = $logistic_data->email;
                                $result = Mail::send('email.deliver', $data, function ($message) use ($email) {
                                        $message->to($email)->subject('Pharma - Order Deliver');
                                }); 
                            }
                        }  
                    }
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
                    $notify= notification_user::where('order_id',$order->id)->orderBy('id','desc')->first();
                    $notify->order_id=$order_history->id;
                    $notify->save();
                    $order_delete = new_orders::find($order->id);
                    $order_delete->delete();
                $response['status'] = 200;
                $response['message'] = 'Order Delivered';
            }else{
                $response['status'] = 404;
                $response['message'] = 'This order was already cancelled';
            }
         } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

    public function orderreturn(Request $request)
	{
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $rejectreason = isset($content->rejectreason) ? $content->rejectreason : '';

        $params = [
			'user_id' => $user_id,
            'order_id' => $order_id,
            'rejectreason' => $rejectreason
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'rejectreason' => 'required'
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
		$response['message'] = '';
        $response['data'] = (object)array();

        $token = $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->first();

        if(!empty($user)){
            
            $order = new_orders::where(['id' => $order_id, 'order_status' => 'pickup'])->first();
            if(!empty($order)){
                $order->order_status = 'incomplete';
                $order->reject_cancel_reason = $content->rejectreason;
                $order->reject_datetime = date('Y-m-d H:i:s');
                $order->updated_at = date('Y-m-d H:i:s');
                if($order->save()){
                    if($user_id > 0){
                $ids = array();
                $order_data = new_orders::where('id',$order_id)->first();
                if(!empty($order_data)){
                    $customerdetail =  new_users::where('id',$order_data->customer_id)->first();
                    if($customerdetail->fcm_token!=''){
                        $ids[] = $customerdetail->fcm_token;
                    }
                    $seller_name =  new_pharma_logistic_employee::where('id',$user_id)->first();
                    $msg = array
                    (
                        'body'   => ' Order number '. $order_data->order_number,
                        'title'     => 'Your Order Returned'
                    );
                    // if(count($ids)>0){
                        // $fields = array(
                            // 'to' => $customerdetail->fcm_token,
                            // 'notification' => $msg
                        // );
                        // $this->sendPushNotification($fields);   
                    // }
                    
                    if (count($ids) > 0) {                  
                        Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Your Order returned', $user->id, 'delivery_boy', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                    }
                    
                    $notification = new notification_user();
                    $notification->user_id=$customerdetail->id;
                    $notification->order_id=$order_data->id;
                    $notification->subtitle=$msg['body'];
                    $notification->title=$msg['title'];
                    $notification->created_at=date('Y-m-d H:i:s');
                    $notification->save();

                    $sellerdetail = new_pharma_logistic_employee::where('id',$order_data->process_user_id)->first();
                    $sids = [];
                    $seller_id = [];
                    if($sellerdetail->fcm_token!=''){
                        $ids[] = $sellerdetail->fcm_token;
                        $sids[] = $sellerdetail->fcm_token;
                        $seller_id[] = $sellerdetail->id;
                    }
                    $msg = array
                    (
                        'body'   => ' Order Returned '. $order_data->order_number,
                        'title'     => 'Order Returned'
                    );
                    // if(count($ids)>0){
                        // $fields = array(
                            // 'to' => $sellerdetail->fcm_token,
                            // 'notification' => $msg
                        // );
                        // $this->sendPushNotificationSeller($fields);   
                    // }
                    if (count($sids) > 0) {                 
                        Helper::sendNotification($sids, 'Order Returned '. $order_data->order_number, 'Order returned', $user->id, 'delivery_boy', $seller_id, 'seller', $sids);
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
            }

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
                    $result = Mail::send('email.return', $data, function ($message) use ($email) {
                            $message->to($email)->subject('Pharma - Order Return');
                    }); 
                }else{
                    $logistic_data = new_logistics::where('id',$order_data->logistic_user_id)->first();
                    $data = [
                        'name' => $logistic_data->name,
                        'orderno'=>$order_data->order_number
                    ];
                    $email = $logistic_data->email;
                    $result = Mail::send('email.return', $data, function ($message) use ($email) {
                            $message->to($email)->subject('Pharma - Order Return');
                    }); 
                }
            }
                }
            }else{
				$response['status'] = 404;
				$response['message'] = 'This order was already cancelled';
            }
           
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
    public function getincompletereasons(Request $request)
	{
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
        $user_id = isset($content->user_id) ? $content->user_id : '';

        $params = [
			'user_id' => $user_id,
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
		$response['message'] = '';
        $response['data'] = (object)array();

        $token = $request->bearerToken();
        $user = User::where(['id'=>$user_id, 'api_token'=>$token, 'user_type'=>'delivery_boy'])->get();

        if(count($user)>0){
            $object = Incompletereason::all();
            $response['data'] = $object;
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

    public function notification_deliveryboy(Request $request)
    {
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
        $user_id = isset($content->user_id) ? $content->user_id : '';
        $page = isset($content->page) ? $content->page : '';
        $params = [
            'user_id' => $user_id,
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
        $notification = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $token = $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->first();

        if(!empty($user)){

        $notification_data = notification_deliveryboy::select('id','user_id','title','subtitle','order_id','created_at')->where('user_id',$user_id)->orderBy('id','DESC');

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
                $response['message'] = 'Notification For Deliveryboy';
            }else{
                $response['status'] = 404;
                $response['message'] = 'Notification For Deliveryboy';
            }
            $response['data']->content = $notification;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthenticated';
        }
        
        return decode_string($response, 200);
    }
    public function clearallnotification(Request $request)
	{
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
		
		$user_id = isset($content->user_id) ? $content->user_id : '';
		
		$params = [
			'user_id' => $user_id
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required'
        ]);
 
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
        $response['status'] = 200;
		$response['message'] = '';
        $response['data'] = (object)array();

		$token =  $request->bearerToken();
		$user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->first();
		if(!empty($user)){
		$notification_arr = [];
		
		$notification = notification_deliveryboy::where('user_id','=',$user_id)->delete();
		
		$response['status'] = 200;
		$response['message'] = 'Notification cleared';
		$response['data'] = $notification_arr;
		}else{
	    		$response['status'] = 401;
	            $response['message'] = 'Unauthenticated';
	   	}
        
        return decode_string($response, 200);
	
	}
    public function sendPushNotification($fields) {
        //firebase server url to send the curl request
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=AAAAl25oxFs:APA91bG5CBSlEjVS_42u4Kt3JIZZYmWbfEb-ZjfQtXbgqLzZZbWcmmkvxrsroWxNN9JNuNdcBGwNAUzPZx14wp1B9UjQS_Js-YDbFrCLBRZCtm9RmAGrd8-RpJRV7S8TR0V_E3Tf98_c',
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
}