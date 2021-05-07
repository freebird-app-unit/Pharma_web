<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\new_orders;
use App\new_pharmacies;
use DB;
use Auth;
use App\SellerModel\Orderassign;
use App\new_users;
use App\new_pharma_logistic_employee;
use Helper;
use App\notification_deliveryboy;


class Script_Deliveryboyacceptorder extends Controller
{
    public function getorderList(Request $request)
    {
          $orders = DB::table("new_orders")
          ->where(["pharmacy_id"=>$request->pharmacy_id,"order_status"=>"assign"])
          ->pluck("order_number","id");
           return response()->json($orders);
    }
    public function getOrderFreePaid(Request $request)
    {
          $order_id = DB::table("new_orders")
          ->where("is_external_delivery","id",$request->order_number)
           ->pluck("is_external_delivery","id");
           return response()->json($order_id);
    }
    public function getdeliveryboyList(Request $request)
    {
          $deliveryboy = DB::table("new_pharma_logistic_employee")
          ->where(["pharma_logistic_id"=>$request->pharmacy_id,"user_type"=>"delivery_boy","is_active"=>"1","is_available"=>"1"])
          ->pluck("name","id");
           return response()->json($deliveryboy);
    }
     public function getcustomerList(Request $request)
    {
          $customer_id = DB::table("new_orders")
          ->where("id",$request->order_number)
          ->first();
          $customer_name = DB::table("new_users")
          ->where("id",$customer_id->customer_id)
          ->pluck("name","id");
           return response()->json($customer_name);
    }
    public function getDeliveryLocation(Request $request)
    {
          $customer_id = DB::table("new_orders")
          ->where("id",$request->order_number)
          ->first();
          $customer_address = DB::table("address_new")
          ->where("id",$customer_id->address_id)
          ->pluck("address","id");
           return response()->json($customer_address);
    }
    public function getPikcupLocation(Request $request)
    {
          $customer_id = DB::table("new_orders")
          ->where("id",$request->order_number)
          ->first();
          $pharmacy_address = DB::table("new_pharmacies")
          ->where("id",$customer_id->pharmacy_id)
          ->pluck("address","id");
           return response()->json($pharmacy_address);
    }
    public function getleaveWithNeighbourLocation(Request $request)
    {
          $leave_with_neighbour = DB::table("new_orders")
          ->where("id",$request->order_number)
          ->pluck("leave_neighbour","id");
           return response()->json($leave_with_neighbour);
    }
    public function getOrderAssign(Request $request)
    {
          $seller_id = DB::table("new_orders")
          ->where("id",$request->order_number)
          ->first();
          $orderassign_by = DB::table("new_pharma_logistic_employee")
          ->where("id",$seller_id->process_user_id)
          ->pluck("name","id");
           return response()->json($orderassign_by);
    }
    
    public function create()
    {
    	$data = array();
  		$data['page_title'] = 'Accept Order From Deliveryboy script';
  		$data['page_condition'] = 'page_deliveryboyacceptorder';
  		$data['pharmacies'] = new_pharmacies::select('id','name','is_active','is_available','is_approve')->where(['is_active'=>'1','is_available'=>'1','is_approve'=>'1'])->get();
  		$data['site_title'] = 'Accept Order From Deliveryboy script | ' . $this->data['site_title'];
  		return view('deliveryboyacceptorder.create', array_merge($this->data, $data));
    }

     public function store(Request $request){
        $validate = $request->validate([
            'pharmacy_id' => 'required',
            'order_number' => 'required',
        ]);
        dd($request->get('save_exit'));
        if($validate){
             $orderAssign = Orderassign::where(['deliveryboy_id' => $request->deliveryboy_id, 'order_id' => $request->order_number, 'order_status' => 'assign'])->first();
           if(!empty($orderAssign)){
                $orderAssign->order_status = 'accept';
                $orderAssign->accept_date = date('Y-m-d H:i:s');
                $orderAssign->reject_date = date('Y-m-d H:i:s');
                $orderAssign->updated_at = date('Y-m-d H:i:s');
                if($orderAssign->save()){
                        $ids = array();
                        $seller_id = array();
                        $user = new_pharma_logistic_employee::where('id',$request->seller)->first();
                        $order_data = new_orders::where('id',$request->order_number)->first();
                        $sellerdetail = new_pharma_logistic_employee::where('id',$order_data->process_user_id)->first();
                        $deliveryboydetail = new_pharma_logistic_employee::where('id',$order_data->deliveryboy_id)->first();
                        if($sellerdetail->fcm_token!=''){
                            $ids[] = $sellerdetail->fcm_token;
                            $seller_id[] = $sellerdetail->id;
                        }
                        if (count($ids) > 0) {                  
                            Helper::sendNotification($ids, 'Order Accepted '. $order_data->order_number .' From '.$deliveryboydetail->name, 'Order Accepted', $user->id, 'delivery_boy', $seller_id, 'seller', $ids);
                        }
                            
                        $notification = new notification_seller();
                        $notification->user_id=$sellerdetail->id;
                        $notification->order_id=$order_data->id;
                        $notification->order_status=$order_data->order_status;
                        $notification->subtitle=' Order Accepted '. $order_data->order_number .' From '.$deliveryboydetail->name;
                        $notification->title=' Order Accepted';
                        $notification->created_at=date('Y-m-d H:i:s');
                        $notification->save();
                }
            }else{
                $response['status'] = 404;
                $response['message'] = 'This order was already cancelled';
            }

            return redirect(route('deliveryboyacceptorder.create'))->with('success_message', trans('Accept Order Successfully'));
        }
    }
}
