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
use App\SellerModel\new_orders;
use App\SellerModel\new_pharma_logistic_employee;
use App\DeliveryboyModel\new_order_images;
use App\DeliveryboyModel\new_order_history;
use App\SellerModel\new_users;
use App\new_logistics;
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

class AcceptorderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function checking_by(Request $request)
    {
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $check = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

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
        
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->first();
        if(!empty($user)){
                $check_data = new_orders::find($order_id);
                $check_data->checking_by = $user_id;
                $check_data->save();

                $user_data = new_pharma_logistic_employee::where('id',$user_id)->first();
                $check[] = [
                    'checking_by' => $user_data->name         
                ];

                $response['status'] = 200;
                $response['data'] = $check;
                $response['message'] = 'Checking By';
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
    public function order_list(Request $request)
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
            if(count($data_array)>0){
                foreach($data_array as $value) { 
                    $prescription_image = '';
                        $p_img = Prescription::where('id',$value['prescription_id'])->first();
                        if (!empty($p_img->image)) {

                            $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                        
                            if (File::exists($filename)) {
                                $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                            } else {
                                $prescription_image = '';
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
                                 'prescription_image' => $prescription_image,
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
    public function accept_order_list(Request $request)
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

        /*$token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){*/
                if(count($data_array)>0){
                         foreach($data_array as $value) {
                                    $prescription_image = '';
                                    $p_img = Prescription::where('id',$value['prescription_id'])->first();
                                    if (!empty($p_img->image)) {

                                        $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                    
                                        if (File::exists($filename)) {
                                            $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                        } else {
                                            $prescription_image = '';
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
                                    'prescription_image' => $prescription_image,
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
        /*}else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }*/

        $response['data']->content = $accept;
        
        return decode_string($response, 200);
    }

    public function deliveryboy_list(Request $request)
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
        $delivery_boy = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        if (!empty($user_id) && !empty($search_text)) {
             $seller = new_pharma_logistic_employee::select('id','pharma_logistic_id','user_type','is_active','profile_image','id','name','mobile_number','is_available')->where(['id'=>$user_id,'user_type'=>'seller','is_active'=>'1'])->first();

            $deliveryboy_list = new_pharma_logistic_employee::select('pharma_logistic_id','user_type','is_active','profile_image','id','name','mobile_number','is_available')->where('name', 'like', '%' .$search_text . '%')->where(['pharma_logistic_id'=>$seller->pharma_logistic_id,'user_type'=>'delivery_boy','is_active'=>'1']);

            $total = $deliveryboy_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $deliveryboy_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
            
        } elseif ($page == -1) { 
            
            $seller = new_pharma_logistic_employee::select('id','pharma_logistic_id','user_type','is_active','profile_image','id','name','mobile_number','is_available')->where(['id'=>$user_id,'user_type'=>'seller','is_active'=>'1'])->first();
            
            $deliveryboy_list = new_pharma_logistic_employee::select('pharma_logistic_id','user_type','is_active','profile_image','id','name','mobile_number','is_available')->where(['pharma_logistic_id'=>$seller->pharma_logistic_id,'user_type'=>'delivery_boy','is_active'=>'1'])->get();

        } else{
            
            $seller = new_pharma_logistic_employee::select('id','pharma_logistic_id','user_type','is_active','profile_image','id','name','mobile_number','is_available')->where(['id'=>$user_id,'user_type'=>'seller','is_active'=>'1'])->first();
            
            $deliveryboy_list = new_pharma_logistic_employee::select('pharma_logistic_id','user_type','is_active','profile_image','id','name','mobile_number','is_available')->where(['pharma_logistic_id'=>$seller->pharma_logistic_id,'user_type'=>'delivery_boy','is_active'=>'1']);
            
            $total = $deliveryboy_list->count();
            $page = $page;
            if($total > ($page*10)){
              $is_record_available = 1;
            }else{
              $is_record_available = 0;
            }
            $per_page = 10;
            $response['data']->currentPageIndex = $page;
            $response['data']->totalPage = ceil($total/$per_page);
            $orders = $deliveryboy_list->paginate($per_page,'','',$page);
            $data_array = $orders->toArray();
            $data_array = $data_array['data'];
        }

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                if(!empty($data_array)){
                    foreach($data_array as $value) {
                        $deliveryboy_image = '';
                        if($value['profile_image']!=''){
                            $destinationPath = base_path() . '/uploads/'.$value['profile_image'];
                            if(file_exists($destinationPath)){
                                $deliveryboy_image = url('/').'/uploads/'.$value['profile_image'];
                            }
                        }

                        $count = new_orders::select('process_user_id','deliveryboy_id','order_status')->where(['process_user_id'=>$user_id,'deliveryboy_id'=>$value['id'],'order_status'=>'assign'])->get();
                                    $delivery_boy[] = [
                                    'deliveryboy_id' => $value['id'],
                                    'deliveryboy_name' => $value['name'],
                                    'deliveryboy_image' => $deliveryboy_image,
                                    'mobile_number'=> $value['mobile_number'],
                                    'is_online'=> (string)$value['is_available'],
                                    'total_order'=> (count($count))?count($count):'0'
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'DeliveryBoy List';
                        $response['data']->content = $delivery_boy;
                } elseif (!empty($deliveryboy_list)) {
                        foreach($deliveryboy_list as $value) {
                        $deliveryboy_image = '';
                        if($value->profile_image!=''){
                            $destinationPath = base_path() . '/uploads/'.$value->profile_image;
                            if(file_exists($destinationPath)){
                                $deliveryboy_image = url('/').'/uploads/'.$value->profile_image;
                            }
                        }

                        $count = new_orders::select('process_user_id','deliveryboy_id','order_status')->where(['process_user_id'=>$user_id,'deliveryboy_id'=>$value->id,'order_status'=>'assign'])->get();
                                    $delivery_boy[] = [
                                    'deliveryboy_id' => $value->id,
                                    'deliveryboy_name' => $value->name,
                                    'deliveryboy_image' => $deliveryboy_image,
                                    'mobile_number'=> $value->mobile_number,
                                    'is_online'=> (string)$value->is_available,
                                    'total_order'=> (count($count))?count($count):'0'
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'DeliveryBoy List';
                        $response['data'] = $delivery_boy;
                }else {
                        $response['status'] = 404;
                        $response['data']->content = $delivery_boy;
                }
         }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
         }
        
        return decode_string($response, 200);
    }

    
    public function outof_order_list(Request $request){
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                if(!empty($data_array)){
                         foreach($data_array as $value) {
                                    $prescription_image = '';
                                    $p_img = Prescription::where('id',$value['prescription_id'])->first();
                                    if (!empty($p_img->image)) {
                                        $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                        if (File::exists($filename)) {
                                            $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                        } else {
                                            $prescription_image = '';
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
                                    'prescription_image' => $prescription_image,
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


   public function reject_order_list(Request $request){
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                    if(count($data_array)>0){
                             foreach($data_array as $value) {
                                        $prescription_image = '';
                                        $p_img = Prescription::where('id',$value['prescription_id'])->first();
                                        if (!empty($p_img->image)) {

                                            $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                        
                                            if (File::exists($filename)) {
                                                $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                            } else {
                                                $prescription_image = '';
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
                                    'prescription_image' => $prescription_image,
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
     public function cancel_order_list(Request $request){
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                if(count($data_array)>0){
                         foreach($data_array as $value) {
                                    $prescription_image = '';
                                    $p_img = Prescription::where('id',$value['prescription_id'])->first();
                                    if (!empty($p_img->image)) {

                                        $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                    
                                        if (File::exists($filename)) {
                                            $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                        } else {
                                            $prescription_image = '';
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
                                    'prescription_image' => $prescription_image,
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

    public function complete_order_list(Request $request){
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                if(count($data_array)>0){
                         foreach($data_array as $value) {
                                    $prescription_image = '';
                                    $image_list = Prescription::where('id',$value['prescription_id'])->first();
                                    if (!empty($p_img->image)) {

                                        $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                    
                                        if (File::exists($filename)) {
                                            $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                        } else {
                                            $prescription_image = '';
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
                                    'prescription_image' => $prescription_image,
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

     public function invoice(Request $request){
        
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $order_amount = isset($content->order_amount) ? $content->order_amount : '';
        $invoice = isset($content->invoice) ? $content->invoice : '';
        
        $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'order_amount' => $order_amount,
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'order_amount' => 'required',   
        ]);
        
        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                $orders = new_orders::where('id',$order_id)->first();
                if(!empty($orders)){
                        $data= new_orders::where(['process_user_id' => $user_id,'id'=>$order_id])->get();
                        $destinationPath = 'storage/app/public/uploads/invoice/'; 
                        if($files=$request->file('invoice')){
                            foreach($files as $key => $file){
                                $filename = time().'-'.$file->getClientOriginalName();
                                $tesw = $file->move($destinationPath, $filename);
                                $invoice_data = new invoice();
                                $invoice_data->order_id = $order_id;
                                $invoice_data->invoice = $filename;
                                $invoice_data->created_at = date('Y-m-d H:i:s');
                                $invoice_data->updated_at = date('Y-m-d H:i:s');
                                $invoice_data->save();
                            }
                        }
                        $invoice_path= new_orders::find($order_id);
                        $invoice_path->order_amount = $order_amount;
                        $invoice_path->save();

                       //send sms to user
                         $mobile_data = new_users::where('id',$orders->customer_id)->first();
                        $pharmacy_data = new_pharmacies::where('id',$orders->pharmacy_id)->first();
                        $message       = 'Dear Customer '.$mobile_data->name.
                                         ', Thank you for ordering your medicine with '.$pharmacy_data->name.
                                         '. Your order '.$orders->order_number.' has been confirmed and your order amount is '.$order_amount.'.';
                        $api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_data->mobile_number."'&message=" . urlencode($message);
                        $sms = file_get_contents($api);

                         //send mail to user
                        $data = [
                          'name' => $mobile_data->name,
                          'pharmacy_name'=>$pharmacy_data->name,
                          'order_number'=>$orders->order_number,
                          'order_amount'=>$order_amount
                        ];
                        $email = $mobile_data->email;
                        $result = Mail::send('email.generateorder', $data, function ($message) use ($email) {
                            $message->to($email)->subject('Pharma - Thank You For Order');
                        }); 

                        $response['status'] = 200;
                        $response['message'] = 'Invoice Uploaded';
                }
            }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
            }
        return decode_string($response, 200);
    }

    public function call_history(Request $request){
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        
        $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
        $timestamp = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
            $calls= Callhistory::where(['user_id' => $user_id,'order_id'=>$order_id])->get();
            if(!empty($calls)){
                foreach($calls as $value) {
                    $timestamp[] = [
                        'current_timestamp' => $value->date_time
                    ];
                }
                $response['status'] = 200;
                $response['message'] = 'Current Timestamp';
            } else {
                $response['status'] = 404;
            }
        }else{
            $response['status'] = 401;
            $response['message'] = 'Unauthenticated';
        }
        $response['data'] = $timestamp;
        return decode_string($response, 200);
    }
    public function accept_upcoming(Request $request){
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $accept = [];
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

        $order_id = isset($content->order_id) ? $content->order_id : '';
        $user_id = isset($content->user_id) ? $content->user_id : '';
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
        $logistic_data = [];
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->first();
        if(!empty($user)){
        	if($user_id > 0){
				$ids = array();
				$order_data = new_orders::where('id',$order_id)->first();
				if(!empty($order_data)){
					$customerdetail =  new_users::where('id',$order_data->customer_id)->first();
					if($customerdetail->fcm_token!=''){
						$ids[] = $customerdetail->fcm_token;
					}
					$msg = array
					(
						'body'   => ' Order number '. $order_data->order_number,
						'title'     => 'Your Order Accepted'
					);
					// if(count($ids)>0){
						// $fields = array(
							// 'to' => $customerdetail->fcm_token,
							// 'notification' => $msg
						// );
						// $this->sendPushNotification($fields);   
					// }
					if (count($ids) > 0) {					
						Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Your Order Accepted', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
					}
					$notification = new notification_user();
					$notification->user_id=$customerdetail->id;
					$notification->order_id=$order_data->id;
					$notification->subtitle=$msg['body'];
					$notification->title=$msg['title'];
					$notification->created_at=date('Y-m-d H:i:s');
					$notification->save();
				}
			}

            $orders = new_orders::where('id',$order_id)->get();
            if(count($orders)>0){
				foreach ($orders as $order) {
					if($order->process_user_id == 0){
						$order->process_user_id = $user_id;
						$order->process_user_type = 'seller';
						$order->accept_datetime = date('Y-m-d H:i:s');
						$order->order_status = 'accept';
						if($order->is_external_delivery == 1){
							if($order->logistic_user_id > 0){
								$order_data = new_orders::where('id',$order_id)->first();
								//mail when order transfer to logistic
								if(!empty($order_data)){
									$email_data = new_logistics::where('id',$order->logistic_user_id)->first();
									$data = [
										'name' => $email_data->name,
										'orderno'=>$order_data->order_number
									];
									$email = $email_data->email;
									$result = Mail::send('email.upcoming', $data, function ($message) use ($email) {
											$message->to($email)->subject('Pharma - Upcoming Order');
									}); 
								}
								$this->AssignOrderOutsideLogistic($order_id,$order);
							} elseif ($order->logistic_user_id < 0) {
								$intersected = $this->AssignOrderToIntersectedLogistic();
								//$check_intersected_array =  $intersected->toArray();
								if(count($intersected) == 1){
									if(strtoupper($intersected[0]->name) == "ELT"){
										//order update in our db
										$orderAssign = new Orderassign();
										$orderAssign->order_id = $order_id;
										$orderAssign->logistic_id = $intersected[0]->id;
										$orderAssign->order_status = 'new';
										$orderAssign->updated_at = date('Y-m-d H:i:s');
										$orderAssign->save();
												
										$order->assign_datetime = date('Y-m-d H:i:s');
										$order->order_status = 'assign';
										$order->logistic_user_id = $intersected[0]->id;

										$order_data = new_orders::where('id',$order_id)->first();
										//mail when order transfer to logistic
										if(!empty($order_data)){
											$email_data = new_logistics::where('id',$order->logistic_user_id)->first();
											$data = [
												'name' => $email_data->name,
												'orderno'=>$order_data->order_number
											];
											$email = $email_data->email;
											$result = Mail::send('email.upcoming', $data, function ($message) use ($email) {
													$message->to($email)->subject('Pharma - Upcoming Order');
											}); 
										}
										$assignOrderEmit = (object)[];
										$assignOrderEmit->pharmacy_id = $order->pharmacy_id;
										$assignOrderEmit->logistic_id = $order->logistic_user_id;
										$image_url = url('/').'/uploads/placeholder.png';

										if (!empty($order->prescription_image)) {
											if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
												$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
											}
										}

										$assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
										$assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
										$assignOrderEmit->order_number = $order->order_number;
										$assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
										$assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
										$assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
										$assignOrderEmit->order_amount = $order->order_amount;
										$assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

										event(new AssignOrderLogistic($assignOrderEmit));

										//order process with external logistic
										$elt_response = [];
										if(count($orders)>0){
											$pharmacy = new_pharmacies::where('id',$orders[0]->pharmacy_id)->first();
											$address = new_address::where('id',$orders[0]->address_id)->first();
											$elt_response[] = [
												'api_key' => 'b623d7c412d5cb593c577b1016eae93c',
												'pickup_latitude' => $pharmacy->lat,
												'pickup_longitude' => $pharmacy->lon,
												'pickup_name' => $pharmacy->name,
												'pickup_contact' => $pharmacy->mobile_number,
												'pickup_address' => $pharmacy->address,
												'pickup_city' => $pharmacy->city,
												'delivery_latitude' => $address->latitude,
												'delivery_longitude' => $address->longitude,
												'delivery_name' => $address->name,
												'delivery_contact' => $address->mobileno,
												'delivery_address' => $address->address,
												'delivery_city' => $address->city,
												'order_amount' => '50',
												'no_of_parcle' => '1',
												'order_id' => $orders[0]->id,
												'order_time'=> $orders[0]->create_datetime,
											];
										}
										$client = new Client;
										$r = $client->post('https://developer.eltapp.in/v3/order_schedule/create', 
										['form-data' => [
											"response" => $elt_response
										]]);
										//dd($r->getBody()->getContents());die;
										$logistic_data[] = [
											'logistic_current_status' => 'true',
											'next_logistic_working_day' => ''
										];
										$response['data'] = $logistic_data;
									}else{
										//order process with internal logistic
										$orderAssign = new Orderassign();
										$orderAssign->order_id = $order_id;
										$orderAssign->logistic_id = $intersected[0]->id;
										$orderAssign->order_status = 'new';
										$orderAssign->updated_at = date('Y-m-d H:i:s');
										$orderAssign->save();
												
										$order->assign_datetime = date('Y-m-d H:i:s');
										$order->order_status = 'assign';
										$order->logistic_user_id = $intersected[0]->id;

										$order_data = new_orders::where('id',$order_id)->first();
										//mail when order transfer to logistic
										if(!empty($order_data)){
											$email_data = new_logistics::where('id',$order->logistic_user_id)->first();
											$data = [
												'name' => $email_data->name,
												'orderno'=>$order_data->order_number
											];
											$email = $email_data->email;
											$result = Mail::send('email.upcoming', $data, function ($message) use ($email) {
													$message->to($email)->subject('Pharma - Upcoming Order');
											}); 
										}
										$assignOrderEmit = (object)[];
										$assignOrderEmit->pharmacy_id = $order->pharmacy_id;
										$assignOrderEmit->logistic_id = $order->logistic_user_id;
										$image_url = url('/').'/uploads/placeholder.png';

										if (!empty($order->prescription_image)) {
											if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
												$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
											}
										}

										$assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
										$assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
										$assignOrderEmit->order_number = $order->order_number;
										$assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
										$assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
										$assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
										$assignOrderEmit->order_amount = $order->order_amount;
										$assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

										event(new AssignOrderLogistic($assignOrderEmit));
										$logistic_data[] = [
											'logistic_current_status' => 'true',
											'next_logistic_working_day' => ''
										];
										$response['data'] = $logistic_data;
									}
								}else{
									//next day order process with first priority logistic
									foreach ($intersected as $value) {
										$orderAssign = new Orderassign();
										$orderAssign->order_id = $order_id;
										$orderAssign->logistic_id = $value->id;
										$orderAssign->order_status = 'new';
										$orderAssign->updated_at = date('Y-m-d H:i:s');
										$orderAssign->save();
												
										$order->assign_datetime = date('Y-m-d H:i:s');
										$order->order_status = 'assign';
										$order->logistic_user_id = $value->id;

										$assignOrderEmit = (object)[];
										$assignOrderEmit->pharmacy_id = $order->pharmacy_id;
										$assignOrderEmit->logistic_id = $order->logistic_user_id;
										$image_url = url('/').'/uploads/placeholder.png';

										if (!empty($order->prescription_image)) {
											if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
												$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
											}
										}

										$assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
										$assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
										$assignOrderEmit->order_number = $order->order_number;
										$assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
										$assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
										$assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
										$assignOrderEmit->order_amount = $order->order_amount;
										$assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

										event(new AssignOrderLogistic($assignOrderEmit));
									}
									$logistic_data[] = [
										'logistic_current_status' => 'false',
										'next_logistic_working_day' => $this->GetNextDayLogic()
									];
									$response['data'] = $logistic_data;
								}
							} 
						} else {
							$order->order_status='accept';
						}
							
						$order->save();
						$response['status'] = 200;
						$response['message'] = 'Accept Order Successfully';
					} else {
						$response['status'] = 404;
						$response['message'] = 'This Order Is Already Accepted';
					}
				}                    
            } else {
                $response['status'] = 404;
                $response['message'] = 'This order was already cancelled';
            }
        } else {
			$response['status'] = 401;
			$response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
    public function AssignOrderOutsideLogistic($order_id,$order)
    {
    	$orderAssign = new Orderassign();
        $orderAssign->order_id = $order_id;
        $orderAssign->logistic_id = $order->logistic_user_id;
        $orderAssign->order_status = 'new';
        $orderAssign->updated_at = date('Y-m-d H:i:s');
        $orderAssign->save();
        $re_assign_data = new_orders::where('id',$order_id)->first();
        if($re_assign_data->order_status=="incomplete"){
                $new_order_images = new_order_images::where('order_id',$order_id)->delete();
        }       
        $order->assign_datetime = date('Y-m-d H:i:s');
        $order->order_status = 'assign';
       
        
        $assignOrderEmit = (object)[];
        $assignOrderEmit->pharmacy_id = $order->pharmacy_id;
        $assignOrderEmit->logistic_id = $order->logistic_user_id;

        $image_url = url('/').'/uploads/placeholder.png';

        if (!empty($order->prescription_image)) {
            if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
                $image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
            }
        }

        $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
        $assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
        $assignOrderEmit->order_number = $order->order_number;
        $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
        $assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
        $assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
        $assignOrderEmit->order_amount = $order->order_amount;
        $assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

        event(new AssignOrderLogistic($assignOrderEmit));

         //order pass to external logistic
               /* $elt_response = [];
                if(count($orders)>0){
                    $pharmacy = new_pharmacies::where('id',$orders[0]->pharmacy_id)->first();
                    $address = new_address::where('id',$orders[0]->address_id)->first();
                    $elt_response[] = [
                        'pickup_latitude' => $pharmacy->lat,
                        'pickup_longitude' => $pharmacy->lon,
                        'pickup_name' => $pharmacy->name,
                        'pickup_contact' => $pharmacy->mobile_number,
                        'pickup_address' => $pharmacy->address,
                        'pickup_city' => $pharmacy->city,
                        'delivery_latitude' => $address->latitude,
                        'delivery_longitude' => $address->longitude,
                        'delivery_name' => $address->name,
                        'delivery_contact' => $address->mobileno,
                        'delivery_address' => $address->address,
                        'delivery_city' => $address->city,
                        'order_amount' => $orders[0]->order_amount,
                        'order_id' => $orders[0]->id,
                    ];
                }
                $client = new Client;
                $r = $client->post('https://developer.eltapp.in/v3/orders/create', 
                ['json' => [
                    "response" => $elt_response
                ]]);*/
    }
    public function AssignOrderToIntersectedLogistic()
    {
        $current_time = date('H:i:s');
    	$logistic_active = new_logistics::where('is_active','1')->where('close_time','>=',$current_time)->orderBy('priority','ASC')->get();
        $logistics = [];
        foreach ($logistic_active as $current_logistic) {
		        $logistic = $current_logistic;
                array_push($logistics,$logistic);
                return $logistics;
	    }
	    if($logistics == []){
	       //response to seller order deliver tom.
            $logistic_priorities = [];
            $logistic_priority = new_logistics::where('is_active','1')->orderBy('priority','ASC')->first();
            array_push($logistic_priorities,$logistic_priority);
            return $logistic_priorities;
	    }
    }
    
    public function GetNextDayLogic(){
		return date('Y-m-d',strtotime("+1 day"));
	}
     public function assign_order(Request $request){
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $deliveryboy_id = isset($content->deliveryboy_id) ? $content->deliveryboy_id : '';

        $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'deliveryboy_id' => $deliveryboy_id,
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->first();
        if(!empty($user)){
             $dupicate_data = new_orders::where('id',$order_id)->first();
            $dupicate_data->deliveryboy_id = 0;
            $dupicate_data->save();
			$assign = new_orders::find($order_id);
			if(!empty($assign)){
				if($assign->deliveryboy_id == 0){
					$assign->order_status='assign';
					$assign->assign_datetime=date('Y-m-d H:i:s');

					$order_assign = new Orderassign();
					$order_assign->order_id=$order_id;
					$order_assign->order_status='assign';
					$order_assign->created_at = date('Y-m-d H:i:s');
					$order_assign->updated_at = date('Y-m-d H:i:s');
							
					$deliveryboy_active = new_pharma_logistic_employee::where(['is_available'=>'1','id'=>$deliveryboy_id])->first();
					if(!empty($deliveryboy_active)){
						if(!empty($deliveryboy_id)){
							$assign->deliveryboy_id=$deliveryboy_id;    
							$order_assign->deliveryboy_id=$deliveryboy_id;
						}
						$order_assign->assign_date=date('Y-m-d H:i:s');
						$assign->save();
						if($order_assign->save()){
                                if($user_id > 0){
                                $ids = array();
                                $order_data = new_orders::where('id',$order_id)->first();
                                $deliveryboydetail =  new_pharma_logistic_employee::where('id',$order_data->deliveryboy_id)->first();
                                    if($deliveryboydetail->fcm_token!=''){
                                        $ids[] = $deliveryboydetail->fcm_token;
                                    }
                                 $delivery_address =  new_address::where('id',$order_data->address_id)->first();
                                 $msg = array
                                (
                                    'body'   => ' Order number '.$order_data->order_number,
                                    'title'     => 'Order Assigned'
                                );
                                // if(count($ids)>0){
                                    // $fields = array(
                                        // 'to' => $deliveryboydetail->fcm_token,
                                        // 'notification' => $msg
                                    // );
                                    // $this->sendPushNotificationDeliveryboy($fields);   
                                // }
                                if (count($ids) > 0) {                  
                                    Helper::sendNotificationDeliveryboy($ids, 'Order number '.$order_data->order_number, 'Order Assigned', $user->id, 'seller', $deliveryboydetail->id, 'delivery_boy', $deliveryboydetail->fcm_token);
                                }
                    
                                $notification = new notification_deliveryboy();
                                $notification->user_id=$deliveryboydetail->id;
                                $notification->order_id=$order_data->id;
                                $notification->subtitle=$msg['body'];
                                $notification->title=$msg['title'];
                                $notification->created_at=date('Y-m-d H:i:s');
                                $notification->save();
                            }
                        }
						
						$response['status'] = 200;
						$response['message'] = 'Assign Order Successfully';      
					}else{
						$response['status'] = 404;
						$response['message'] = 'Deliveryboy is not available';
					}
				}else{
					$response['status'] = 404;
					$response['message'] = 'Order already assigned';   
				}
					
			}else{
					$response['status'] = 404;
					$response['message'] = 'This order was already cancelled';
			}
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
    public function reject_upcoming(Request $request){
		
		$response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $rejectreason_id = isset($content->rejectreason_id) ? $content->rejectreason_id : '';

        $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'rejectreason_id' => $rejectreason_id
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'rejectreason_id' => 'required'
            
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->first();
        if(!empty($user)){
			$reject = new_orders::find($order_id);
			if(!empty($reject)){
				$reject->process_user_id=$user_id;
				$reject->reject_user_id=$user_id;
				$reject->rejectby_user='seller';
				$reject->order_status='reject';
				$reject->reject_cancel_reason=$rejectreason_id;
				$reject->reject_datetime = date('Y-m-d H:i:s');
				if($reject->save()){
                    if($user_id > 0){
                $ids = array();
                $order_data = new_orders::where('id',$order_id)->first();
                if(!empty($order_data)){
                     $customerdetail =  new_users::where('id',$order_data->customer_id)->first();
                     if(!empty($customerdetail)){
                        if($customerdetail->fcm_token!=''){
                            $ids[] = $customerdetail->fcm_token;
                        }
                        
                        $msg = array
                        (
                            'body'   => ' Order number '. $order_data->order_number,
                            'title'     => 'Your Order Rejected '
                        );
                        // if(count($ids)>0){
                            // $fields = array(
                                // 'to' => $customerdetail->fcm_token,
                                // 'notification' => $msg
                            // );
                            // $this->sendPushNotification($fields);   
                        // }
                        
                        if (count($ids) > 0) {                  
                            Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Your Order Rejected', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                        }
                        
                        $notification = new notification_user();
                        $notification->user_id=$customerdetail->id;
                        $notification->order_id=$order_data->id;
                        $notification->subtitle=$msg['body'];
                        $notification->title=$msg['title'];
                        $notification->created_at=date('Y-m-d H:i:s');
                        $notification->save();
                    }
                }
            }
                }
				$response['status'] = 200;
				$response['message'] = 'Reject Order';
			}else{
				$response['status'] = 404;
				$response['message'] = 'This order was already cancelled';
			}
                
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
    public function reject_order(Request $request){
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $rejectreason_id = isset($content->rejectreason_id) ? $content->rejectreason_id : '';

        $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'rejectreason_id' => $rejectreason_id
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'rejectreason_id' => 'required'
            
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->first();
        if(!empty($user)){
                $reject = new_orders::find($order_id);
                if(!empty($reject)){
                    $reject->order_status='reject';
                    $reject->reject_user_id=$user_id;
                    $reject->rejectby_user='seller';
                    $reject->reject_cancel_reason=$rejectreason_id;
                    $reject->reject_datetime = date('Y-m-d H:i:s');
                    $reject->save();
                    $response['status'] = 200;
                    $response['message'] = 'Reject Order';
                }else{
                    $response['status'] = 404;
                    $response['message'] = 'This order was already cancelled';
                }
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }
    
     public function reason_list(Request $request){
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $reasons=[];
        $data = Rejectreason::all();
        if(!empty($data)){
                 foreach($data as $value) {
                    $reasons[] = [
                            'id' => $value->id,
                            'reason' => $value->reason
                        ];
                    }
                $response['status'] = 200;
                $response['message'] = 'Reasons';
        } else {
                $response['status'] = 404;
        }
        $response['data'] = $reasons;
        return decode_string($response, 200);
    }

     public function add_time(Request $request){
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $date_time = isset($content->date_time) ? $content->date_time : '';

         $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'date_time' => $date_time
        ];

         $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'date_time'=> 'required'
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }
        $time=[];
        $token =  $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
            $data = new Callhistory();
            $data->user_id=$user_id;
            $data->order_id=$order_id;
            $data->date_time=$date_time;
            $data->save();
            $response['status'] = 200;
            $response['message'] = 'Time added';
        }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
        }
        return decode_string($response, 200);
    }

    public function return_order_list(Request $request){
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                    if(count($data_array)>0){
                             foreach($data_array as $value) {
                                        $prescription_image = '';
                                        $p_img = Prescription::where('id',$value['prescription_id'])->first();
                                        if (!empty($p_img->image)) {

                                            $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                        
                                            if (File::exists($filename)) {
                                                $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                            } else {
                                                $prescription_image = '';
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
                                        'prescription_image' => $prescription_image,
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

    public function delivery_charges_list(Request $request){

        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
        

        $order_id = isset($content->order_id) ? $content->order_id : '';

        $params = [
            'order_id' => $order_id
        ];
        
        $validator = Validator::make($params, [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();
        $logistic_id = 0;
        $upcoming_order_details =  new_orders::where('id',$order_id)->get()->first();

        if(!empty($upcoming_order_details)){
            $delivery_data = new_address::where('id',$upcoming_order_details->address_id)->get()->first();
            $delivery_location = array($delivery_data->latitude, $delivery_data->longitude);

            $pickup_data = new_pharmacies::where('id',$upcoming_order_details->pharmacy_id)->get()->first();
            $pickup_location = array($pickup_data->lat, $pickup_data->lon);

            $logistics = new_logistics::select('new_logistics.*')
            /*->where('new_users.user_type', '=', 'logistic')*/
            ->where('new_logistics.city', '=', $pickup_data->city)->get();

            if(count($logistics)){
                if(count($logistics)>0){
                    $logistic = $this->getLogisticList($logistics, $pickup_location);
                    if(count($logistic) > 0){
                        $result = $this->pharamcyGeofenceCheck($logistic, $delivery_location);
                        if($result[0] == 'true'){
                            $logisticValue = $result[1];
                        }
                    }
                }
            }
            if(isset($logisticValue)){
                $logistic_id = $logisticValue->id;
            }
        }
        //old code
        /*$delivery_charges_data = new_delivery_charges::where('logistic_id', $logistic_id)->orWhere('logistic_id', 0)->orderBy('logistic_id', 'ASC')->get();*/
        $delivery_charges_data = new_delivery_charges::all();
        $logistic_get_data = new_orders::where('id',$order_id)->first();

        // $delivery_charges_data = delivery_charges::all();
        if(!empty($delivery_charges_data)){
            foreach($delivery_charges_data as $value) {
                if($value->is_user == 0){
                        $delivery_charges[] = [
                        'id' => $value->id,
                        'delivery_type' => $value->delivery_type,
                        'delivery_price' => $value->delivery_price,
                        'delivery_approx_time' => $value->delivery_approx_time,
                        'logistic_id' => $logistic_get_data->logistic_user_id,
                    ];
                }
            }
            $response['status'] = 200;
            $response['message'] = 'Delivery List';
        }else{
            $response['status'] = 404;
        }
        $response['data'] = $delivery_charges;
        return decode_string($response, 200);
    }

    public function set_delivery_charges(Request $request){
        
        $response = array();
		$data = $request->input('data');
		$encode_string = encode_string($data);
		$content = json_decode($encode_string);
        
        $user_id = isset($content->user_id) ? $content->user_id : '';
        $order_id = isset($content->order_id) ? $content->order_id : '';
        $delivery_charges_id = isset($content->delivery_charges_id) ? $content->delivery_charges_id : '';
        $logistic_id = isset($content->logistic_id) ? $content->logistic_id : '';
        
        $params = [
            'user_id' => $user_id,
            'order_id' => $order_id,
            'logistic_id' => $logistic_id,
            'delivery_charges_id' => $delivery_charges_id
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'logistic_id' => 'required',
            'delivery_charges_id' => 'required'
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
            $orders = new_orders::where('id',$order_id)->get();
            if(count($orders)>0){
              // remining_paid_deliveries
               $delivery_charges_data = new_delivery_charges::where('id',$delivery_charges_id)->first();
               $check_data = new_orders::where('id',$order_id)->first();
                      if($delivery_charges_data->delivery_type == 'express'){
                          $phar_data = new_pharmacies::where('id',$check_data->pharmacy_id)->first();
                          $remining_express_paid_deliveries = $phar_data->remining_express_paid_deliveries;
                          if($remining_express_paid_deliveries != -5){
                              $phar_data->remining_express_paid_deliveries = $remining_express_paid_deliveries - 1;
                              $phar_data->save();
                              foreach ($orders as $order) {
                        if($order->process_user_id == 0 || $order->order_status == "incomplete"){
                        $order->is_external_delivery = 1;
                        $order->delivery_charges_id = $delivery_charges_id;
                        $order->external_delivery_initiatedby = 'seller';
                        $order->process_user_id = $user_id;
                        $order->process_user_type = 'seller';
                        $order->accept_datetime = date('Y-m-d H:i:s');
                        $order->order_status = 'accept';
                        if($order->logistic_user_id > 0){
                            $order_data = new_orders::where('id',$order_id)->first();
                            if($order_data->order_status == "incomplete"){
                                    if(!empty($order_data->logistic_user_id)){
                                        $email_data = new_logistics::where('id',$order_data->logistic_user_id)->first();
                                        $data = [
                                            'name' => $email_data->name,
                                            'orderno'=>$order_data->order_number
                                        ];
                                        $email = $email_data->email;
                                        $result = Mail::send('email.redeliver', $data, function ($message) use ($email) {
                                                $message->to($email)->subject('Pharma - Redeliver Order');
                                        }); 
                                }
                            }
                            $this->AssignOrderOutsideLogistic($order_id,$order);
                            //notification to user order accept
                            if($user_id > 0){
                            $ids = array();
                            $order_data = new_orders::where('id',$order_id)->first();
                            $customerdetails = new_users::where('id',$order_data->customer_id)->get();
                                foreach ($customerdetails as $customerdetail) {
                                    if($customerdetail->fcm_token!=''){
                                                $ids[] = $customerdetail->fcm_token;
                                            }
                                            $msg = array
                                            (
                                                'body'   => ' Order Accept '. $order_data->order_number,
                                                'title'     => 'Order Accepted'
                                            );
                                            // if(count($ids)>0){
                        // $fields = array(
                          // 'to' => $customerdetail->fcm_token,
                          // 'notification' => $msg
                        // );
                        // $this->sendPushNotification($fields);   
                                            // }
                      
                      if (count($ids) > 0) {          
                        Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Order Accepted', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                      }
                                            $notification = new notification_user();
                                            $notification->user_id=$customerdetail->id;
                                            $notification->order_id=$order_data->id;
                                            $notification->subtitle=$msg['body'];
                                            $notification->title=$msg['title'];
                                            $notification->created_at=date('Y-m-d H:i:s');
                                            $notification->save();
                                    }             
                            }
                        } elseif ($order->logistic_user_id < 0) {
                            $intersected = $this->AssignOrderToIntersectedLogistic();
                            if(count($intersected) == 1){
                                    if(strtoupper($intersected[0]->name) == "ELT"){
                                    //order update in our db
                                    $orderAssign = new Orderassign();
                                    $orderAssign->order_id = $order_id;
                                    $orderAssign->logistic_id = $intersected[0]->id;
                                    $orderAssign->order_status = 'new';
                                    $orderAssign->updated_at = date('Y-m-d H:i:s');
                                    $orderAssign->save();
                                            
                                    $order->assign_datetime = date('Y-m-d H:i:s');
                                    $order->order_status = 'assign';
                                    $order->logistic_user_id = $intersected[0]->id;

                                    $assignOrderEmit = (object)[];
                                    $assignOrderEmit->pharmacy_id = $order->pharmacy_id;
                                    $assignOrderEmit->logistic_id = $order->logistic_user_id;
                                    $image_url = url('/').'/uploads/placeholder.png';

                                    if (!empty($order->prescription_image)) {
                                        if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
                                            $image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
                                        }
                                    }

                                    $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
                                    $assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
                                    $assignOrderEmit->order_number = $order->order_number;
                                    $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                                    $assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
                                    $assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
                                    $assignOrderEmit->order_amount = $order->order_amount; 
                                    $assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

                                    event(new AssignOrderLogistic($assignOrderEmit));

                                    //order process with external logistic
                                         $elt_response = [];
                                        if(count($orders)>0){
                                            $pharmacy = new_pharmacies::where('id',$orders[0]->pharmacy_id)->first();
                                            $address = new_address::where('id',$orders[0]->address_id)->first();
                                            $elt_response[] = [
                                                'api_key' => 'b623d7c412d5cb593c577b1016eae93c',
                                                'pickup_latitude' => $pharmacy->lat,
                                                'pickup_longitude' => $pharmacy->lon,
                                                'pickup_name' => $pharmacy->name,
                                                'pickup_contact' => $pharmacy->mobile_number,
                                                'pickup_address' => $pharmacy->address,
                                                'pickup_city' => $pharmacy->city,
                                                'delivery_latitude' => $address->latitude,
                                                'delivery_longitude' => $address->longitude,
                                                'delivery_name' => $address->name,
                                                'delivery_contact' => $address->mobileno,
                                                'delivery_address' => $address->address,
                                                'delivery_city' => $address->city,
                                                'order_amount' => $orders[0]->order_amount,
                                                'no_of_parcle' => '1',
                                                'order_id' => $orders[0]->id,
                                                'order_time'=> $orders[0]->create_datetime,
                                            ];
                                        }
                                        $client = new Client;
                                        $r = $client->post('https://developer.eltapp.in/v3/order_schedule/create', 
                                        ['json' => [
                                            "response" => $elt_response
                                        ]]);
                                        $logistic_data[] = [
                                            'logistic_current_status' => true,
                                            'next_logistic_working_day' => ''
                                        ];
                                        $response['status'] = 200;
                                        $response['message'] = 'Set delivery';
                                        $object_data = (object)$logistic_data[0];
                                        $response['data'] = $object_data;

                                        //notification to user order accept
                                        if($user_id > 0){
                                        $ids = array();
                                        $order_data = new_orders::where('id',$order_id)->first();
                                        $customerdetails = new_users::where('id',$order_data->customer_id)->get();
                                            foreach ($customerdetails as $customerdetail) {
                                                if($customerdetail->fcm_token!=''){
                                                            $ids[] = $customerdetail->fcm_token;
                                                        }
                                                        $msg = array
                                                        (
                                                            'body'   => ' Order Accept '. $order_data->order_number,
                                                            'title'     => 'Order Accepted'
                                                        );
                                                        // if(count($ids)>0){
                              // $fields = array(
                                // 'to' => $customerdetail->fcm_token,
                                // 'notification' => $msg
                              // );
                              // $this->sendPushNotification($fields);   
                                                        // }
                            if (count($ids) > 0) {          
                              Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Order Accepted', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                            }
                                                        $notification = new notification_user();
                                                        $notification->user_id=$customerdetail->id;
                                                        $notification->order_id=$order_data->id;
                                                        $notification->subtitle=$msg['body'];
                                                        $notification->title=$msg['title'];
                                                        $notification->created_at=date('Y-m-d H:i:s');
                                                        $notification->save();
                                                }             
                                        }
                                    }else{
                                        //order process with internal logistic
                                        
                                        $orderAssign = new Orderassign();
                                        $orderAssign->order_id = $order_id;
                                        $orderAssign->logistic_id = $intersected[0]->id;
                                        $orderAssign->order_status = 'new';
                                        $orderAssign->updated_at = date('Y-m-d H:i:s');
                                        $orderAssign->save();
                                                
                                        $order->assign_datetime = date('Y-m-d H:i:s');
                                        $order->order_status = 'assign';
                                        $order->logistic_user_id = $intersected[0]->id;

                                        $assignOrderEmit = (object)[];
                                        $assignOrderEmit->pharmacy_id = $order->pharmacy_id;
                                        $assignOrderEmit->logistic_id = $order->logistic_user_id;
                                        $image_url = url('/').'/uploads/placeholder.png';

                                        if (!empty($order->prescription_image)) {
                                            if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
                                                $image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
                                            }
                                        }

                                        $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
                                        $assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
                                        $assignOrderEmit->order_number = $order->order_number;
                                        $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                                        $assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
                                        $assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
                                        $assignOrderEmit->order_amount = $order->order_amount;
                                        $assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

                                        event(new AssignOrderLogistic($assignOrderEmit));
                                        $logistic_data[] = [
                                            'logistic_current_status' => true,
                                            'next_logistic_working_day' => ''
                                        ];
                                    $response['status'] = 200;
                                    $response['message'] = 'Set delivery';
                                    $object_data = (object)$logistic_data[0];
                                    $response['data'] = $object_data;
                                    
                                    //notification to user for order accept
                                    if($user_id > 0){
                                        $ids = array();
                                        $order_data = new_orders::where('id',$order_id)->first();
                                        $customerdetails = new_users::where('id',$order_data->customer_id)->get();
                                            foreach ($customerdetails as $customerdetail) {
                                                if($customerdetail->fcm_token!=''){
                                                            $ids[] = $customerdetail->fcm_token;
                                                        }
                                                        $msg = array
                                                        (
                                                            'body'   => ' Order Accept '. $order_data->order_number,
                                                            'title'     => 'Order Accepted'
                                                        );
                                                        // if(count($ids)>0){
                              // $fields = array(
                                // 'to' => $customerdetail->fcm_token,
                                // 'notification' => $msg
                              // );
                              // $this->sendPushNotification($fields);   
                                                        // }
                            if (count($ids) > 0) {
                              Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Order Accepted', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                            }
                                                        $notification = new notification_user();
                                                        $notification->user_id=$customerdetail->id;
                                                        $notification->order_id=$order_data->id;
                                                        $notification->subtitle=$msg['body'];
                                                        $notification->title=$msg['title'];
                                                        $notification->created_at=date('Y-m-d H:i:s');
                                                        $notification->save();
                                                }             
                                        }
                                    }
                            }else{
                                //next day order process with first priority logistic
                                foreach ($intersected as $value) {
                                        $orderAssign = new Orderassign();
                                        $orderAssign->order_id = $order_id;
                                        $orderAssign->logistic_id = $value->id;
                                        $orderAssign->order_status = 'new';
                                        $orderAssign->updated_at = date('Y-m-d H:i:s');
                                        $orderAssign->save();
                                                
                                        $order->assign_datetime = date('Y-m-d H:i:s');
                                        $order->order_status = 'assign';
                                        $order->logistic_user_id = $value->id;

                                        $assignOrderEmit = (object)[];
                                        $assignOrderEmit->pharmacy_id = $order->pharmacy_id;
                                        $assignOrderEmit->logistic_id = $order->logistic_user_id;
                                        $image_url = url('/').'/uploads/placeholder.png';

                                        if (!empty($order->prescription_image)) {
                                            if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
                                                $image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
                                            }
                                        }

                                        $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
                                        $assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
                                        $assignOrderEmit->order_number = $order->order_number;
                                        $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                                        $assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
                                        $assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
                                        $assignOrderEmit->order_amount = $order->order_amount;
                                        $assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

                                        event(new AssignOrderLogistic($assignOrderEmit));
                                }
                                $logistic_data[] = [
                                    'logistic_current_status' => false,
                                    'next_logistic_working_day' => $this->GetNextDayLogic()
                                ];
                                $response['status'] = 200;
                                $response['message'] = 'Set delivery';
                                $object_data = (object)$logistic_data[0];
                                $response['data'] = $object_data;
                            }
                        } else {
                            $order->order_status = 'accept';
                        }
                       
                        $order->save();

                        $order_data = new_orders::where('id',$order_id)->first();
                            //mail when order transfer to logistic
                            if(!empty($order_data)){
                                $email_data = new_logistics::where('id',$order->logistic_user_id)->first();
                                $data = [
                                    'name' => $email_data->name,
                                    'orderno'=>$order_data->order_number
                                ];
                                $email = $email_data->email;
                                $result = Mail::send('email.upcoming', $data, function ($message) use ($email) {
                                        $message->to($email)->subject('Pharma - Upcoming Order');
                                }); 
                            }
                        }else{
                            $response['status'] = 404;
                            $response['message'] = 'This Order Is Already Accepted';
                        }
                    }
                          }else{
                              $response['status'] = 404;
                              $response['message'] = 'Your deliveries is over';
                          }
                      }else{
                          $phar_data = new_pharmacies::where('id',$check_data->pharmacy_id)->first();
                          $remining_standard_paid_deliveries = $phar_data->remining_standard_paid_deliveries;
                          if($remining_standard_paid_deliveries != -5){
                              $phar_data->remining_standard_paid_deliveries = $remining_standard_paid_deliveries - 1;
                               $phar_data->save();
                               foreach ($orders as $order) {
                        if($order->process_user_id == 0 || $order->order_status == "incomplete"){
                        $order->is_external_delivery = 1;
                        $order->delivery_charges_id = $delivery_charges_id;
                        $order->external_delivery_initiatedby = 'seller';
                        $order->process_user_id = $user_id;
                        $order->process_user_type = 'seller';
                        $order->accept_datetime = date('Y-m-d H:i:s');
                        $order->order_status = 'accept';
                        if($order->logistic_user_id > 0){
                            $order_data = new_orders::where('id',$order_id)->first();
                            if($order_data->order_status == "incomplete"){
                                    if(!empty($order_data->logistic_user_id)){
                                        $email_data = new_logistics::where('id',$order_data->logistic_user_id)->first();
                                        $data = [
                                            'name' => $email_data->name,
                                            'orderno'=>$order_data->order_number
                                        ];
                                        $email = $email_data->email;
                                        $result = Mail::send('email.redeliver', $data, function ($message) use ($email) {
                                                $message->to($email)->subject('Pharma - Redeliver Order');
                                        }); 
                                }
                            }
                            $this->AssignOrderOutsideLogistic($order_id,$order);
                            //notification to user order accept
                            if($user_id > 0){
                            $ids = array();
                            $order_data = new_orders::where('id',$order_id)->first();
                            $customerdetails = new_users::where('id',$order_data->customer_id)->get();
                                foreach ($customerdetails as $customerdetail) {
                                    if($customerdetail->fcm_token!=''){
                                                $ids[] = $customerdetail->fcm_token;
                                            }
                                            $msg = array
                                            (
                                                'body'   => ' Order Accept '. $order_data->order_number,
                                                'title'     => 'Order Accepted'
                                            );
                                            // if(count($ids)>0){
                        // $fields = array(
                          // 'to' => $customerdetail->fcm_token,
                          // 'notification' => $msg
                        // );
                        // $this->sendPushNotification($fields);   
                                            // }
                      
                      if (count($ids) > 0) {          
                        Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Order Accepted', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                      }
                                            $notification = new notification_user();
                                            $notification->user_id=$customerdetail->id;
                                            $notification->order_id=$order_data->id;
                                            $notification->subtitle=$msg['body'];
                                            $notification->title=$msg['title'];
                                            $notification->created_at=date('Y-m-d H:i:s');
                                            $notification->save();
                                    }             
                            }
                        } elseif ($order->logistic_user_id < 0) {
                            $intersected = $this->AssignOrderToIntersectedLogistic();
                            if(count($intersected) == 1){
                                    if(strtoupper($intersected[0]->name) == "ELT"){
                                    //order update in our db
                                    $orderAssign = new Orderassign();
                                    $orderAssign->order_id = $order_id;
                                    $orderAssign->logistic_id = $intersected[0]->id;
                                    $orderAssign->order_status = 'new';
                                    $orderAssign->updated_at = date('Y-m-d H:i:s');
                                    $orderAssign->save();
                                            
                                    $order->assign_datetime = date('Y-m-d H:i:s');
                                    $order->order_status = 'assign';
                                    $order->logistic_user_id = $intersected[0]->id;

                                    $assignOrderEmit = (object)[];
                                    $assignOrderEmit->pharmacy_id = $order->pharmacy_id;
                                    $assignOrderEmit->logistic_id = $order->logistic_user_id;
                                    $image_url = url('/').'/uploads/placeholder.png';

                                    if (!empty($order->prescription_image)) {
                                        if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
                                            $image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
                                        }
                                    }

                                    $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
                                    $assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
                                    $assignOrderEmit->order_number = $order->order_number;
                                    $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                                    $assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
                                    $assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
                                    $assignOrderEmit->order_amount = $order->order_amount; 
                                    $assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

                                    event(new AssignOrderLogistic($assignOrderEmit));

                                    //order process with external logistic
                                         $elt_response = [];
                                        if(count($orders)>0){
                                            $pharmacy = new_pharmacies::where('id',$orders[0]->pharmacy_id)->first();
                                            $address = new_address::where('id',$orders[0]->address_id)->first();
                                            $elt_response[] = [
                                                'api_key' => 'b623d7c412d5cb593c577b1016eae93c',
                                                'pickup_latitude' => $pharmacy->lat,
                                                'pickup_longitude' => $pharmacy->lon,
                                                'pickup_name' => $pharmacy->name,
                                                'pickup_contact' => $pharmacy->mobile_number,
                                                'pickup_address' => $pharmacy->address,
                                                'pickup_city' => $pharmacy->city,
                                                'delivery_latitude' => $address->latitude,
                                                'delivery_longitude' => $address->longitude,
                                                'delivery_name' => $address->name,
                                                'delivery_contact' => $address->mobileno,
                                                'delivery_address' => $address->address,
                                                'delivery_city' => $address->city,
                                                'order_amount' => $orders[0]->order_amount,
                                                'no_of_parcle' => '1',
                                                'order_id' => $orders[0]->id,
                                                'order_time'=> $orders[0]->create_datetime,
                                            ];
                                        }
                                        $client = new Client;
                                        $r = $client->post('https://developer.eltapp.in/v3/order_schedule/create', 
                                        ['json' => [
                                            "response" => $elt_response
                                        ]]);
                                        $logistic_data[] = [
                                            'logistic_current_status' => true,
                                            'next_logistic_working_day' => ''
                                        ];
                                        $response['status'] = 200;
                                        $response['message'] = 'Set delivery';
                                        $object_data = (object)$logistic_data[0];
                                        $response['data'] = $object_data;

                                        //notification to user order accept
                                        if($user_id > 0){
                                        $ids = array();
                                        $order_data = new_orders::where('id',$order_id)->first();
                                        $customerdetails = new_users::where('id',$order_data->customer_id)->get();
                                            foreach ($customerdetails as $customerdetail) {
                                                if($customerdetail->fcm_token!=''){
                                                            $ids[] = $customerdetail->fcm_token;
                                                        }
                                                        $msg = array
                                                        (
                                                            'body'   => ' Order Accept '. $order_data->order_number,
                                                            'title'     => 'Order Accepted'
                                                        );
                                                        // if(count($ids)>0){
                              // $fields = array(
                                // 'to' => $customerdetail->fcm_token,
                                // 'notification' => $msg
                              // );
                              // $this->sendPushNotification($fields);   
                                                        // }
                            if (count($ids) > 0) {          
                              Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Order Accepted', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                            }
                                                        $notification = new notification_user();
                                                        $notification->user_id=$customerdetail->id;
                                                        $notification->order_id=$order_data->id;
                                                        $notification->subtitle=$msg['body'];
                                                        $notification->title=$msg['title'];
                                                        $notification->created_at=date('Y-m-d H:i:s');
                                                        $notification->save();
                                                }             
                                        }
                                    }else{
                                        //order process with internal logistic
                                        
                                        $orderAssign = new Orderassign();
                                        $orderAssign->order_id = $order_id;
                                        $orderAssign->logistic_id = $intersected[0]->id;
                                        $orderAssign->order_status = 'new';
                                        $orderAssign->updated_at = date('Y-m-d H:i:s');
                                        $orderAssign->save();
                                                
                                        $order->assign_datetime = date('Y-m-d H:i:s');
                                        $order->order_status = 'assign';
                                        $order->logistic_user_id = $intersected[0]->id;

                                        $assignOrderEmit = (object)[];
                                        $assignOrderEmit->pharmacy_id = $order->pharmacy_id;
                                        $assignOrderEmit->logistic_id = $order->logistic_user_id;
                                        $image_url = url('/').'/uploads/placeholder.png';

                                        if (!empty($order->prescription_image)) {
                                            if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
                                                $image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
                                            }
                                        }

                                        $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
                                        $assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
                                        $assignOrderEmit->order_number = $order->order_number;
                                        $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                                        $assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
                                        $assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
                                        $assignOrderEmit->order_amount = $order->order_amount;
                                        $assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

                                        event(new AssignOrderLogistic($assignOrderEmit));
                                        $logistic_data[] = [
                                            'logistic_current_status' => true,
                                            'next_logistic_working_day' => ''
                                        ];
                                    $response['status'] = 200;
                                    $response['message'] = 'Set delivery';
                                    $object_data = (object)$logistic_data[0];
                                    $response['data'] = $object_data;
                                    
                                    //notification to user for order accept
                                    if($user_id > 0){
                                        $ids = array();
                                        $order_data = new_orders::where('id',$order_id)->first();
                                        $customerdetails = new_users::where('id',$order_data->customer_id)->get();
                                            foreach ($customerdetails as $customerdetail) {
                                                if($customerdetail->fcm_token!=''){
                                                            $ids[] = $customerdetail->fcm_token;
                                                        }
                                                        $msg = array
                                                        (
                                                            'body'   => ' Order Accept '. $order_data->order_number,
                                                            'title'     => 'Order Accepted'
                                                        );
                                                        // if(count($ids)>0){
                              // $fields = array(
                                // 'to' => $customerdetail->fcm_token,
                                // 'notification' => $msg
                              // );
                              // $this->sendPushNotification($fields);   
                                                        // }
                            if (count($ids) > 0) {
                              Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Order Accepted', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                            }
                                                        $notification = new notification_user();
                                                        $notification->user_id=$customerdetail->id;
                                                        $notification->order_id=$order_data->id;
                                                        $notification->subtitle=$msg['body'];
                                                        $notification->title=$msg['title'];
                                                        $notification->created_at=date('Y-m-d H:i:s');
                                                        $notification->save();
                                                }             
                                        }
                                    }
                            }else{
                                //next day order process with first priority logistic
                                foreach ($intersected as $value) {
                                        $orderAssign = new Orderassign();
                                        $orderAssign->order_id = $order_id;
                                        $orderAssign->logistic_id = $value->id;
                                        $orderAssign->order_status = 'new';
                                        $orderAssign->updated_at = date('Y-m-d H:i:s');
                                        $orderAssign->save();
                                                
                                        $order->assign_datetime = date('Y-m-d H:i:s');
                                        $order->order_status = 'assign';
                                        $order->logistic_user_id = $value->id;

                                        $assignOrderEmit = (object)[];
                                        $assignOrderEmit->pharmacy_id = $order->pharmacy_id;
                                        $assignOrderEmit->logistic_id = $order->logistic_user_id;
                                        $image_url = url('/').'/uploads/placeholder.png';

                                        if (!empty($order->prescription_image)) {
                                            if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
                                                $image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
                                            }
                                        }

                                        $assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->id.'</span>';
                                        $assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
                                        $assignOrderEmit->order_number = $order->order_number;
                                        $assignOrderEmit->delivery_type = new_delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
                                        $assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
                                        $assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
                                        $assignOrderEmit->order_amount = $order->order_amount;
                                        $assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

                                        event(new AssignOrderLogistic($assignOrderEmit));
                                }
                                $logistic_data[] = [
                                    'logistic_current_status' => false,
                                    'next_logistic_working_day' => $this->GetNextDayLogic()
                                ];
                                $response['status'] = 200;
                                $response['message'] = 'Set delivery';
                                $object_data = (object)$logistic_data[0];
                                $response['data'] = $object_data;
                            }
                        } else {
                            $order->order_status = 'accept';
                        }
                       
                        $order->save();

                        $order_data = new_orders::where('id',$order_id)->first();
                            //mail when order transfer to logistic
                            if(!empty($order_data)){
                                $email_data = new_logistics::where('id',$order->logistic_user_id)->first();
                                $data = [
                                    'name' => $email_data->name,
                                    'orderno'=>$order_data->order_number
                                ];
                                $email = $email_data->email;
                                $result = Mail::send('email.upcoming', $data, function ($message) use ($email) {
                                        $message->to($email)->subject('Pharma - Upcoming Order');
                                }); 
                            }
                        }else{
                            $response['status'] = 404;
                            $response['message'] = 'This Order Is Already Accepted';
                        }
                    }
                          }else{
                              $response['status'] = 404;
                              $response['message'] = 'Your deliveries is over';
                          }
                      }
                    
                }
        }else{
            $response['status'] = 401;
            $response['message'] = 'Unauthenticated';
        }  
        return decode_string($response, 200);
    }

   
    public function getLogisticList($logistics, $content)
	{
		$coordinates = array();
		$logisticChecked = array();
		$coordinates[0] = $content[0];
		$coordinates[1] = $content[1];

		foreach ($logistics as $key => $value) { 
			$logistic = $value;
			$geo_fencings = \DB::table('geo_fencings')->select('*')->where('user_id', '=', $value->id)->get();

			$result = 'false';
			if(count($geo_fencings)>0){
				foreach ($geo_fencings as $key => $value) { 
					switch ($value->type) {
						case 'circle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRound($coords, $value->radius, $coordinates);
							break;
						
						case 'polygon':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinPolygon($coords, $coordinates);
							break;
	
						case 'rectangle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRectangle($coords, $coordinates);
							break;
					}
				}
			}

			if($result == 'true'){
				$logisticChecked[count($logisticChecked)] = $logistic;
			}
		}

		return $logisticChecked;
    }
    
    public function checkWithinRound($center, $radius, $coordinates)
	{
		// https://stackoverflow.com/questions/12439801/how-to-check-if-a-certain-coordinates-fall-to-another-coordinates-radius-using-p
		try {
			$earth_radius = 6371;
			$radius = $radius/1000; // for KM value
			$dLat = deg2rad($center[0] - $coordinates[0]);  
			$dLon = deg2rad($center[1] - $coordinates[1]);
			
			$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($coordinates[0])) * cos(deg2rad($center[0])) * sin($dLon/2) * sin($dLon/2);  
			$c = 2 * asin(sqrt($a));  
			$d = $earth_radius * $c;  
			
			if($d <  $radius) {
				return 'true';
			} else {
				return 'false';
			}
		} catch (Exception $e) {
			return 'false';
		}
	}

	public function checkWithinPolygon($coords, $coordinates)
	{
		// https://stackoverflow.com/questions/5065039/find-point-in-polygon-php
		try {
			$vertices_x = array();
			$vertices_y = array();

			foreach ($coords as $key => $value) { 
				if(!ctype_space($value)){
					if (($key%2) == 0) array_push($vertices_x, $value);
					else array_push($vertices_y, $value);
				}
			}

			$points_polygon = count($vertices_x) - 1;

			$longitude_x = $coordinates[0];  // x-coordinate of the point to test
			$latitude_y = $coordinates[1]; 

			$i = $j = $c = 0;
			for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
				if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
				($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
			}

			if ($c){
				return 'true';
			}
			else return 'false';

		} catch (Exception $e) {
			return 'false';
		}
	}

	public function checkWithinRectangle($coords, $coordinates)
	{
		// https://stackoverflow.com/questions/5065039/find-point-in-polygon-php
		try {
			$vertices_x = array();
			$vertices_y = array();

			foreach ($coords as $key => $value) { 
				if(!ctype_space($value)){
					if (($key%2) == 0) array_push($vertices_x, $value);
					else array_push($vertices_y, $value);
				}
			}

			$points_polygon = count($vertices_x) - 1;

			$longitude_x = $coordinates[0];  // x-coordinate of the point to test
			$latitude_y = $coordinates[1]; 

			$i = $j = $c = 0;
			for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
				if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
				($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
			}

			if ($c){
				return 'true';
			}
			else return 'false';

		} catch (Exception $e) {
			return 'false';
		}
    }

    public function pharamcyGeofenceCheck($logistics, $content)
	{
		$coordinates = array();
		$coordinates[0] = $content[0];
		$coordinates[1] = $content[1];

		foreach ($logistics as $key => $value) { 
			$logistic = $value;
			$geo_fencings = \DB::table('geo_fencings')->select('*')->where('user_id', '=', $value->id)->get();

			$result = 'false';
			if(count($geo_fencings)>0){
				foreach ($geo_fencings as $key => $value) { 
					switch ($value->type) {
						case 'circle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRound($coords, $value->radius, $coordinates);
							break;
						
						case 'polygon':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinPolygon($coords, $coordinates);
							break;
	
						case 'rectangle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRectangle($coords, $coordinates);
							break;
					}
					if($result == 'true'){
						return array($result, $logistic);
					}
				}
			}
		}
	}
    
    public function logistic_list(Request $request){
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

        $seller_data =  new_pharma_logistic_employee::where('id',$user_id)->get();
        foreach ($seller_data as $seller) {
            $phar_data= new_pharma_logistic_employee::where('id',$seller->pharma_logistic_id)->get();
            foreach ($phar_data as $phar) {
                $logistics = \DB::table('new_logistics')
                        ->select('new_logistics.*')
                       /* ->where('users.user_type', '=', 'logistic')*/
                        ->where('new_logistics.pincode', '=', $phar->pincode)->get();
            $content = array();
            $content[0] = $phar->latitude;
            $content[1] = $phar->longitude;
            $response['status'] = 200;
            $response['message'] = 'Logistics list';
            $response['data'] = $this->getLogisticList($logistics, $content);
            }
        }
        return decode_string($response, 200);
    }

    public function cancel_order(Request $request){
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
            'rejectreason' => $rejectreason,
        ];
        
        $validator = Validator::make($params, [
            'user_id' => 'required',
            'order_id' => 'required',
            'rejectreason' => 'required',
        ]);

        if ($validator->fails()) {
            return validation_error($validator->errors()->first());  
        }

        $cancel = new_orders::find($order_id);
        if(!empty($cancel)){
            $cancel->reject_cancel_reason = $rejectreason;
            $cancel->cancel_datetime =date('Y-m-d H:i:s');
            $cancel->order_status = 'cancel';
            $cancel->save();

            $user = new_pharma_logistic_employee::where(['id'=>$user_id])->first();

            if($user_id > 0){
                $ids = array();
                $order_data = new_orders::where('id',$order_id)->first();
                $customerdetail =  new_users::where('id',$order_data->customer_id)->first();
                    if($customerdetail->fcm_token!=''){
                        $ids[] = $customerdetail->fcm_token;
                    }
                    $delivery_address =  new_address::where('id',$order_data->address_id)->first();
                    $msg = array
                (
                    'body'   => ' Order number '.$order_data->order_number,
                    'title'     => 'Your Order Cancelled'
                );
                // if(count($ids)>0){
                    // $fields = array(
                        // 'to' => $customerdetail->fcm_token,
                        // 'notification' => $msg
                    // );
                    // $this->sendPushNotification($fields);   
                // }
                if (count($ids) > 0) {
                    Helper::sendNotificationUser($ids, 'Order number '. $order_data->order_number, 'Your Order Cancelled', $user->id, 'seller', $order_data->customer_id, 'user', $customerdetail->fcm_token);
                }
                $notification = new notification_user();
                $notification->user_id=$customerdetail->id;
                $notification->order_id=$order_data->id;
                $notification->subtitle=$msg['body'];
                $notification->title=$msg['title'];
                $notification->created_at=date('Y-m-d H:i:s');
                $notification->save();
            }
            
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
            $response['message'] = 'Cancel Order Successfully';
        }else{
                $response['status'] = 404;
                $response['message'] = 'This order is already cancelled';
        }
       
        return decode_string($response, 200);

    }
    public function return_confirm(Request $request)
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

        $token = $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->first();

        if(!empty($user)){
            $order = new_orders::find($order_id);
            if(!empty($order)){
                $order_data = new_orders::where('id',$order_id)->first();
                $order_assign = Orderassign::where('order_id',$order_id)->first();
                if(!empty($order_assign)){
                         $logistic_data = new_logistics::where('id',$order_data->logistic_user_id)->first();
                                $data = [
                                    'name' => $logistic_data->name,
                                    'orderno'=>$order_data->order_number
                                ];
                                $email = $logistic_data->email;
                                $result = Mail::send('email.handshake', $data, function ($message) use ($email) {
                                        $message->to($email)->subject('Pharma - Pharmacy Order Handshake');
                                });
                }

                if($user_id > 0){
                    $ids = array();
                    $order_data = new_orders::where('id',$order_id)->first();
                    if(!empty($order_data)){
                       $deliveryboydetail =  new_pharma_logistic_employee::where('id',$order_data->deliveryboy_id)->first();
                        if($deliveryboydetail->fcm_token!=''){
                            $ids[] = $deliveryboydetail->fcm_token;
                        }
                        $msg = array
                        (
                            'body'   => ' Order number '. $order_data->order_number,
                            'title'     => 'Order Return Confirmed'
                        );
                        if(count($ids)>0){
                            $fields = array(
                                'to' => $deliveryboydetail->fcm_token,
                                'notification' => $msg
                            );
                            // $this->sendPushNotificationDeliveryboy($fields);   
                        }
                        
                        if(count($ids)>0){
                            Helper::sendNotificationDeliveryboy($ids, 'Order number '.$order_data->order_number, 'Order Return Confirmed', $user->id, 'seller', $deliveryboydetail->id, 'delivery_boy', $deliveryboydetail->fcm_token);
                        }
                    }
                }
                $order->return_confirmtime = date('Y-m-d H:i:s');
                $order->deliveryboy_id = 0;
                $order->save();

                $orderAssign = Orderassign::where('order_id',$order_id)->first();
                $orderAssign->deliveryboy_id = 0;
                $orderAssign->save();
                $response['status'] = 200;
                $response['message'] = 'Order Return Confirmed';
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
    public function notification_seller(Request $request)
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

        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();
        $notification = [];
        $token = $request->bearerToken();
        $user = new_pharma_logistic_employee::where(['id'=>$user_id, 'api_token'=>$token])->get();
        if(count($user)>0){
        $notification_data = notification_seller::select('id','user_id','title','subtitle','order_id','order_status','created_at')->where('user_id',$user_id)->orderBy('id','DESC');
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
                                'order_status'=> $value['order_status'],
                                'created_at'=> date('h:i A', strtotime($value['created_at'])),
                                'date'=> (date_format(new DateTime($value['created_at']),"Y-m-d"))
                            ];
                    }
                $response['status'] = 200;
                $response['message'] = 'Notification For Seller';
               
            }else{
            	$response['status'] = 404;
              $response['message'] = 'Notification For Seller';
            }
            $response['data']->content = $notification;
        } else {
            $response['status'] = 401;
            $response['message'] = 'Unauthenticated';
        }
        
        return decode_string($response, 200);
    }
    public function order_detail(Request $request)
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
        $user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
        if(count($user)>0){
                $order_details =  new_orders::where('id' , $order_id)->orderBy('id', 'DESC')->get();
                $order_details_complete =  new_order_history::where('order_id' , $order_id)->orderBy('order_id', 'DESC')->get();
                if(count($order_details)>0){
                         foreach($order_details as $value) {
                                    $prescription_image = '';
                                    $image_list = Prescription::where('id',$value->prescription_id)->get();
                                    foreach ($image_list as $p_img) {
                                    if (!empty($p_img->image)) {

                                        $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                    
                                        if (File::exists($filename)) {
                                            $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                        } else {
                                            $prescription_image = '';
                                        }
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
                               $user_list = new_users::where('id',$value->customer_id)->get();
                               $name='';
                               $mobile='';
                                foreach ($user_list as $u_img) { 
                                    $name = $u_img->name;
                                    $mobile = $u_img->mobile_number;
                                }
                                $address_data = new_address::where('id',$value->address_id)->get();
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
                                $deliveryboy_name = new_pharma_logistic_employee::where('id',$value->deliveryboy_id)->get();
                                $deliveryboy='';
                                foreach ($deliveryboy_name as $d_name) {
                                    $deliveryboy = $d_name->name;
                                }
                                $delivery_type_data = new_delivery_charges::where('id',$value->delivery_charges_id)->get();

                                 $pickup_images=[];
                                 $pickup_date = '';
                                 if($value->order_status == 'pickup'){
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
                                    $pickup_date = $value->pickup_datetime;
                                 }
                                
                                $delivered_images=[];
                                $deliver_date = '';
                                 if($value->order_status == 'complete'){  
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
                                    $deliver_date = $value->deliver_datetime;
                                }
                                   

                                $delivery_type = '';
                                foreach ($delivery_type_data as $dt) {
                                    $delivery_type =$dt->delivery_type;
                                }
                                    $orders[] = [
                                    'order_id' => $value->id,
                                    'order_number' => $value->order_number,
                                    'prescription_image' => $prescription_image,
                                    'invoice'=> $invoice_images,
                                    'order_note' => $value->order_note,
                                    'order_type' => $value->order_type,
                                    'total_days' => $value->total_days,
                                    'reminder_days' => $value->reminder_days,
                                    'customer_name' => $name,
                                    'mobile_number' => $mobile,
                                    'return_confirmtime' => ($value->return_confirmtime)?$value->return_confirmtime:'',
                                    'location' => ($destination_address)?$destination_address[0]:'',
                                    'order_assign_to' => $deliveryboy,
                                    'deliver_to' =>  $name,
                                    'accept_date' => ($value->accept_datetime)?$value->accept_datetime:'',
                                    'deliver_date' => $deliver_date,
                                    'assign_date' => ($value->assign_datetime)?$value->assign_datetime:'',
                                    'cancel_reason' => ($value->reject_cancel_reason) ? $value->reject_cancel_reason: '',
                                    'return_reason' => ($value->reject_cancel_reason) ? $value->reject_cancel_reason: '',
                                    'reject_reason' => ($value->reject_cancel_reason) ?$value->reject_cancel_reason: '',
                                    'return_date' => ($value->reject_datetime)?$value->reject_datetime:'',
                                    'order_amount' => ($value->order_amount)?$value->order_amount:'',
                                    'delivery_type' => ($delivery_type)?$delivery_type:'free',
                                    'pickup_images' => $pickup_images,
                                    'pickup_date' => $pickup_date,
                                    'drop_images' => $delivered_images,
                                    'drop_date' => $deliver_date,
                                    'reject_date' => ($value->reject_datetime)?$value->reject_datetime:'',
                                    'cancel_date' => ($value->cancel_datetime)?$value->cancel_datetime:'',
                                    'received_date' => (date_format($value->created_at,"Y-m-d H:i:s"))?(date_format($value->created_at,"Y-m-d H:i:s")):'',
                                    'order_status' => $value->order_status,
                                    'external_delivery_initiatedby' => ($value->external_delivery_initiatedby)?$value->external_delivery_initiatedby:'',
                                    'order_time'=>($value->create_datetime)?$value->create_datetime:''
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'Order Details';
                } elseif (count($order_details_complete)>0) {
                    foreach($order_details_complete as $value) {
                                    $prescription_image = '';
                                    $image_list = Prescription::where('id',$value->prescription_id)->get();
                                    foreach ($image_list as $p_img) {
                                    if (!empty($p_img->image)) {

                                        $filename = storage_path('app/public/uploads/prescription/' .  $p_img->image);
                                    
                                        if (File::exists($filename)) {
                                            $prescription_image = asset('storage/app/public/uploads/prescription/' .  $p_img->image);
                                        } else {
                                            $prescription_image = '';
                                        }
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
                               $user_list = new_users::where('id',$value->customer_id)->get();
                               $name='';
                               $mobile='';
                                foreach ($user_list as $u_img) { 
                                    $name = $u_img->name;
                                    $mobile = $u_img->mobile_number;
                                }
                                $address_data = new_address::where('id',$value->address_id)->get();
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
                                $deliveryboy_name = new_pharma_logistic_employee::where('id',$value->deliveryboy_id)->get();
                                $deliveryboy='';
                                foreach ($deliveryboy_name as $d_name) {
                                    $deliveryboy = $d_name->name;
                                }
                                $delivery_type_data = new_delivery_charges::where('id',$value->delivery_charges_id)->get();

                                 $pickup_images=[];
                                 $pickup_date = '';
                                 if($value->order_status == 'pickup'){
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
                                    $pickup_date = $value->pickup_datetime;
                                 }
                                
                                $delivered_images=[];
                                $deliver_date = '';
                                 if($value->order_status == 'complete'){  
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
                                    $deliver_date = $value->deliver_datetime;
                                }
                                   

                                $delivery_type = '';
                                foreach ($delivery_type_data as $dt) {
                                    $delivery_type =$dt->delivery_type;
                                }
                                    $orders[] = [
                                    'order_id' => $value->order_id,
                                    'order_number' => $value->order_number,
                                    'prescription_image' => $prescription_image,
                                    'invoice'=> $invoice_images,
                                    'order_note' => $value->order_note,
                                    'order_type' => $value->order_type,
                                    'total_days' => $value->total_days,
                                    'reminder_days' => $value->reminder_days,
                                    'customer_name' => $name,
                                    'mobile_number' => $mobile,
                                    'return_confirmtime' => ($value->return_confirmtime)?$value->return_confirmtime:'',
                                    'location' => ($destination_address)?$destination_address[0]:'',
                                    'order_assign_to' => $deliveryboy,
                                    'deliver_to' =>  $name,
                                    'accept_date' => ($value->accept_datetime)?$value->accept_datetime:'',
                                    'deliver_date' => $deliver_date,
                                    'assign_date' => ($value->assign_datetime)?$value->assign_datetime:'',
                                    'cancel_reason' => ($value->reject_cancel_reason) ? $value->reject_cancel_reason: '',
                                    'return_reason' => ($value->reject_cancel_reason) ? $value->reject_cancel_reason: '',
                                    'reject_reason' => ($value->reject_cancel_reason) ?$value->reject_cancel_reason: '',
                                    'return_date' => ($value->reject_datetime)?$value->reject_datetime:'',
                                    'order_amount' => ($value->order_amount)?$value->order_amount:'',
                                    'delivery_type' => ($delivery_type)?$delivery_type:'free',
                                    'pickup_images' => $pickup_images,
                                    'pickup_date' => $pickup_date,
                                    'drop_images' => $delivered_images,
                                    'drop_date' => $deliver_date,
                                    'reject_date' => ($value->reject_datetime)?$value->reject_datetime:'',
                                    'cancel_date' => ($value->cancel_datetime)?$value->cancel_datetime:'',
                                    'received_date' => (date_format($value->created_at,"Y-m-d H:i:s"))?(date_format($value->created_at,"Y-m-d H:i:s")):'',
                                    'order_status' => $value->order_status,
                                    'external_delivery_initiatedby' => ($value->external_delivery_initiatedby)?$value->external_delivery_initiatedby:'',
                                    'order_time'=>($value->create_datetime)?$value->create_datetime:''
                                ];
                            }
                        $response['status'] = 200;
                        $response['message'] = 'Order Details';
                }  else {
                        $response['status'] = 404;
                }
            }else{
                $response['status'] = 401;
                $response['message'] = 'Unauthenticated';
            }
        
        $response['data'] = $orders;
        
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
		$user = new_pharma_logistic_employee::where(['id'=>$user_id,'api_token'=>$token])->get();
		if(count($user)>0){
		$notification_arr = [];
		
		$notification = notification_seller::where('user_id','=',$user_id)->delete();
		
		$response['status'] = 200;
		$response['message'] = 'Notification cleared';
		$response['data'] = [];
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

    public function sendPushNotificationDeliveryboy($fields) {
        //firebase server url to send the curl request
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=AAAA-mRxROI:APA91bGgKa1Znu-pnOUQlnBVEX65jC-O6N1aNZK26c7owecQsogxjyFKy2S4Fb7-p0CxBUETphRrgoH9c2tb90OCUu-iGJJq7TQ0PHyLBCRM3Bsz0NNjJqxTIQ0gF16l98rXEGFJ9qN5',
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
