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
          $outofdelivery_list = new_orders::select('process_user_id','order_status','customer_id','order_number','assign_datetime','deliveryboy_id','prescription_id','delivery_charges_id','id','accept_datetime','order_amount','pickup_datetime','external_delivery_initiatedby','create_datetime')->where('process_user_id',$user_id)->where('order_status', 'like', $order_status.'%')->orderBy('new_orders.id', 'DESC');

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

    public function rejectorderlist_imagedata(Request $request){
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
        $reject = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        if (!empty($user_id) && !empty($search_text)) {
         
           $reject_list = new_orders::select('new_orders.process_user_id','new_orders.order_status','new_orders.customer_id','new_orders.order_number','new_orders.id','new_orders.prescription_id','new_orders.reject_cancel_reason','new_orders.external_delivery_initiatedby','new_orders.create_datetime','u1.name','new_orders.reject_datetime','new_orders.delivery_charges_id')->where(['new_orders.process_user_id' => $user_id, 'new_orders.order_status' => 'reject'])->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')->where('u1.name', 'like', $search_text.'%')->orWhere('new_orders.order_number', 'like', $search_text.'%')->orderBy('new_orders.id', 'DESC');

           $total = $reject_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $reject_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
           
        }else{
               $reject_list =  new_orders::select('process_user_id','order_status','customer_id','order_number','id','prescription_id','reject_cancel_reason','external_delivery_initiatedby','create_datetime','reject_datetime','delivery_charges_id')->where('process_user_id', $user_id)->where('order_status','reject')->orderBy('reject_datetime', 'DESC');

                $total = $reject_list->count();
                $page = $page;
                if($total > ($page*10)){
                  $is_record_available = 1;
                }else{
                  $is_record_available = 0;
                }
                $per_page = 10;
                $response['data']->currentPageIndex = $page;
                $response['data']->totalPage = ceil($total/$per_page);
                $orders = $reject_list->paginate($per_page,'','',$page);
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
                                $reject[] = [
                                    'order_id' => $value['id'],
                                    'order_number' => $value['order_number'],
                                    'prescription_image' => $mutiple_images,
                                    'customer_name' => $name,
                                    'reason' =>  ($value['reject_cancel_reason'])?$value['reject_cancel_reason']:'',
                                    'delivery_type' => $delivery_type,
                                    'external_delivery_initiatedby' => ($value['external_delivery_initiatedby'])?$value['external_delivery_initiatedby']:'',
                                    'order_time'=>($value['create_datetime'])?$value['create_datetime']:''
                                    ];
                                }
                            $response['status'] = 200;
                            $response['message'] = 'Rejected Order List';
                    } else {
                            $response['status'] = 404;
                            $response['message'] = 'Rejected Order List';
                    }
             }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
             }
        
        $response['data']->content = $reject;
        
        return decode_string($response, 200);
    }

    public function cancelorderlist_seller_imagedata(Request $request){
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
        $cancel = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        if (!empty($user_id) && !empty($search_text)) {

             $cancel_list = new_order_history::select('new_order_history.process_user_id','new_order_history.order_status','new_order_history.customer_id','new_order_history.order_number','new_order_history.id','new_order_history.prescription_id','new_order_history.reject_cancel_reason','new_order_history.external_delivery_initiatedby','new_order_history.create_datetime','u1.name','new_order_history.order_id','new_order_history.delivery_charges_id')->where(['new_order_history.process_user_id' => $user_id, 'new_order_history.order_status' => 'cancel'])->leftJoin('new_users as u1', 'u1.id', '=', 'new_order_history.customer_id')->where('u1.name', 'like', $search_text.'%')->orWhere('new_order_history.order_number', 'like', $search_text.'%')->orderBy('new_order_history.id', 'DESC');

            $total = $cancel_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $cancel_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
        }else{
               $cancel_list =  new_order_history::select('process_user_id','order_status','customer_id','order_number','id','prescription_id','reject_cancel_reason','external_delivery_initiatedby','create_datetime','order_id','delivery_charges_id')->where('process_user_id', $user_id)->where('order_status','cancel')->orderBy('id', 'DESC');

            $total = $cancel_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $cancel_list->paginate($per_page,'','',$page);
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
                                    $cancel[] = [
                                    'order_id' => $value['order_id'],
                                    'order_number' => $value['order_number'],
                                    'prescription_image' => $mutiple_images,
                                    'customer_name' => $name,
                                    'reason' =>($value['reject_cancel_reason'])?$value['reject_cancel_reason']:'',
                                    'delivery_type' => $delivery_type,
                                    'external_delivery_initiatedby' => ($value['external_delivery_initiatedby'])?$value['external_delivery_initiatedby']:'',
                                    'order_time'=>($value['create_datetime'])?$value['create_datetime']:''
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'Cancelled Order List';
                } else {
                        $response['status'] = 404;
                        $response['message'] = 'Cancelled Order List';
                }
            }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
            }
        
        $response['data']->content = $cancel;
        
        return decode_string($response, 200);
    }

    public function completeorderlist_imagedata(Request $request){
        $response = array();
    		$data = $request->input('data');
    		$encode_string = encode_string($data);
    		$content = json_decode($encode_string);
        
        $user_id = isset($content->user_id) ? $content->user_id : '';
        $search_text = isset($content->search_text) ? $content->search_text : '';
        $delivery_boy = isset($content->delivery_boy) ? $content->delivery_boy : '';
        $start_date = isset($content->start_date) ? $content->start_date : '';
        $end_date   = isset($content->end_date) ? $content->end_date : '';
        $logistic_id   = isset($content->logistic_id) ? $content->logistic_id : '';
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
        $complete = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        if (!empty($user_id) && !empty($search_text)) {
          $complete_list = new_order_history::select('u1.name','new_order_history.process_user_id','new_order_history.customer_id','new_order_history.deliver_datetime','new_order_history.order_status','new_order_history.order_number','new_order_history.created_at','new_order_history.deliveryboy_id','new_order_history.prescription_id','new_order_history.delivery_charges_id','new_order_history.order_id','new_order_history.id','new_order_history.assign_datetime','new_order_history.external_delivery_initiatedby','new_order_history.create_datetime','new_order_history.logistic_user_id')->where(['new_order_history.process_user_id' => $user_id, 'new_order_history.order_status' => 'complete'])->leftJoin('new_users as u1', 'u1.id', '=', 'new_order_history.customer_id')->where('u1.name', 'like', $search_text.'%')->orWhere('new_order_history.order_number', 'like', $search_text.'%')->orderBy('new_order_history.deliver_datetime', 'DESC');

            $total = $complete_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $complete_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
             
        }else if(!empty($user_id) && !empty($start_date) && !empty($end_date) && !empty($delivery_boy)) { 
             $complete_list= new_order_history::select('process_user_id','customer_id','deliver_datetime','order_status','order_number','created_at','deliveryboy_id','prescription_id','delivery_charges_id','order_id','id','assign_datetime','external_delivery_initiatedby','create_datetime','logistic_user_id')->whereBetween('created_at', [$start_date.' 00:00:00',$end_date.' 23:59:59'])->where(['process_user_id' => $user_id, 'order_status' => 'complete','deliveryboy_id'=> $delivery_boy])->orderBy('deliver_datetime', 'DESC');

            $total = $complete_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $complete_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
        }else if(!empty($user_id) && !empty($start_date) && !empty($end_date)) { 
            $complete_list= new_order_history::select('process_user_id','customer_id','deliver_datetime','order_status','order_number','created_at','deliveryboy_id','prescription_id','delivery_charges_id','order_id','id','assign_datetime','external_delivery_initiatedby','create_datetime','logistic_user_id')->whereBetween('created_at', [$start_date.' 00:00:00',$end_date.' 23:59:59'])->where(['process_user_id' => $user_id, 'order_status' => 'complete'])->orderBy('deliver_datetime', 'DESC');

            $total = $complete_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $complete_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
        }else if(!empty($user_id) && !empty($delivery_boy)){
            $complete_list = new_order_history::select('process_user_id','customer_id','deliver_datetime','order_status','order_number','created_at','deliveryboy_id','prescription_id','delivery_charges_id','order_id','id','assign_datetime','external_delivery_initiatedby','create_datetime','logistic_user_id')->where('deliveryboy_id', 'like', '%' .$search_text . '%')->where(['process_user_id' => $user_id, 'order_status' => 'complete','deliveryboy_id' => $delivery_boy])->orderBy('deliver_datetime', 'DESC');

            $total = $complete_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $complete_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
        }else if(!empty($user_id) && !empty($logistic_id)){
            $complete_list = new_order_history::select('process_user_id','customer_id','deliver_datetime','order_status','order_number','created_at','deliveryboy_id','prescription_id','delivery_charges_id','order_id','id','assign_datetime','external_delivery_initiatedby','create_datetime','logistic_user_id')->where('logistic_user_id', 'like', '%' .$search_text . '%')->where(['process_user_id' => $user_id, 'order_status' => 'complete','logistic_user_id' => $logistic_id])->orderBy('deliver_datetime', 'DESC');

            $total = $complete_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $complete_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
        }else{
               $complete_list =  new_order_history::select('process_user_id','customer_id','deliver_datetime','order_status','order_number','created_at','deliveryboy_id','prescription_id','delivery_charges_id','order_id','id','assign_datetime','external_delivery_initiatedby','create_datetime','logistic_user_id')->where('process_user_id', $user_id)->where('order_status','complete')->orderBy('deliver_datetime', 'DESC');

            $total = $complete_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $complete_list->paginate($per_page,'','',$page);
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
                                    $mobile =$user_data->mobile_number;
                                }else{
                                    $name = '';
                                    $mobile='';
                                }

                                $delivery_type_data = new_delivery_charges::where('id',$value['delivery_charges_id'])->first();
                                if(!empty($delivery_type_data)){
                                    $delivery_type =$delivery_type_data->delivery_type;
                                }else{
                                    $delivery_type = '';
                                }

                                $deliveryboy_name = new_pharma_logistic_employee::where('id',$value['deliveryboy_id'])->first();
                                if(!empty($deliveryboy_name)){
                                    $deliveryboy =$deliveryboy_name->name;
                                    $deliveryboy_mobile =$deliveryboy_name->mobile_number;
                                }else{
                                    $deliveryboy='';
                                    $deliveryboy_mobile='';
                                }
                                
                                $complete[] = [
                                    'order_id' => $value['order_id'],
                                    'order_number' => $value['order_number'],
                                    'prescription_image' => $mutiple_images,
                                    'customer_name' => $name,
                                    'customer_mobilenumber' =>$mobile,
                                    'deliveryboy_id' => $value['deliveryboy_id'],
                                    'deliveryboy_name' => $deliveryboy,
                                    'deliveryboy_mobilenumber' =>$deliveryboy_mobile,
                                    'order_assign_to' => $deliveryboy,
                                    'date' => ($value['assign_datetime'])?$value['assign_datetime']:'',
                                    'delivery_type' => $delivery_type,
                                    'delivered_date' => ($value['deliver_datetime'])?$value['deliver_datetime']:'',
                                    'external_delivery_initiatedby' => ($value['external_delivery_initiatedby'])?$value['external_delivery_initiatedby']:'',
                                    'order_time'=>($value['create_datetime'])?$value['create_datetime']:''
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'Completed Order List';
                } else {
                        $response['status'] = 404;
                        $response['message'] = 'Completed Order List';
                }
            }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
            }
        
        $response['data']->content = $complete;
        
        return decode_string($response, 200);
    }

    public function return_order_list_imagedata(Request $request){
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
        $return = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        if (!empty($user_id) && !empty($search_text)) {
          $return_list = new_orders::select('new_orders.process_user_id','new_orders.order_status','new_orders.customer_id','new_orders.order_number','new_orders.id','new_orders.prescription_id','new_orders.reject_cancel_reason','new_orders.external_delivery_initiatedby','new_orders.create_datetime','u1.name','new_orders.delivery_charges_id','new_orders.return_confirmtime','new_orders.logistic_user_id','new_orders.pharmacy_id')->where(['new_orders.process_user_id' => $user_id, 'new_orders.order_status' => 'incomplete'])->leftJoin('new_users as u1', 'u1.id', '=', 'new_orders.customer_id')->where('u1.name', 'like', $search_text.'%')->orWhere('new_orders.order_number', 'like', $search_text.'%')->orderBy('new_orders.id', 'DESC');

            $total = $return_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $return_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data']; 
        }else{
            $return_list =  new_orders::select('process_user_id','order_status','customer_id','order_number','id','prescription_id','reject_cancel_reason','external_delivery_initiatedby','create_datetime','delivery_charges_id','return_confirmtime','logistic_user_id','pharmacy_id')->where('process_user_id', $user_id)->where('order_status','incomplete')->orderBy('id', 'DESC');
            $total = $return_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $return_list->paginate($per_page,'','',$page);
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

                                $is_redelivery_data = new_pharmacies::where('id',$value['pharmacy_id'])->first();
                                   
                                        $return[] = [
                                        'order_id' => $value['id'],
                                        'order_number' => $value['order_number'],
                                        'delivery_type' => $delivery_type,
                                        'prescription_image' => $mutiple_images,
                                        'customer_name' => $name,
                                        'reason' => $value['reject_cancel_reason'],
                                        'return_confirmtime' => ($value['return_confirmtime'])?$value['return_confirmtime']:'',
                                        'delivery_charges_id' => $value['delivery_charges_id'],
                                        'is_redelivery' => $is_redelivery_data['is_redelivery'],
                                        'logistic_id' => $value['logistic_user_id'],
                                        'external_delivery_initiatedby' => ($value['external_delivery_initiatedby'])?$value['external_delivery_initiatedby']:'',
                                        'order_time'=>($value['create_datetime'])?$value['create_datetime']:''
                                    ];
                                }
                            $response['status'] = 200;
                            $response['message'] = 'Returned Order List';
                    } else {
                            $response['status'] = 404;
                            $response['message'] = 'Returned Order List';
                    }
             }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
             }
        
        $response['data']->content = $return;
        
        return decode_string($response, 200);
    }

    public function order_detail_imagedata(Request $request)
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
        $orders = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::select('id','api_token')->where(['id'=>$user_id,'api_token'=>$token])->first();
        if(!empty($user)){
                $order_details =  new_orders::select('id','prescription_id','customer_id','address_id','deliveryboy_id','delivery_charges_id','order_number','order_note','order_type','total_days','reminder_days','return_confirmtime','accept_datetime','reject_cancel_reason','reject_datetime','order_amount','cancel_datetime','created_at','order_status','external_delivery_initiatedby','create_datetime','pickup_datetime','deliver_datetime')->where('id' , $order_id)->orderBy('id', 'DESC')->first(); 
                $order_details_complete =  new_order_history::select('id','prescription_id','customer_id','address_id','deliveryboy_id','delivery_charges_id','order_number','order_note','order_type','total_days','reminder_days','return_confirmtime','accept_datetime','reject_cancel_reason','reject_datetime','order_amount','cancel_datetime','created_at','order_status','external_delivery_initiatedby','create_datetime','pickup_datetime','deliver_datetime')->where('order_id' , $order_id)->orderBy('order_id', 'DESC')->first();

                if(!empty($order_details)){
                                    $mutiple_data = multiple_prescription::where(['prescription_id'=>$order_details->prescription_id,'is_delete'=>'0'])->get();
										$mutiple_images = [];
										if(count($mutiple_data)>0){
												foreach ($mutiple_data as $mutiple) {
													$mutiple_images[]=[
													'id'	=> $mutiple->id,
													'image' => $mutiple->image,
												];	
											}
										} 
                                $invoice_images=[];
                                $invoice_data = invoice::where('order_id',$order_id)->get();
                                foreach ($invoice_data as $invoice) {
                                     $invoice_image = '';
                                        if (!empty($invoice->invoice)) {

                                            $filename = storage_path('app/public/uploads/invoice/' .  $invoice->invoice);
                                        
                                            if (File::exists($filename)) {
                                                $invoice_image = asset('storage/app/public/uploads/invoice/' .  $invoice->invoice);
                                            } else {
                                                $invoice_image = '';
                                            }
                                        }
                                    $invoice_images[] =[
                                        'id' => $invoice->id,
                                        'invoice' => $invoice_image
                                    ];
                                }

                               $user_data = new_users::where('id',$order_details->customer_id)->first();
                               if(!empty($user_data)){
                                    $name = $user_data->name;
                                    $mobile = $user_data->mobile_number;
                               }else{
                                    $name = '';
                                    $mobile = '';
                               }
                               
                               $address_data = new_address::where('id',$order_details->address_id)->first();
                               if(!empty($address_data)){
                                     $destination_address[] = [
                                        'address_id' => $address_data->id,
                                        'user_id' => $address_data->user_id,
                                        'locality'=>$address_data->locality,
                                        'address' =>$address_data->address,
                                        'name' => $address_data->name,
                                        'mobileno' =>$address_data->mobileno,
                                        'blockno' =>$address_data->blockno,
                                        'streetname'=>$address_data->streetname,
                                        'city'=>$address_data->city,
                                        'pincode'=>$address_data->pincode,
                                        'latitude'=>$address_data->latitude,
                                        'longitude'=>$address_data->longitude
                                    ];
                               }
                                   
                                $deliveryboy_name = new_pharma_logistic_employee::where('id',$order_details->deliveryboy_id)->first();
                                if(!empty($deliveryboy_name)){
                                     $deliveryboy = $deliveryboy_name->name;
                                }else{
                                    $deliveryboy ='';
                                }
                               
                                $delivery_type_data = new_delivery_charges::where('id',$order_details->delivery_charges_id)->first();
                                if(!empty($delivery_type_data)){
                                    $delivery_type = $delivery_type_data->delivery_type;
                                }else{
                                    $delivery_type = 'free';
                                }
                                
                                 $pickup_images=[];
                                 $pickup_date = '';
                                 if($order_details->order_status == 'pickup'){
                                     $pickup_data = new_order_images::where(['order_id'=>$order_id,'image_type'=>'pickup'])->get();
                                    foreach ($pickup_data as $pickup) {
                                            $pickup_image = '';
                                            if (!empty($pickup->image_name)) {

                                                $filename = storage_path('app/public/uploads/pickup/' .  $pickup->image_name);
                                            
                                                if (File::exists($filename)) {
                                                    $pickup_image = asset('storage/app/public/uploads/pickup/' .  $pickup->image_name);
                                                } else {
                                                    $pickup_image = '';
                                                }
                                            }
                                        $pickup_images[] =[
                                            'id' => $pickup->id,
                                            'pickup_image' => $pickup_image
                                        ];
                                    }
                                    $pickup_date = $order_details->pickup_datetime;
                                 }
                                
                                $delivered_images=[];
                                $deliver_date = '';
                                 if($order_details->order_status == 'complete'){  
                                     $deliver_data = new_order_images::where(['order_id'=>$order_id,'image_type'=>'deliver'])->get();
                                    foreach ($deliver_data as $deliver) {
                                            $deliver_image = '';
                                            if (!empty($deliver->image_name)) {

                                                $filename = storage_path('app/public/uploads/deliver/' .  $deliver->image_name);
                                            
                                                if (File::exists($filename)) {
                                                    $deliver_image = asset('storage/app/public/uploads/deliver/' .  $deliver->image_name);
                                                } else {
                                                    $deliver_image = '';
                                                }
                                            }
                                        $delivered_images[] =[
                                            'id' => $deliver->id,
                                            'deliver_image' => $deliver_image
                                        ];
                                    }
                                    $deliver_date = $order_details->deliver_datetime;
                                }
                              
                                    $orders[] = [
                                    'order_id' => $order_details->id,
                                    'order_number' => $order_details->order_number,
                                    'prescription_image' => $mutiple_images,
                                    'invoice'=> $invoice_images,
                                    'order_note' => $order_details->order_note,
                                    'order_type' => $order_details->order_type,
                                    'total_days' => ($order_details->total_days)?$order_details->total_days:'',
                                    'reminder_days' => ($order_details->reminder_days)?$order_details->reminder_days:'',
                                    'customer_name' => $name,
                                    'mobile_number' => $mobile,
                                    'return_confirmtime' => ($order_details->return_confirmtime)?$order_details->return_confirmtime:'',
                                    'location' => ($destination_address)?$destination_address:'',
                                    'order_assign_to' => $deliveryboy,
                                    'deliver_to' =>  $name,
                                    'accept_date' => ($order_details->accept_datetime)?$order_details->accept_datetime:'',
                                    'deliver_date' => $deliver_date,
                                    'assign_date' => ($order_details->assign_datetime)?$order_details->assign_datetime:'',
                                    'cancel_reason' => ($order_details->reject_cancel_reason) ? $order_details->reject_cancel_reason: '',
                                    'return_reason' => ($order_details->reject_cancel_reason) ? $order_details->reject_cancel_reason: '',
                                    'reject_reason' => ($order_details->reject_cancel_reason) ?$order_details->reject_cancel_reason: '',
                                    'return_date' => ($order_details->reject_datetime)?$order_details->reject_datetime:'',
                                    'order_amount' => ($order_details->order_amount)?$order_details->order_amount:'',
                                    'delivery_type' => $delivery_type,
                                    'pickup_images' => $pickup_images,
                                    'pickup_date' => $pickup_date,
                                    'drop_images' => $delivered_images,
                                    'drop_date' => $deliver_date,
                                    'reject_date' => ($order_details->reject_datetime)?$order_details->reject_datetime:'',
                                    'cancel_date' => ($order_details->cancel_datetime)?$order_details->cancel_datetime:'',
                                    'received_date' => (date_format($order_details->created_at,"Y-m-d H:i:s"))?(date_format($order_details->created_at,"Y-m-d H:i:s")):'',
                                    'order_status' => $order_details->order_status,
                                    'external_delivery_initiatedby' => ($order_details->external_delivery_initiatedby)?$order_details->external_delivery_initiatedby:'',
                                    'order_time'=>($order_details->create_datetime)?$order_details->create_datetime:''
                                ];
                        $response['status'] = 200;
                        $response['message'] = 'Order Details';
                } elseif (!empty($order_details_complete)) {
                               $mutiple_data = multiple_prescription::where(['prescription_id'=>$order_details_complete->prescription_id,'is_delete'=>'0'])->get();
										$mutiple_images = [];
										if(count($mutiple_data)>0){
												foreach ($mutiple_data as $mutiple) {
													$mutiple_images[]=[
													'id'	=> $mutiple->id,
													'image' => $mutiple->image,
												];	
											}
										} 
                                $invoice_images=[];
                                $invoice_data = invoice::where('order_id',$order_id)->get();
                                foreach ($invoice_data as $invoice) {
                                     $invoice_image = '';
                                        if (!empty($invoice->invoice)) {

                                            $filename = storage_path('app/public/uploads/invoice/' .  $invoice->invoice);
                                        
                                            if (File::exists($filename)) {
                                                $invoice_image = asset('storage/app/public/uploads/invoice/' .  $invoice->invoice);
                                            } else {
                                                $invoice_image = '';
                                            }
                                        }
                                    $invoice_images[] =[
                                        'id' => $invoice->id,
                                        'invoice' => $invoice_image
                                    ];
                                }

                              $user_data = new_users::where('id',$order_details_complete->customer_id)->first();
                               if(!empty($user_data)){
                                    $name = $user_data->name;
                                    $mobile = $user_data->mobile_number;
                               }else{
                                    $name = '';
                                    $mobile = '';
                               }
                               
                               $address_data = new_address::where('id',$order_details_complete->address_id)->first();
                               if(!empty($address_data)){
                                     $destination_address[] = [
                                        'address_id' => $address_data->id,
                                        'user_id' => $address_data->user_id,
                                        'locality'=>$address_data->locality,
                                        'address' =>$address_data->address,
                                        'name' => $address_data->name,
                                        'mobileno' =>$address_data->mobileno,
                                        'blockno' =>$address_data->blockno,
                                        'streetname'=>$address_data->streetname,
                                        'city'=>$address_data->city,
                                        'pincode'=>$address_data->pincode,
                                        'latitude'=>$address_data->latitude,
                                        'longitude'=>$address_data->longitude
                                    ];
                               }
                                   
                                $deliveryboy_name = new_pharma_logistic_employee::where('id',$order_details_complete->deliveryboy_id)->first();
                                if(!empty($deliveryboy_name)){
                                     $deliveryboy = $deliveryboy_name->name;
                                }else{
                                    $deliveryboy ='';
                                }
                               
                                $delivery_type_data = new_delivery_charges::where('id',$order_details_complete->delivery_charges_id)->first();
                                if(!empty($delivery_type_data)){
                                    $delivery_type = $delivery_type_data->delivery_type;
                                }else{
                                    $delivery_type = 'free';
                                }

                                $pickup_images=[];
                                $pickup_date = '';
                                if($order_details_complete->order_status == 'pickup'){
                                    $pickup_data = new_order_images::where(['order_id'=>$order_id,'image_type'=>'pickup'])->get();
                                    foreach ($pickup_data as $pickup) {
                                            $pickup_image = '';
                                            if (!empty($pickup->image_name)) {

                                                $filename = storage_path('app/public/uploads/pickup/' .  $pickup->image_name);
                                            
                                                if (File::exists($filename)) {
                                                    $pickup_image = asset('storage/app/public/uploads/pickup/' .  $pickup->image_name);
                                                } else {
                                                    $pickup_image = '';
                                                }
                                            }
                                            $pickup_images[] =[
                                                'id' => $pickup->id,
                                                'pickup_image' => $pickup_image
                                            ];
                                        }
                                    $pickup_date = $order_details_complete->pickup_datetime;
                                }
                                
                                $delivered_images=[];
                                $deliver_date = '';
                                 if($order_details_complete->order_status == 'complete'){  
                                     $deliver_data = new_order_images::where(['order_id'=>$order_id,'image_type'=>'deliver'])->get();
                                    foreach ($deliver_data as $deliver) {
                                            $deliver_image = '';
                                            if (!empty($deliver->image_name)) {

                                                $filename = storage_path('app/public/uploads/deliver/' .  $deliver->image_name);
                                            
                                                if (File::exists($filename)) {
                                                    $deliver_image = asset('storage/app/public/uploads/deliver/' .  $deliver->image_name);
                                                } else {
                                                    $deliver_image = '';
                                                }
                                            }
                                        $delivered_images[] =[
                                            'id' => $deliver->id,
                                            'deliver_image' => $deliver_image
                                        ];
                                    }
                                    $deliver_date = $order_details_complete->deliver_datetime;
                                }
                              
                                    $orders[] = [
                                    'order_id' => $order_details_complete->id,
                                    'order_number' => $order_details_complete->order_number,
                                    'prescription_image' => $mutiple_images,
                                    'invoice'=> $invoice_images,
                                    'order_note' => $order_details_complete->order_note,
                                    'order_type' => $order_details_complete->order_type,
                                    'total_days' => ($order_details_complete->total_days)?$order_details_complete->total_days:'',
                                    'reminder_days' => ($order_details_complete->reminder_days)?$order_details_complete->reminder_days:'',
                                    'customer_name' => $name,
                                    'mobile_number' => $mobile,
                                    'return_confirmtime' => ($order_details_complete->return_confirmtime)?$order_details_complete->return_confirmtime:'',
                                    'location' => ($destination_address)?$destination_address:'',
                                    'order_assign_to' => $deliveryboy,
                                    'deliver_to' =>  $name,
                                    'accept_date' => ($order_details_complete->accept_datetime)?$order_details_complete->accept_datetime:'',
                                    'deliver_date' => $deliver_date,
                                    'assign_date' => ($order_details_complete->assign_datetime)?$order_details_complete->assign_datetime:'',
                                    'cancel_reason' => ($order_details_complete->reject_cancel_reason) ? $order_details_complete->reject_cancel_reason: '',
                                    'return_reason' => ($order_details_complete->reject_cancel_reason) ? $order_details_complete->reject_cancel_reason: '',
                                    'reject_reason' => ($order_details_complete->reject_cancel_reason) ?$order_details_complete->reject_cancel_reason: '',
                                    'return_date' => ($order_details_complete->reject_datetime)?$order_details_complete->reject_datetime:'',
                                    'order_amount' => ($order_details_complete->order_amount)?$order_details_complete->order_amount:'',
                                    'delivery_type' => $delivery_type,
                                    'pickup_images' => $pickup_images,
                                    'pickup_date' => $pickup_date,
                                    'drop_images' => $delivered_images,
                                    'drop_date' => $deliver_date,
                                    'reject_date' => ($order_details_complete->reject_datetime)?$order_details_complete->reject_datetime:'',
                                    'cancel_date' => ($order_details_complete->cancel_datetime)?$order_details_complete->cancel_datetime:'',
                                    'received_date' => (date_format($order_details_complete->created_at,"Y-m-d H:i:s"))?(date_format($order_details_complete->created_at,"Y-m-d H:i:s")):'',
                                    'order_status' => $order_details_complete->order_status,
                                    'external_delivery_initiatedby' => ($order_details_complete->external_delivery_initiatedby)?$order_details_complete->external_delivery_initiatedby:'',
                                    'order_time'=>($order_details_complete->create_datetime)?$order_details_complete->create_datetime:''
                                ];
                        $response['status'] = 200;
                        $response['message'] = 'Order Details';
                } else {
                        $response['status'] = 404;
                }
            }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
            }
        
        $response['data'] = $orders;
        
        return decode_string($response, 200);
    }
}
