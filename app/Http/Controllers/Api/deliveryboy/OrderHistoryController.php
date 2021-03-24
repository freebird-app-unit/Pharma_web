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
use App\DeliveryboyModel\new_order_history;
use App\new_delivery_charges;
use Validator;
use Storage;
use Image;
use File;
use DB;
use DateTime;
use DatePeriod;
use DateInterval;
class OrderHistoryController extends Controller
{
    public function orderhistorylist(Request $request){
		
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_status = isset($content->order_status) ? $content->order_status : '';
        $page = isset($content->page) ? $content->page : '';
        $is_completed = isset($content->is_completed) ? $content->is_completed : '';
        $pharmacy_id = isset($content->pharmacy_id) ? $content->pharmacy_id : '';
        $params = [
			'order_status' => $order_status
		];
		
		$validator = Validator::make($params, [
            'order_status' => 'required',
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
		$response['message'] = '';
       /* $response['data'] = array();*/
       $response['data']['currentPageIndex'] = '';
       $response['data']['totalPage']='';
        $response['data']['content'] = array();
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->get();
       
        if(count($user)>0){
            if($is_completed == 1){
                    $order_list = new_order_history::select('new_order_history.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number','u2.lat AS pickup_lat','u2.lon AS pickup_lon', 'u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark','u1add.latitude AS latitude','u1add.longitude AS longitude')
                    ->leftJoin('new_users as u1', 'u1.id', '=', 'new_order_history.customer_id')
                    ->leftJoin('address_new as u1add', 'new_order_history.address_id', '=', 'u1add.id')
                    ->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_order_history.pharmacy_id')
                    ->where(['new_order_history.deliveryboy_id' => $user_id]);

                    if(isset($content->search_text)){
                        $searchtxt = $content->search_text;
                        $order_list = $order_list->where(function($query) use($searchtxt){
                            $query->where('new_order_history.id', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1add.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.name', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1.name', 'like', '%'.$searchtxt.'%');
                        });
                    }

                    if(isset($content->from_date) && isset($content->to_date)){
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');

                        $order_list = $order_list->where(function($query) use ($from, $to){
                            $query->whereBetween('new_order_history.deliver_datetime', [$from, $to]);
                            $query->orWhereBetween('new_order_history.reject_datetime', [$from, $to]);
                        });
                    }

                    if(!empty($pharmacy_id)){
                        $order_list = $order_list->where('new_order_history.pharmacy_id', '=', $pharmacy_id);
                    }

                    switch ($order_status) {
                        case 'complete':
                            $order_list = $order_list->where(['new_order_history.order_status' => 'complete']);
                            break;
                        default:
                            $order_list = $order_list->where(function($query){
                                $query->where('new_order_history.order_status', '=', 'complete');
                            });
                            break;
                    }

                 $order_list = $order_list->orderBy('new_order_history.id', 'DESC');
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
                    $orders_data1 = $order_list->paginate($per_page,'','',$page);
                    $data_array = $orders_data1->toArray();
                    $data_array = $data_array['data'];
            }elseif ($is_completed == 0) {
                    $order_list = new_orders::select('new_orders.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number','u2.lat AS pickup_lat','u2.lon AS pickup_lon','u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark','u1add.latitude AS latitude','u1add.longitude AS longitude')
                    ->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')
                    ->leftJoin('address_new as u1add', 'new_orders.address_id', '=', 'u1add.id')
                    ->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_orders.pharmacy_id')
                    ->where(['new_orders.deliveryboy_id' => $user_id]);

                    if(isset($content->search_text)){
                        $searchtxt = $content->search_text;
                        $order_list = $order_list->where(function($query) use($searchtxt){
                            $query->where('new_orders.id', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1add.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.name', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1.name', 'like', '%'.$searchtxt.'%');
                        });
                    }

                    if(isset($content->from_date) && isset($content->to_date)){
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');

                        $order_list = $order_list->where(function($query) use ($from, $to){
                            $query->whereBetween('new_orders.deliver_datetime', [$from, $to]);
                            $query->orWhereBetween('new_orders.reject_datetime', [$from, $to]);
                        });
                    }

                     if(!empty($pharmacy_id)){
                        $order_list = $order_list->where('new_order_history.pharmacy_id', '=', $pharmacy_id);
                    }

            switch ($order_status) {
                case 'return':
                    $order_list = $order_list->where(['new_orders.order_status' => 'incomplete']);
                    break;
                default:
                    $order_list = $order_list->where(function($query){
                        $query->orWhere('new_orders.order_status', '=', 'incomplete');
                    });
                    break;
            }

            $order_list = $order_list->orderBy('new_orders.id', 'DESC');
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
                $orders_data1 = $order_list->paginate($per_page,'','',$page);
                $data_array = $orders_data1->toArray();
                $data_array = $data_array['data'];
            }elseif ($is_completed == 2) {
            		$order_list1 = new_orders::select('new_orders.id AS id','new_orders.order_status','new_orders.deliveryboy_id','new_orders.customer_id','new_orders.address_id','new_orders.pharmacy_id','new_orders.deliver_datetime','new_orders.reject_datetime','new_orders.order_number','new_orders.order_amount','new_orders.delivery_charges_id','new_orders.reject_cancel_reason','new_orders.leave_neighbour','new_orders.created_at', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number','u2.lat AS pickup_lat','u2.lon AS pickup_lon' ,'u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark','u1add.latitude AS latitude','u1add.longitude AS longitude')->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')
                    ->leftJoin('address_new as u1add', 'new_orders.address_id', '=', 'u1add.id')
                    ->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_orders.pharmacy_id')
                    ->where(['new_orders.deliveryboy_id' => $user_id]);

                    if(isset($content->search_text)){
                        $searchtxt = $content->search_text;
                        $order_list1 = $order_list1->where(function($query) use($searchtxt){
                            $query->where('new_orders.id', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1add.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.name', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1.name', 'like', '%'.$searchtxt.'%');
                        });
                    }

                    if(isset($content->from_date) && isset($content->to_date)){
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');

                        $order_list1 = $order_list1->where(function($query) use ($from, $to){
                            $query->whereBetween('new_orders.deliver_datetime', [$from, $to]);
                            $query->orWhereBetween('new_orders.reject_datetime', [$from, $to]);
                        });
                    }

                    if(!empty($pharmacy_id)){
                        $order_list1 = $order_list1->where('new_orders.pharmacy_id', '=', $pharmacy_id);
                    }
                   switch ($order_status) {
		                default:
		                    $order_list1 = $order_list1->where(function($query){
		                      	$query->orWhere('new_orders.order_status', '=', 'incomplete');
		                    });
		                    break;
		            }

                    $order_list = new_order_history::select('new_order_history.id AS id','new_order_history.order_status','new_order_history.deliveryboy_id','new_order_history.customer_id','new_order_history.address_id','new_order_history.pharmacy_id','new_order_history.deliver_datetime','new_order_history.reject_datetime','new_order_history.order_number','new_order_history.order_amount','new_order_history.delivery_charges_id','new_order_history.reject_cancel_reason','new_order_history.leave_neighbour','new_order_history.created_at', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number','u2.lat AS pickup_lat','u2.lon AS pickup_lon', 'u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark','u1add.latitude AS latitude','u1add.longitude AS longitude')
                    ->leftJoin('new_users as u1', 'u1.id', '=', 'new_order_history.customer_id')
                    ->leftJoin('address_new as u1add', 'new_order_history.address_id', '=', 'u1add.id')
                    ->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_order_history.pharmacy_id')
                    ->where(['new_order_history.deliveryboy_id' => $user_id]);

                    if(isset($content->search_text)){
                        $searchtxt = $content->search_text;
                        $order_list = $order_list->where(function($query) use($searchtxt){
                            $query->where('new_order_history.id', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1add.address', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u2.name', 'like', '%'.$searchtxt.'%');
                            $query->orWhere('u1.name', 'like', '%'.$searchtxt.'%');
                        });
                    }

                    if(isset($content->from_date) && isset($content->to_date)){
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');

                        $order_list = $order_list->where(function($query) use ($from, $to){
                            $query->whereBetween('new_order_history.deliver_datetime', [$from, $to]);
                            $query->orWhereBetween('new_order_history.reject_datetime', [$from, $to]);
                        });
                    }

                    if(!empty($pharmacy_id)){
                        $order_list = $order_list->where('new_order_history.pharmacy_id', '=', $pharmacy_id);
                    }

                switch ($order_status) {
                default:
                    $order_list = $order_list->where(function($query){
                        $query->where('new_order_history.order_status', '=', 'complete');
                    });
                    break;
                }
            //print query
              /*  $order_list = $order_list->orderBy('new_order_history.id', 'DESC')->union($order_list1)->toSql();*/
            	$order_list = $order_list->union($order_list1);
                $order_list = $order_list->orderBy('id','DESC');

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
                    $orders_data1 = $order_list->paginate($per_page,'','',$page);
                    $data_array = $orders_data1->toArray();
                    $data_array = $data_array['data'];
            }
            
            if(!empty($data_array)){
                foreach($data_array as $order) {
                    $object = (object)[];
                    $object->id = $order['id'];
                    $object->order_id = isset($order['order_number'])?($order['order_number']):'';
                    $object->order_status = $order['order_status'];
                    $object->price = isset($order['order_amount'])?($order['order_amount']):0;
                    
                    $delivery_type = new_delivery_charges::where('id', $order['delivery_charges_id'])->value('delivery_type');
                    $object->delivery_type = isset($delivery_type)?$delivery_type:'';

                    $object->rejectreason = isset($order['reject_cancel_reason'])?($order['reject_cancel_reason']):'';
                    $object->reject_date = isset($order['reject_datetime'])?($order['reject_datetime']):'';

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
                   $pickup_image_array = new_order_images::where(['order_id'=>$order['id'],'image_type'=>'pickup'])->get();
                     if(count($pickup_image_array)>0){
                        $pickup_images=[];
                        foreach ($pickup_image_array as $pickup_image_data) {
                            $pickup_image = '';
                            if (!empty($pickup_image_data->image_name)) {
                                $filename = storage_path('app/public/uploads/pickup/'.$pickup_image_data->image_name);
                                if (File::exists($filename)) {
                                    $pickup_image = asset('storage/app/public/uploads/pickup/'.$pickup_image_data->image_name);
                                    array_push($pickup_images, $pickup_image);
                                } 
                            }
                        }
                        $object->pickup_images = $pickup_images;
                    }

                   $deliver_image_array = new_order_images::where(['order_id'=>$order['id'],'image_type'=>'deliver'])->get();
                     if(count($deliver_image_array)>0){
                        $delivered_images=[];
                        foreach ($deliver_image_array as $deliver_image_data) {
                            $delivered_image = '';
                            if (!empty($deliver_image_data->image_name)) {
                                $filename = storage_path('app/public/uploads/deliver/'.$deliver_image_data->image_name);
                                if (File::exists($filename)) {
                                    $delivered_image = asset('storage/app/public/uploads/deliver/'.$deliver_image_data->image_name);
                                    array_push($delivered_images, $delivered_image);
                                } 
                            }
                        }
                        $object->delivered_images = $delivered_images;
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
                    $object->orderdate = (date_format(new DateTime($order['created_at']),"Y-m-d H:i:s"))?(date_format(new DateTime($order['created_at']),"Y-m-d H:i:s")):'';    
                    array_push($response['data']['content'], $object);
                }
            }
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

    public function orderdetail(Request $request){
    
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $order_status = isset($content->order_status) ? $content->order_status : '';

        $params = [
			'user_id' => $user_id,
			'order_id' => $order_id,
			'order_status' => $order_status
		];
		
		$validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'order_status' => 'required'
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
		$response['message'] = '';
        $response['data'] = (object)[];
        
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->get();

        if(count($user)>0){
            $order_list = new_orders::select('new_orders.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number', 'u2.lat AS pickup_lat','u2.lon AS pickup_lon','u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark','u1add.latitude AS latitude','u1add.longitude AS longitude', 'ordAssign.order_status AS ordAssign_status','a1.name as accept_order_by','as1.name as assign_order_by','d1.name as accept_by_deliveryboy','os1.accept_date as deliveryboy_accept_date');
            switch ($order_status) {
                case 'upcoming':
                    $order_list = $order_list->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'assign', 'ordAssign.order_status' => 'assign', 'new_orders.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_orders.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
                    });
                    break;
                case 'pickup':
                    $order_list = $order_list->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'assign', 'ordAssign.order_status' => 'accept', 'new_orders.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_orders.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
                    });
                    break;
                case 'deliver':
                    $order_list = $order_list->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'pickup', 'ordAssign.order_status' => 'accept', 'new_orders.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_orders.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
                    });
                    break;
                case 'complete':
                    $order_list = $order_list->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'complete', 'new_orders.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_orders.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
                    });
                    break;
                case 'incomplete':
                    $order_list = $order_list->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'incomplete', 'new_orders.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_orders.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
                    });;
                    break;
                default:
                    $order_list = $order_list->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_orders.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
                    });;
                    break;
            }

            $order_list = $order_list->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')->leftJoin('address_new as u1add', 'new_orders.address_id', '=', 'u1add.id')->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_orders.pharmacy_id')->leftJoin('new_pharma_logistic_employee as a1', 'a1.id', '=', 'new_orders.process_user_id')->leftJoin('new_pharma_logistic_employee as as1', 'as1.id', '=', 'new_orders.process_user_id')->leftJoin('new_pharma_logistic_employee as d1', 'd1.id', '=', 'new_orders.deliveryboy_id')->leftJoin('order_assign as os1', 'os1.order_id', '=', 'new_orders.id');

            $order = $order_list->orderBy('new_orders.id', 'DESC')->get();
            
            if(count($order)>0){
                    foreach ($order as $key => $value) {
                        $object = (object)[];

                        $object->id = $value['id'];
                        $object->order_id = isset($value['order_number'])?($value['order_number']):'';
                        $object->price = isset($value['order_amount'])?($value['order_amount']):0;
                        $object->rejectreason = isset($value['rejectreason'])?($value['rejectreason']):'';
                        $object->reject_date = isset($value['reject_date'])?($value['reject_date']):'';

                        if($order_status == 'deliver'){
                            $object->remain_time = "3600000";
                        }
                        
                        $delivery_type = new_delivery_charges::where('id', $value['delivery_charges_id'])->value('delivery_type');
                        $object->delivery_type = isset($delivery_type)?$delivery_type:'';
                        
                        $object->invoice = array();
                        $invoice_data = invoice::where('order_id', $value['id'])->get();

                        if(count($invoice_data)>0){
                            $invoice_images=[];
                            foreach ($invoice_data as $invoice) {
                                $invoice_image = '';
                                if (!empty($invoice->invoice)) {
                                    $filename = storage_path('app/public/uploads/invoice/'.$invoice->invoice);
                                    if (File::exists($filename)) {
                                        $invoice_image = asset('storage/app/public/uploads/invoice/'.$invoice->invoice);
                                        array_push($invoice_images, $invoice_image);
                                    } 
                                }
                            }
                            $object->invoice = $invoice_images;
                        }
                       if($value['order_status']=='pickup'){
                            $pickup_image_array = new_order_images::where(['order_id'=>$value['id'],'image_type'=>'pickup'])->get();
                             if(count($pickup_image_array)>0){
                                $pickup_images=[];
                                foreach ($pickup_image_array as $pickup_image_data) {
                                    $pickup_image = '';
                                    if (!empty($pickup_image_data->image_name)) {
                                        $filename = storage_path('app/public/uploads/pickup/'.$pickup_image_data->image_name);
                                        if (File::exists($filename)) {
                                            $pickup_image = asset('storage/app/public/uploads/pickup/'.$pickup_image_data->image_name);
                                            array_push($pickup_images, $pickup_image);
                                        } 
                                    }
                                }
                                $object->pickup_images = $pickup_images;
                                $object->order_pickup =  $value['pickup_datetime'];
                            }
                       }else{
                            $object->pickup_images = [];
                            $object->order_pickup = '';
                       }
                       
                       if($value['order_status']=='complete'){
                            $deliver_image_array = new_order_images::where(['order_id'=>$value['id'],'image_type'=>'deliver'])->get();
                             if(count($deliver_image_array)>0){
                                $delivered_images=[];
                                foreach ($deliver_image_array as $deliver_image_data) {
                                    $delivered_image = '';
                                    if (!empty($deliver_image_data->image_name)) {
                                        $filename = storage_path('app/public/uploads/deliver/'.$deliver_image_data->image_name);
                                        if (File::exists($filename)) {
                                            $delivered_image = asset('storage/app/public/uploads/deliver/'.$deliver_image_data->image_name);
                                            array_push($delivered_images, $delivered_image);
                                        } 
                                    }
                                }
                                $object->delivered_images = $delivered_images;
                                $object->order_delivered = $value['deliver_datetime'];
                            }
                        }else{
                            $object->delivered_images = [];
                            $object->order_delivered = '';
                        }

                        $pickup_info = (object)[];
                        $pickup_info->name = isset($value['pickup_name'])?($value['pickup_name']):'';
                        $pickup_info->address = isset($value['pickup_address'])?($value['pickup_address']):'';
                        $pickup_info->mobile_number = isset($value['pickup_mobile_number'])?($value['pickup_mobile_number']):'';
                        $pickup_info->locality = isset($value['pickup_locality'])?$value['pickup_locality']:'';
                        $pickup_info->landmark = isset($value['pickup_landmark'])?$value['pickup_landmark']:'';
                        $pickup_info->lat = isset($value['pickup_lat'])?$value['pickup_lat']:'';
                        $pickup_info->lon = isset($value['pickup_lon'])?$value['pickup_lon']:'';
                        $object->pickup_location = $pickup_info;

                        $delivery_info = (object)[];
                        $delivery_info->name = isset($value['delivery_name'])?($value['delivery_name']):'';
                        $delivery_info->address = isset($value['delivery_address'])?($value['delivery_address']):'';
                        $delivery_info->mobile_number = isset($value['delivery_mobile_number'])?($value['delivery_mobile_number']):'';
                        $delivery_info->locality = isset($value['delivery_locality'])?$value['delivery_locality']:'';
                        $delivery_info->landmark = isset($value['delivery_landmark'])?$value['delivery_landmark']:'';
                        $delivery_info->latitude = isset($value['latitude'])?(string)$value['latitude']:'';
                        $delivery_info->longitude = isset($value['longitude'])?(string)$value['longitude']:'';
                        $object->delivery_location = $delivery_info;

                        $object->leave_with_neighbour = isset($value['leave_neighbour'])?$value['leave_neighbour']:'false';
                        $object->neighbour_name = isset($value['neighbour_info'])?$value['neighbour_info']:'';
                        $object->orderdate = (date_format($value['created_at'],"Y-m-d H:i:s"))?(date_format($value['created_at'],"Y-m-d H:i:s")):'';
                        $object->order_receive = (date_format($value['created_at'],"Y-m-d H:i:s"))?(date_format($value['created_at'],"Y-m-d H:i:s")):'';
                        $object->order_accept = isset($value['accept_datetime'])?($value['accept_datetime']):'';
                        $object->order_assign = isset($value['assign_datetime'])?($value['assign_datetime']):'';
                        $object->reject_datetime = isset($value['reject_datetime'])?($value['reject_datetime']):'';
                        $object->accept_order_by = isset($value['accept_order_by'])?($value['accept_order_by']):'';
                        $object->assign_order_by = isset($value['assign_order_by'])?($value['assign_order_by']):'';
                        $object->accept_by_deliveryboy = isset($value['accept_by_deliveryboy'])?($value['accept_by_deliveryboy']):'';
                        $object->deliveryboy_accept_date = isset($value['deliveryboy_accept_date'])?($value['deliveryboy_accept_date']):'';
                        $response['data']= $object;
                    }
        } elseif ($order_status =='complete') {
             $order_list_complete = new_order_history::select('new_order_history.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number', 'u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark', 'ordAssign.order_status AS ordAssign_status','a1.name as accept_order_by','as1.name as assign_order_by','d1.name as accept_by_deliveryboy','os1.accept_date as deliveryboy_accept_date');

            switch ($order_status) {
                case 'complete':
                    $order_list_complete = $order_list_complete->where(['new_order_history.deliveryboy_id' => $user_id, 'new_order_history.order_status' => 'complete', 'new_order_history.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_order_history.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_order_history.deliveryboy_id');
                    });
                    break;
                default:
                    $order_list_complete = $order_list_complete->where(['new_order_history.deliveryboy_id' => $user_id, 'new_order_history.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_order_history.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_order_history.deliveryboy_id');
                    });;
                    break;
            }

            $order_list_complete = $order_list_complete->leftJoin('new_users as u1', 'u1.id', '=', 'new_order_history.customer_id')->leftJoin('address_new as u1add', 'new_order_history.address_id', '=', 'u1add.id')->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_order_history.pharmacy_id')->leftJoin('new_pharma_logistic_employee as a1', 'a1.id', '=', 'new_order_history.process_user_id')->leftJoin('new_pharma_logistic_employee as as1', 'as1.id', '=', 'new_order_history.process_user_id')->leftJoin('new_pharma_logistic_employee as d1', 'd1.id', '=', 'new_order_history.deliveryboy_id')->leftJoin('order_assign as os1', 'os1.order_id', '=', 'new_order_history.id');

            $order_complete = $order_list_complete->orderBy('new_order_history.id', 'DESC')->get();
            foreach ($order_complete as $key => $value) {
                    $object = (object)[];

                        $object->id = $value['order_id'];
                        $object->order_id = isset($value['order_number'])?($value['order_number']):'';
                        $object->price = isset($value['order_amount'])?($value['order_amount']):0;
                        $object->rejectreason = isset($value['rejectreason'])?($value['rejectreason']):'';
                        $object->reject_date = isset($value['reject_date'])?($value['reject_date']):'';

                        if($order_status == 'deliver'){
                            $object->remain_time = "3600000";
                        }
                        
                        $delivery_type = new_delivery_charges::where('id', $value['delivery_charges_id'])->value('delivery_type');
                        $object->delivery_type = isset($delivery_type)?$delivery_type:'';
                        
                        $object->invoice = array();
                        $invoice_data = invoice::where('order_id', $value['order_id'])->get();

                        if(count($invoice_data)>0){
                            $invoice_images=[];
                            foreach ($invoice_data as $invoice) {
                                $invoice_image = '';
                                if (!empty($invoice->invoice)) {
                                    $filename = storage_path('app/public/uploads/invoice/'.$invoice->invoice);
                                    if (File::exists($filename)) {
                                        $invoice_image = asset('storage/app/public/uploads/invoice/'.$invoice->invoice);
                                        array_push($invoice_images, $invoice_image);
                                    } 
                                }
                            }
                            $object->invoice = $invoice_images;
                        }
                            $pickup_image_array = new_order_images::where(['order_id'=>$value['order_id'],'image_type'=>'pickup'])->get();
                             if(count($pickup_image_array)>0){
                                $pickup_images=[];
                                foreach ($pickup_image_array as $pickup_image_data) {
                                    $pickup_image = '';
                                    if (!empty($pickup_image_data->image_name)) {
                                        $filename = storage_path('app/public/uploads/pickup/'.$pickup_image_data->image_name);
                                        if (File::exists($filename)) {
                                            $pickup_image = asset('storage/app/public/uploads/pickup/'.$pickup_image_data->image_name);
                                            array_push($pickup_images, $pickup_image);
                                        } 
                                    }
                                }
                                $object->pickup_images = $pickup_images;
                                $object->order_pickup =  $value['pickup_datetime'];
                            }
                       
                       
                       
                            $deliver_image_array = new_order_images::where(['order_id'=>$value['order_id'],'image_type'=>'deliver'])->get();
                             if(count($deliver_image_array)>0){
                                $delivered_images=[];
                                foreach ($deliver_image_array as $deliver_image_data) {
                                    $delivered_image = '';
                                    if (!empty($deliver_image_data->image_name)) {
                                        $filename = storage_path('app/public/uploads/deliver/'.$deliver_image_data->image_name);
                                        if (File::exists($filename)) {
                                            $delivered_image = asset('storage/app/public/uploads/deliver/'.$deliver_image_data->image_name);
                                            array_push($delivered_images, $delivered_image);
                                        } 
                                    }
                                }
                                $object->delivered_images = $delivered_images;
                                $object->order_delivered = $value['deliver_datetime'];
                            }
                       

                        $pickup_info = (object)[];
                        $pickup_info->name = isset($value['pickup_name'])?($value['pickup_name']):'';
                        $pickup_info->address = isset($value['pickup_address'])?($value['pickup_address']):'';
                        $pickup_info->mobile_number = isset($value['pickup_mobile_number'])?($value['pickup_mobile_number']):'';
                        $pickup_info->locality = isset($value['pickup_locality'])?$value['pickup_locality']:'';
                        $pickup_info->landmark = isset($value['pickup_landmark'])?$value['pickup_landmark']:'';
                        $pickup_info->lat = isset($value['pickup_lat'])?$value['pickup_lat']:'';
                        $pickup_info->lon = isset($value['pickup_lon'])?$value['pickup_lon']:'';
                        $object->pickup_location = $pickup_info;

                        $delivery_info = (object)[];
                        $delivery_info->name = isset($value['delivery_name'])?($value['delivery_name']):'';
                        $delivery_info->address = isset($value['delivery_address'])?($value['delivery_address']):'';
                        $delivery_info->mobile_number = isset($value['delivery_mobile_number'])?($value['delivery_mobile_number']):'';
                        $delivery_info->locality = isset($value['delivery_locality'])?$value['delivery_locality']:'';
                        $delivery_info->landmark = isset($value['delivery_landmark'])?$value['delivery_landmark']:'';
                        $delivery_info->latitude = isset($value['latitude'])?(string)$value['latitude']:'';
                        $delivery_info->longitude = isset($value['longitude'])?(string)$value['longitude']:'';
                        $object->delivery_location = $delivery_info;

                        $object->leave_with_neighbour = isset($value['leave_neighbour'])?$value['leave_neighbour']:'false';
                        $object->neighbour_name = isset($value['neighbour_info'])?$value['neighbour_info']:'';
                        $object->orderdate = (date_format($value['created_at'],"Y-m-d H:i:s"))?(date_format($value['created_at'],"Y-m-d H:i:s")):'';
                        $object->order_receive = (date_format($value['created_at'],"Y-m-d H:i:s"))?(date_format($value['created_at'],"Y-m-d H:i:s")):'';
                        $object->order_accept = isset($value['accept_datetime'])?($value['accept_datetime']):'';
                        $object->order_assign = isset($value['assign_datetime'])?($value['assign_datetime']):'';
                        $object->reject_datetime = isset($value['reject_datetime'])?($value['reject_datetime']):'';
                        $object->accept_order_by = isset($value['accept_order_by'])?($value['accept_order_by']):'';
                        $object->assign_order_by = isset($value['assign_order_by'])?($value['assign_order_by']):'';
                        $object->accept_by_deliveryboy = isset($value['accept_by_deliveryboy'])?($value['accept_by_deliveryboy']):'';
                        $object->deliveryboy_accept_date = isset($value['deliveryboy_accept_date'])?($value['deliveryboy_accept_date']):'';
                        $response['data']= $object;
                    }
            }else{
                 $response['status'] = 404;
            } 
        }
        else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

    public function pharmacy_list(Request $request){
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
        $response['data'] = array();

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->get();

        if(count($user)>0){
            $order_list = new_orders::select('new_orders.pharmacy_id')->where(['new_orders.deliveryboy_id' => $user_id])->where(function($query){
                $query->where('new_orders.order_status', '=', 'complete');
                $query->orWhere('new_orders.order_status', '=', 'incomplete');
            })->get();

            if(count($order_list)){
                $pharmacyid_array = array();
                foreach ($order_list as $key => $value) {
                    array_push($pharmacyid_array, $value->pharmacy_id);
                }
                $pharmacyid_array = array_unique($pharmacyid_array);
                $pharmacy_list = new_pharmacies::select('id', 'name')->whereIn('id', $pharmacyid_array)->get();
                $response['data']= $pharmacy_list;
                $response['message'] = 'PharmacyList Successfully';
            }
            $pharmacy_list = $order_list->where(['new_orders.order_status' => 'complete']);
        } else {
            $response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
}
