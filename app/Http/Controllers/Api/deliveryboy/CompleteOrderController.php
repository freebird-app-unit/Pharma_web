<?php

namespace App\Http\Controllers\Api\deliveryboy;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Orders;
use App\Orderassign;
use App\Address;
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

class CompleteOrderController extends Controller
{
    public function completeorderdetail(Request $request){
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
            $order_list = new_order_history::select('new_order_history.*', 'u2.name AS pickup_name', 'u2.address AS pickup_address', 'u2.mobile_number AS pickup_mobile_number', 'u1.name AS delivery_name', 'u1add.address AS delivery_address', 'u1.mobile_number AS delivery_mobile_number', 'u1add.locality AS delivery_locality', 'u1add.locality AS delivery_landmark', 'ordAssign.order_status AS ordAssign_status','a1.name as accept_order_by','as1.name as assign_order_by','d1.name as accept_by_deliveryboy','os1.accept_date as deliveryboy_accept_date');

            switch ($order_status) {
                /*case 'upcoming':
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
                    break;*/
                case 'complete':
                    $order_list = $order_list->where(['new_order_history.deliveryboy_id' => $user_id, 'new_order_history.order_status' => 'complete', 'new_order_history.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_order_history.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_order_history.deliveryboy_id');
                    });
                    break;
                /*case 'incomplete':
                    $order_list = $order_list->where(['new_orders.deliveryboy_id' => $user_id, 'new_orders.order_status' => 'incomplete', 'new_orders.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_orders.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_orders.deliveryboy_id');
                    });;
                    break;*/
                default:
                    $order_list = $order_list->where(['new_order_history.deliveryboy_id' => $user_id, 'new_order_history.id' => $order_id])->leftJoin('order_assign as ordAssign', function($join) {
                        $join->on('ordAssign.order_id', '=', 'new_order_history.id')
                        ->on('ordAssign.deliveryboy_id', '=', 'new_order_history.deliveryboy_id');
                    });;
                    break;
            }

            $order_list = $order_list->leftJoin('new_users as u1', 'u1.id', '=', 'new_order_history.customer_id')->leftJoin('address_new as u1add', 'new_order_history.address_id', '=', 'u1add.id')->leftJoin('new_pharmacies as u2', 'u2.id', '=', 'new_order_history.pharmacy_id')->leftJoin('new_pharma_logistic_employee as a1', 'a1.id', '=', 'new_order_history.process_user_id')->leftJoin('new_pharma_logistic_employee as as1', 'as1.id', '=', 'new_order_history.process_user_id')->leftJoin('new_pharma_logistic_employee as d1', 'd1.id', '=', 'new_order_history.deliveryboy_id')->leftJoin('order_assign as os1', 'os1.order_id', '=', 'new_order_history.id');

            $order = $order_list->orderBy('new_order_history.id', 'DESC')->get()->first();


            if(!empty($order)){
                    $object = (object)[];

                    $object->id = $order->id;
                    $object->order_id = isset($order->order_number)?($order->order_number):'';
                    $object->price = isset($order->order_amount)?($order->order_amount):0;
                    $object->rejectreason = isset($order->rejectreason)?($order->rejectreason):'';
                    $object->reject_date = isset($order->reject_date)?($order->reject_date):'';

                    if($order_status == 'deliver'){
                        $object->remain_time = "3600000";
                    }
                    
                    $delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                    $object->delivery_type = isset($delivery_type)?$delivery_type:'';
                    
                    $object->invoice = array();
                    $invoice_data = invoice::where('order_id', $order->id)->get();

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

                   $pickup_image_array = new_order_images::where(['order_id'=>$order_id,'image_type'=>'pickup'])->get();
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

                    $deliver_image_array = new_order_images::where(['order_id'=>$order_id,'image_type'=>'deliver'])->get();
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

                    $object->leave_with_neighbour = isset($order->leave_neighbour)?$order->leave_neighbour:'false';
                    $object->neighbour_name = isset($order->neighbour_name)?$order->neighbour_name:'';
                    $object->orderdate = (date_format($order->created_at,"Y-m-d H:i:s"))?(date_format($order->created_at,"Y-m-d H:i:s")):'';
                    $object->order_receive = (date_format($order->created_at,"Y-m-d H:i:s"))?(date_format($order->created_at,"Y-m-d H:i:s")):'';
                    $object->order_accept = isset($order->accept_datetime)?($order->accept_datetime):'';
                    $object->order_assign = isset($order->assign_datetime)?($order->assign_datetime):'';
                    $object->order_pickup = isset($order->pickup_datetime)?($order->pickup_datetime):'';
                    $object->order_delivered = isset($order->deliver_datetime)?($order->deliver_datetime):'';
                    $object->reject_datetime = isset($order->reject_datetime)?($order->reject_datetime):'';
                    $object->accept_order_by = isset($order->accept_order_by)?($order->accept_order_by):'';
                    $object->assign_order_by = isset($order->assign_order_by)?($order->assign_order_by):'';
                    $object->accept_by_deliveryboy = isset($order->accept_by_deliveryboy)?($order->accept_by_deliveryboy):'';
                    $object->deliveryboy_accept_date = isset($order->deliveryboy_accept_date)?($order->deliveryboy_accept_date):'';
                    $response['data']= $object;

            }
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
}
