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

class Script_assignordercontroller extends Controller
{
	public function getorderList(Request $request)
    {
          $orders = DB::table("new_orders")
          ->where(["pharmacy_id"=>$request->pharmacy_id,"order_status"=>"accept"])
          ->pluck("order_number","id");
           return response()->json($orders);
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
    public function create()
    {
    	$data = array();
  		$data['page_title'] = 'Assign order script';
  		$data['page_condition'] = 'page_assignorder';
  		$data['pharmacies'] = new_pharmacies::select('id','name','is_active','is_available','is_approve')->where(['is_active'=>'1','is_available'=>'1','is_approve'=>'1'])->get();
  		$data['site_title'] = 'Assign order script | ' . $this->data['site_title'];
  		return view('assignorder.create', array_merge($this->data, $data));
    }
     public function store(Request $request){
        $validate = $request->validate([
            'pharmacy_id' => 'required',
            'order_number' => 'required',
            'deliveryboy_id' => 'required',
        ]);

        if($validate){
            $find_data = new_orders::select('id','order_status','assign_datetime','deliveryboy_id')->where('id',$request->order_number)->first();
           	$find_data->order_status = "assign";
            $find_data->assign_datetime = date('Y-m-d H:i:s');
            $find_data->deliveryboy_id = $request->deliveryboy_id;
			if($find_data->save()){
				  $ids = array();
                  $order_data = new_orders::select('id','deliveryboy_id','order_number','customer_id')->where('id',$request->order_number)->first();
                  $user = new_users::where('id',$order_data->customer_id)->first();
                  $deliveryboydetail =  new_pharma_logistic_employee::select('id','fcm_token')->where('id',$order_data->deliveryboy_id)->first();
                   if(!empty($deliveryboydetail)){
                       $ids[] = $deliveryboydetail->fcm_token;
                   }
                   if (count($ids) > 0) {                  
                        Helper::sendNotificationDeliveryboy($ids, 'Order number '.$order_data->order_number, 'Order Assigned', $user->id, 'seller', $deliveryboydetail->id, 'delivery_boy', $deliveryboydetail->fcm_token);
                    }                    
                    $notification = new notification_deliveryboy();
                    $notification->user_id=$deliveryboydetail->id;
                    $notification->order_id=$order_data->id;
                    $notification->subtitle= 'Order number'.$order_data->order_number;
                    $notification->title='Order Assigned';
                    $notification->created_at=date('Y-m-d H:i:s');
                    $notification->save();
			}

			$orderAssign = Orderassign::where('order_id',$request->order_number)->first();
            $orderAssign->order_status="assign";
            $orderAssign->logistic_id=0;
            $orderAssign->assign_date=date('Y-m-d H:i:s');
            $orderAssign->deliveryboy_id=$request->deliveryboy_id;
            $orderAssign->updated_at = date('Y-m-d H:i:s');
            $orderAssign->save();

            return redirect(route('assignorder.create'))->with('success_message', trans('Assign Successfully'));
        }
    }
}
