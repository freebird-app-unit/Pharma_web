<?php

namespace App\Http\Controllers\Api\deliveryboy;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Orders;
use App\Orderassign;
use App\Address;
use App\new_address;
use App\delivery_charges;
use App\SellerModel\invoice;
use App\DeliveryboyModel\new_pharma_logistic_employee;
use App\DeliveryboyModel\new_orders;
use App\DeliveryboyModel\new_pharmacies;
use App\DeliveryboyModel\new_users;
use App\DeliveryboyModel\new_order_images;
use App\new_delivery_charges;
use App\notification_seller;
use Validator;
use Storage;
use Image;
use File;
use DB;
use Helper;

class UpcomingOrderController extends Controller
{
	public function upcomingorderlist(Request $request)
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->first();
        
		if(!empty($user)){
            $order_list = new_orders::select('new_orders.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number','u2.lat AS pickup_lat','u2.lon AS pickup_lon','u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark','u1add.latitude AS latitude','u1add.longitude AS longitude')
            ->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'assign', 'ordAssign.order_status' => 'assign', 'delivery_boy.is_available' => 1])
            ->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')
            ->leftJoin('new_pharma_logistic_employee as delivery_boy', 'delivery_boy.id', '=', 'new_orders.deliveryboy_id')
            ->leftJoin('order_assign as ordAssign', function($join) {
                $join->on('ordAssign.order_id', '=', 'new_orders.id')
                ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
            })
            ->leftJoin('address_new as u1add', 'new_orders.address_id', '=', 'u1add.id')
            ->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_orders.pharmacy_id');

            if($search_text !== ''){
                $order_list = $order_list->where('new_orders.id', 'like', $search_text.'%');
            } else {
                $order_list = $order_list->orderBy('pickup_datetime', 'DESC');
            }

            $order_list = $order_list->orderBy('new_orders.assign_datetime', 'DESC');

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

            if(count($data_array)>0){
                foreach($data_array as $order) { 
                    $object = (object)[];

                    $object->id = $order['id'];
                    $object->order_id = isset($order['order_number'])?($order['order_number']):'';
                    $object->price = (string)isset($order['order_amount'])?($order['order_amount']):0;
                    
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
                    $object->leave_with_neighbour = isset($order['leave_neighbour'])?$order['leave_neighbour']:'false';
                    array_push($response['data']['content'], $object);
                }
            }
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

    /*public function upcomingorderdetail(Request $request)
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
            $order_list = Orders::select('orders.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number', 'u1.name AS delivery_name', 'u1.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.landmark AS delivery_landmark')
            ->where(['orders.deliveryboy_id' => $user_id, 'orders.order_status' => 'assign', 'orders.id' => $order_id])
            ->leftJoin('users as u1', 'u1.id', '=', 'orders.customer_id')
            ->leftJoin('address_new as u1add', 'orders.address_id', '=', 'u1add.id')
            ->leftJoin('users as u2', 'u2.id', '=', 'orders.pharmacy_id');
            $order_list = $order_list->orderBy('orders.id', 'DESC')->get();

            if(count($order_list)){
                foreach($order_list as $order) { 
                    $object = (object)[];

                    $object->id = $order->id;
                    $object->order_id = isset($order->order_number)?($order->order_number):'';
                    $object->price = (string)isset($order->order_amount)?($order->order_amount):0;
                   
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

                    $object->order_receive = isset($order->receive_date)?($order->receive_date):'';
                    $object->order_accept = isset($order->accept_date)?($order->accept_date):'';
                    $object->order_assign = isset($order->assign_date)?($order->assign_date):'';
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

    public function orderaccept(Request $request)
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
            $orderAssign = Orderassign::where(['deliveryboy_id' => $user_id, 'order_id' => $order_id, 'order_status' => 'assign'])->first();
            if(!empty($orderAssign)){
                $orderAssign->order_status = 'accept';
                $orderAssign->accept_date = date('Y-m-d H:i:s');
                $orderAssign->reject_date = date('Y-m-d H:i:s');
                $orderAssign->updated_at = date('Y-m-d H:i:s');
                if($orderAssign->save()){
                     if($user_id > 0){
                        $ids = array();
                        $seller_id = array();
                        $order_data = new_orders::where('id',$order_id)->first();
                        $sellerdetail = new_pharma_logistic_employee::where('id',$order_data->process_user_id)->first();
                        $deliveryboydetail = new_pharma_logistic_employee::where('id',$order_data->deliveryboy_id)->first();
                        if($sellerdetail->fcm_token!=''){
                            $ids[] = $sellerdetail->fcm_token;
                            $seller_id[] = $sellerdetail->id;
                        }
                        $msg = array
                        (
                            'body'   => ' Order Accepted '. $order_data->order_number .' From '.$deliveryboydetail->name,
                            'title'     => ' Order Accepted'
                        );
                        // if(count($ids)>0){
                            // $fields = array(
                                // 'to' => $sellerdetail->fcm_token,
                                // 'notification' => $msg
                            // );
                            // $this->sendPushNotification($fields);    
                        // }
                        if (count($ids) > 0) {                  
                            Helper::sendNotification($ids, 'Order Accepted '. $order_data->order_number .' From '.$deliveryboydetail->name, 'Order Accepted', $user->id, 'delivery_boy', $seller_id, 'seller', $ids);
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

    public function orderreject(Request $request)
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

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->first();

        if(!empty($user)){
           
            $order = new_orders::find($order_id);
            if(!empty($order)){
                $order->order_status = 'reject';
                $order->reject_user_id = $user_id;
                $order->rejectby_user = 'deliveryboy';
                $order->reject_cancel_reason = $rejectreason;
                $order->reject_datetime = date('Y-m-d H:i:s');
                if($order->save()){
                     if($user_id > 0){
                        $ids = array();
                        $seller_id = array();
                        $order_data = new_orders::where('id',$order_id)->first();
                        $sellerdetail = new_pharma_logistic_employee::where('id',$order_data->process_user_id)->first();
                        $deliveryboydetail = new_pharma_logistic_employee::where('id',$order_data->deliveryboy_id)->first();
                        if($sellerdetail->fcm_token!=''){
                            $ids[] = $sellerdetail->fcm_token;
                            $seller_id[] = $sellerdetail->id;
                        }
                        $msg = array
                        (
                            'body'   => ' Order Rejected '. $order_data->order_number .' From '.$deliveryboydetail->name,
                            'title'     => 'Order Rejected'
                        );
                        // if(count($ids)>0){
                            // $fields = array(
                                // 'to' => $sellerdetail->fcm_token,
                                // 'notification' => $msg
                            // );
                            // $this->sendPushNotification($fields);   
                        // }
                        if (count($ids) > 0) {                  
                            Helper::sendNotification($ids, 'Order Rejected '. $order_data->order_number .' From '.$deliveryboydetail->name, 'Order Rejected', $user->id, 'delivery_boy', $seller_id, 'seller', $ids);
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
                $response['status'] = 200;
                $response['message'] = 'Order Rejected';
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
     public function sendPushNotification($fields) {
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