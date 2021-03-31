<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\new_users;
use Auth;
use DB;
use App\SellerModel\Prescription;
use App\new_pharmacies;
use App\new_logistics;
use App\new_delivery_charges;
use App\new_orders;
use App\new_address;
use Helper;
use App\new_pharma_logistic_employee;
use App\notification_seller;
use Mail;
use App\Events\CreateNewOrder;

class Script_Acceptordercontroller extends Controller
{
	public function getorderList(Request $request)
    {
          $orders = DB::table("new_orders")
          ->where("pharmacy_id",$request->pharmacy_id)
          ->pluck("order_number","id");
           return response()->json($orders);
    }
    public function getsellerList(Request $request)
    {
          $seller = DB::table("new_pharma_logistic_employee")
          ->where(["pharma_logistic_id"=>$request->pharmacy_id,"user_type"=>"seller","is_active"=>"1","is_available"=>"1"])
          ->pluck("name","id");
           return response()->json($seller);
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
		$data['page_title'] = 'Accept order script';
		$data['page_condition'] = 'page_createorder';
		$data['pharmacies'] = new_pharmacies::where(['is_active'=>'1','is_available'=>'1','is_approve'=>'1'])->get();
		$data['site_title'] = 'Accept order script | ' . $this->data['site_title'];
		return view('acceptorder.create', array_merge($this->data, $data));
    }
}
