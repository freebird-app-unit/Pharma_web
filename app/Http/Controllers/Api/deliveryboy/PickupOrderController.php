<?php

namespace App\Http\Controllers\Api\deliveryboy;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Orders;
use App\Address;
use App\new_address;
use App\delivery_charges;
use App\SellerModel\invoice;
use App\DeliveryboyModel\new_pharma_logistic_employee;
use App\DeliveryboyModel\new_orders;
use App\DeliveryboyModel\new_pharmacies;
use App\SellerModel\new_users;
use App\DeliveryboyModel\new_order_images;
use App\new_logistics;
use App\new_delivery_charges;
use App\SellerModel\Orderassign;
use App\notification_user;
use App\notification_seller;
use Validator;
use Storage;
use Image;
use File;
use DB;
use Mail;
use Helper;

class PickupOrderController extends Controller
{
	public function pickuporderlist(Request $request)
	{
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $search_text = isset($content->search_text) ? $content->search_text : '';

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
        $response['data'] = array();

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->get();
        
		if(count($user)>0){
            $order_list = new_orders::select('new_orders.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number','u2.lat AS pickup_lat','u2.lon AS pickup_lon','u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark', 'ordAssign.order_status AS ordAssign_status','u1add.latitude AS latitude','u1add.longitude AS longitude')
            ->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'assign', 'ordAssign.order_status' => 'accept'])
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
            $order_list = $order_list->orderBy('ordAssign.assign_date', 'DESC')->get();

            if(count($order_list)){
                foreach($order_list as $order) { 
                    $object = (object)[];

                    $object->id = $order->id;
                    $object->order_id = isset($order->order_number)?($order->order_number):'';
                    $object->price = isset($order->order_amount)?($order->order_amount):0;
                    
                    $delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
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
                    $pickup_info->lat = isset($order->pickup_lat)?$order->pickup_lat:'';
                    $pickup_info->lon = isset($order->pickup_lon)?$order->pickup_lon:'';
                    $object->pickup_location = $pickup_info;

                    $delivery_info = (object)[];
                    $delivery_info->name = isset($order->delivery_name)?($order->delivery_name):'';
                    $delivery_info->address = isset($order->delivery_address)?($order->delivery_address):'';
                    $delivery_info->mobile_number = isset($order->delivery_mobile_number)?($order->delivery_mobile_number):'';
                    $delivery_info->locality = isset($order->delivery_locality)?$order->delivery_locality:'';
                    $delivery_info->landmark = isset($order->delivery_landmark)?$order->delivery_landmark:'';
                    $delivery_info->latitude = isset($order->latitude)?(string)$order->latitude:'';
                    $delivery_info->longitude = isset($order->longitude)?(string)$order->longitude:'';
                    $object->delivery_location = $delivery_info;
                    $object->leave_with_neighbour = isset($order->leave_neighbour)?$order->leave_neighbour:'false';
                    array_push($response['data'], $object);
                }
            }
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

   /* public function pickuporderdetail(Request $request)
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
            ->where(['orders.deliveryboy_id' => $user_id, 'orders.order_status' => 'assign', 'ordAssign.order_status' => 'accept', 'orders.id' => $order_id])
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
            $order_list = $order_list->orderBy('orders.id', 'DESC')->get();

            if(count($order_list)){
                foreach($order_list as $order) { 
                    $object = (object)[];

                    $object->id = $order->id;
                    $object->order_id = isset($order->order_number)?($order->order_number):'';
                    $object->price = isset($order->order_amount)?($order->order_amount):0;
                    
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

    public function orderpickup(Request $request)
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
            $order = new_orders::where('id' ,$order_id)->first();
            if(!empty($order)){
                $order->order_status = 'pickup';
                $order->pickup_datetime = date('Y-m-d H:i:s');
                $order->return_confirmtime = NULL;
                $order->updated_at = date('Y-m-d H:i:s');

                $destinationPath = 'storage/app/public/uploads/pickup/'; 
                        if($files=$request->file('images')){
                            foreach($files as $key => $file){
                                $filename = time().'-'.$file->getClientOriginalName();
                                $tesw = $file->move($destinationPath, $filename);
                                $pickup_data = new new_order_images();
                                $pickup_data->order_id = $order_id;
                                $pickup_data->image_type = 'pickup';
                                $pickup_data->image_name = $filename;
                                $pickup_data->created_at = date('Y-m-d H:i:s');
                                $pickup_data->updated_at = date('Y-m-d H:i:s');
                                $pickup_data->save();
                            }
                        }
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
                                'body'   => '  Order number '. $order_data->order_number,
                                'title'     => 'Your Order Is Out For Delivery'
                            );
                            // if(count($ids)>0){
                                // $fields = array(
                                    // 'to' => $customerdetail->fcm_token,
                                    // 'notification' => $msg
                                // );
                                // $this->sendPushNotification($fields);   
                            // }
                            if (count($ids) > 0) {                  
                                Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Your Order is out for delivery', $user->id, 'delivery_boy', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                            }
                            $notification = new notification_user();
                            $notification->user_id=$customerdetail->id;
                            $notification->order_id=$order_data->id;
                            $notification->subtitle=$msg['body'];
                            $notification->title=$msg['title'];
                            $notification->created_at=date('Y-m-d H:i:s');
                            $notification->save();
                            
                            $sids = [];
                            $seller_id = [];
                            $sellerdetail = new_pharma_logistic_employee::where('id',$order_data->process_user_id)->first();
                            $deliveryboydetail = new_pharma_logistic_employee::where('id',$order_data->deliveryboy_id)->first();
                            if($sellerdetail->fcm_token!=''){
                                $ids[] = $sellerdetail->fcm_token;
                                $sids[] = $sellerdetail->fcm_token;
                                $seller_id[] = $sellerdetail->id;
                            }
                            $msg = array
                            (
                                'body'   => ' Order Out For Delivery '. $order_data->order_number .' From '.$deliveryboydetail->name,
                                'title'     => ' Order Out For Delivery'
                            );
                            // if(count($ids)>0){
                                // $fields = array(
                                    // 'to' => $sellerdetail->fcm_token,
                                    // 'notification' => $msg
                                // );
                                // $this->sendPushNotificationSeller($fields);   
                            // }
                            if (count($sids) > 0) {                 
                                Helper::sendNotification($sids, 'Order Out For Delivery '. $order_data->order_number .' From '.$deliveryboydetail->name, 'Order Out For Delivery', $user->id, 'delivery_boy', $seller_id, 'seller', $sids);
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
                                    $result = Mail::send('email.pickup', $data, function ($message) use ($email) {
                                            $message->to($email)->subject('Pharma - Order Out For Delivery');
                                    });  
                        }else{
                                $logistic_data= new_logistics::where('id',$order_data->logistic_user_id)->first();
                                if(!empty($logistic_data)){
                                    $data = [
                                    'name' => $logistic_data->name,
                                    'orderno'=>$order_data->order_number
                                    ];
                                    $email = $logistic_data->email;
                                    $result = Mail::send('email.pickup', $data, function ($message) use ($email) {
                                            $message->to($email)->subject('Pharma - Order Out For Delivery');
                                    });  
                                }
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