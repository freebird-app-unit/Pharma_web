<?php

namespace App\Http\Controllers\Api\Seller;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\SellerModel\Orders;
use App\SellerModel\Prescription;
use App\SellerModel\Rejectreason;
use App\SellerModel\Cancelreason;
use App\SellerModel\Callhistory;
use App\SellerModel\Incompletereason;
use App\SellerModel\User;
use App\SellerModel\delivery_charges;
use App\SellerModel\invoice;
use App\SellerModel\Orderassign;
use App\SellerModel\new_address;
use App\new_orders;
use App\SellerModel\new_pharma_logistic_employee;
use App\DeliveryboyModel\new_order_images;
use App\DeliveryboyModel\new_order_history;
use App\SellerModel\new_users;
use App\new_logistics;
use App\multiple_prescription;
use App\SellerModel\new_pharmacies;
use App\new_delivery_charges;
use Validator;
use File;
use Image;
use Storage;
use DB;
use App\Events\AssignOrderLogistic;
use App\notification_user;
use App\notification_seller;
use App\notification_deliveryboy;
use Mail;
use GuzzleHttp\Client;
use DateTime;
use DatePeriod;
use DateInterval;
use Helper;


class AcceptorderController_imagedata extends Controller
{
    public function order_list_imagedata(Request $request)
    {
        $response = array();
    	$data = $request->input('data');
    	$encode_string = encode_string($data);
    	$content = json_decode($encode_string);

        $order = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $pharmacy_id = isset($content->pharmacy_id) ? $content->pharmacy_id : '';
        $search_text = isset($content->search_text) ? $content->search_text : '';
        $user_id = isset($content->user_id) ? $content->user_id : '';
        $page = isset($content->page) ? $content->page : '';

        if (!empty($pharmacy_id) && !empty($search_text)) {
         $order_list = new_orders::select('new_orders.id','new_orders.pharmacy_id','new_orders.order_status','new_orders.customer_id','new_orders.order_number','new_orders.checking_by','new_orders.delivery_charges_id','new_orders.order_note','new_orders.order_type','new_orders.total_days','new_orders.prescription_id','new_orders.external_delivery_initiatedby','new_orders.create_datetime','u1.name')->where(['new_orders.pharmacy_id' => $pharmacy_id, 'new_orders.order_status' => 'new'])->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')->where('u1.name', 'like', $search_text.'%')->orWhere('new_orders.order_number', 'like', $search_text.'%')->orderBy('new_orders.id', 'DESC');
            
            $total = $order_list->count();
            $page = $page;
            if($total < ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $order_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
            
        }else{
               $order_list =  new_orders::select('id','pharmacy_id','order_status','customer_id','order_number','checking_by','delivery_charges_id','order_note','order_type','total_days','prescription_id','external_delivery_initiatedby','create_datetime')->where('pharmacy_id', $pharmacy_id)->where('order_status','new')->orderBy('id', 'DESC');

            $total = $order_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $order_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];      
        }
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
            if(count($data_array)>0){
                foreach($data_array as $value) { 
                       		$mutiple_data = multiple_prescription::where(['prescription_id'=>$value['prescription_id'],'is_delete'=>'0'])->get();
							$mutiple_images = [];
							if(count($mutiple_data)>0){
									foreach ($mutiple_data as $mutiple) {
										$mutiple_images[]=[
										'id'	=> $mutiple->id,
										'image' => $mutiple->image,
									];	
								}
							}
                    $user_data = new_users::where('id',$value['customer_id'])->first();
                    if(!empty($user_data)){
                        $name =$user_data->name;
                    }else{
                        $name = '';
                    }

                    $checking = new_pharma_logistic_employee::where('id',$value['checking_by'])->first();
                    if(!empty($checking)){
                        $checking_by =$checking->name;
                    }else{
                        $checking_by = '';
                    }

                    $delivery_type_data = new_delivery_charges::where('id',$value['delivery_charges_id'])->first();
                    if(!empty($delivery_type_data)){
                        $delivery_type =$delivery_type_data->delivery_type;
                    }else{
                        $delivery_type = 'free';
                    }
                   
                     $order[] = [
                                 'order_id' => $value['id'],
                                 'order_number' => $value['order_number'],
                                 'order_type' => $value['order_type'],
                                 'total_days' => ($value['total_days'])?$value['total_days']:'',
                                 'customer_name' => $name,
                                 'prescription_image' => $mutiple_images,
                                 'order_note' => ($value['order_note'])?$value['order_note']:'',
                                 'checking_by' => $checking_by,
                                 'checking_by_user_id' => ($value['checking_by'])?$value['checking_by']:'',
                                 'delivery_type' => $delivery_type,
                                 'external_delivery_initiatedby' => ($value['external_delivery_initiatedby'])?$value['external_delivery_initiatedby']:'',
                                 'order_time'=>($value['create_datetime'])?$value['create_datetime']:''
                    ];
                }
                $response['status'] = 200;
                $response['message'] = 'Order List';
            }else{
                $response['status'] = 404;
                $response['message'] = 'Order List';
            }
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }   

        $response['data']->content = $order;
        return decode_string($response, 200);
    }

    public function acceptorderlist_imagedata(Request $request)
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
        $accept = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        if (!empty($user_id) && !empty($search_text)) {

          $accept_list = new_orders::select('new_orders.process_user_id','new_orders.order_status','new_orders.customer_id','new_orders.accept_datetime','new_orders.order_number','new_orders.id','new_orders.prescription_id','new_orders.delivery_charges_id','new_orders.order_note','new_orders.total_days','new_orders.reminder_days','new_orders.order_amount','new_orders.order_type','new_orders.external_delivery_initiatedby','new_orders.create_datetime','u1.name')->where(['new_orders.process_user_id' => $user_id, 'new_orders.order_status' => 'accept'])->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')->where('u1.name', 'like', $search_text.'%')->orWhere('new_orders.order_number', 'like', $search_text.'%')->orderBy('new_orders.accept_datetime', 'DESC');


            $total = $accept_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $accept_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 

        }else{
               $accept_list =  new_orders::select('process_user_id','order_status','customer_id','accept_datetime','order_number','id','prescription_id','customer_id','delivery_charges_id','order_note','total_days','reminder_days','order_amount','order_type','external_delivery_initiatedby','create_datetime')->where('process_user_id', $user_id)->where(['order_status'=>'accept'])->orderBy('accept_datetime', 'DESC');

            $total = $accept_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $accept_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
        }

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                if(count($data_array)>0){
                         foreach($data_array as $value) {
                                    $mutiple_data = multiple_prescription::where(['prescription_id'=>$value['prescription_id'],'is_delete'=>'0'])->get();
									$mutiple_images = [];
									if(count($mutiple_data)>0){
											foreach ($mutiple_data as $mutiple) {
												$mutiple_images[]=[
												'id'	=> $mutiple->id,
												'image' => $mutiple->image,
											];	
										}
									}
                                $user_data = new_users::where('id',$value['customer_id'])->first();
                                if(!empty($user_data)){
                                    $name =$user_data->name;
                                }else{
                                    $name = '';
                                }

                                $delivery_type_data = new_delivery_charges::where('id',$value['delivery_charges_id'])->first();
                                if(!empty($delivery_type_data)){
                                    $delivery_type =$delivery_type_data->delivery_type;
                                }else{
                                    $delivery_type = 'free';
                                }

                                    $accept[] = [
                                    'order_id' => $value['id'],
                                    'order_number' => $value['order_number'],
                                    'prescription_image' => $mutiple_images,
                                    'customer_name' => $name,
                                    'accepted_date' => $value['accept_datetime'],
                                    'delivery_type' => $delivery_type,
                                    'order_note' => $value['order_note'],
                                    'total_days' => ($value['total_days'])?$value['total_days']:'',
                                    'reminder_days' => ($value['reminder_days'])?$value['reminder_days']:'',
                                    'order_amount' => ($value['order_amount'])?$value['order_amount']:'',
                                    'order_type' => $value['order_type'],
                                    'delivery_type' => $delivery_type,
                                    'external_delivery_initiatedby' => ($value['external_delivery_initiatedby'])?$value['external_delivery_initiatedby']:'',
                                    'order_time'=>($value['create_datetime'])?$value['create_datetime']:''
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'Accepted Order List';
                }else {
                        $response['status'] = 404;
                        $response['message'] = 'Accepted Order List';
                }
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }

        $response['data']->content = $accept;
        
        return decode_string($response, 200);
    }

    public function outoforderlist_imagedata(Request $request){
        $response = array();
    		$data = $request->input('data');
    		$encode_string = encode_string($data);
    		$content = json_decode($encode_string);
        
        $user_id = isset($content->user_id) ? $content->user_id : '';
        $search_text = isset($content->search_text) ? $content->search_text : '';
        $delivery_boy = isset($content->delivery_boy) ? $content->delivery_boy : '';
        $order_status = isset($content->order_status) ? $content->order_status : '';
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
        $outof = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        if (!empty($user_id) && !empty($search_text)) {
          $outofdelivery_list = new_orders::select('new_orders.process_user_id','new_orders.order_status','new_orders.customer_id','new_orders.order_number','new_orders.assign_datetime','new_orders.deliveryboy_id','u1.name','new_orders.prescription_id','new_orders.delivery_charges_id','new_orders.id','new_orders.accept_datetime','new_orders.order_amount','new_orders.pickup_datetime','new_orders.external_delivery_initiatedby','new_orders.create_datetime')->where('process_user_id',$user_id)->where(function($query) {
                        $query->where('order_status','assign')
                            ->orWhere('order_status','pickup')
                            ->orWhere('order_status','accept');
                    })->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')->where('u1.name', 'like', $search_text.'%')->orWhere('new_orders.order_number', 'like', $search_text.'%')->orderBy('new_orders.id', 'DESC');

            $total = $outofdelivery_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $outofdelivery_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 

        }elseif(!empty($user_id) && !empty($delivery_boy)){
             $outofdelivery_list = new_orders::select('process_user_id','order_status','customer_id','order_number','assign_datetime','deliveryboy_id','prescription_id','delivery_charges_id','id','accept_datetime','order_amount','pickup_datetime','external_delivery_initiatedby','create_datetime')->where('process_user_id',$user_id)->where(function($query) {
                        $query->where('order_status','assign')
                            ->orWhere('order_status','pickup')
                            ->orWhere('order_status','accept');
                    })->where('deliveryboy_id', 'like', $delivery_boy.'%')->orderBy('new_orders.id', 'DESC');
            $total = $outofdelivery_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $outofdelivery_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
             
        }elseif(!empty($user_id) && !empty($order_status)){
          $outofdelivery_list = new_orders::select('process_user_id','order_status','customer_id','order_number','assign_datetime','deliveryboy_id','prescription_id','delivery_charges_id','id','accept_datetime','order_amount','pickup_datetime','external_delivery_initiatedby','create_datetime')->where('process_user_id',$user_id)->where('order_status', 'like', $order_status.'%')->orderBy('new_orders.id', 'DESC')->get();

            $total = $outofdelivery_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $outofdelivery_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];

        }else{
               $outofdelivery_list = new_orders::select('process_user_id','order_status','customer_id','order_number','assign_datetime','deliveryboy_id','prescription_id','delivery_charges_id','id','accept_datetime','order_amount','pickup_datetime','external_delivery_initiatedby','create_datetime')->where('process_user_id',$user_id)->where(function($query) {
                        $query->where('order_status','assign')
                            ->orWhere('order_status','pickup');
                    })->orderBy('new_orders.id', 'DESC');

            $total = $outofdelivery_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $outofdelivery_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
        }

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                if(!empty($data_array)){
                         foreach($data_array as $value) {
                                    $mutiple_data = multiple_prescription::where(['prescription_id'=>$value['prescription_id'],'is_delete'=>'0'])->get();
									$mutiple_images = [];
									if(count($mutiple_data)>0){
											foreach ($mutiple_data as $mutiple) {
												$mutiple_images[]=[
												'id'	=> $mutiple->id,
												'image' => $mutiple->image,
											];	
										}
									} 
                                $user_data = new_users::where('id',$value['customer_id'])->first();
                                if(!empty($user_data)){
                                    $name =$user_data->name;
                                    $mobile =$user_data->mobile_number;
                                }else{
                                    $name = '';
                                    $mobile = '';
                                }


                                $delivery_type_data = new_delivery_charges::where('id',$value['delivery_charges_id'])->first();
                                if(!empty($delivery_type_data)){
                                    $delivery_type =$delivery_type_data->delivery_type;
                                }else{
                                    $delivery_type = 'free';
                                }

                                $delivery_name_data = new_pharma_logistic_employee::where('id', $value['deliveryboy_id'])->first();
                                if(!empty($delivery_name_data)){
                                    $delivery_name =$delivery_name_data->name;
                                }else{
                                    $delivery_name = '';
                                }

                                    $outof[] = [
                                    'order_id' => $value['id'],
                                    'order_number' => $value['order_number'],
                                    'prescription_image' => $mutiple_images,
                                    'customer_name' => $name,
                                    'mobile_number' => $mobile,
                                    'deliveryboy_id' => $value['deliveryboy_id'],
                                    'deliveryboy_name' => $delivery_name,
                                    'accepted_date' => ($value['accept_datetime'])?$value['accept_datetime']:'',
                                    'assigned_date' => ($value['assign_datetime'])?$value['assign_datetime']:'',
                                    'delivery_type' => $delivery_type,
                                    'order_amount' => ($value['order_amount'])?$value['order_amount']:'',
                                    'pickup_date'=> ($value['pickup_datetime'])?$value['pickup_datetime']:'',
                                    'order_status' => $value['order_status'],
                                    'external_delivery_initiatedby' => ($value['external_delivery_initiatedby'])?$value['external_delivery_initiatedby']:'',
                                    'order_time'=>($value['create_datetime'])?$value['create_datetime']:''
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'Out Of Delivery List';
                } else {
                        $response['status'] = 404;
                        $response['message'] = 'Out Of Delivery List';
                }
         }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
         }
        $response['data']->content = $outof;
        return decode_string($response, 200);
    }
}
