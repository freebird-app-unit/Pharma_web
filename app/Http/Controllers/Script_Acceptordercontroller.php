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
use App\SellerModel\invoice;

class Script_Acceptordercontroller extends Controller
{
	public function getorderList(Request $request)
    {
          $orders = DB::table("new_orders")
          ->where(["pharmacy_id"=>$request->pharmacy_id,"order_status"=>"new"])
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

    public function store(Request $request){
            $validate = $request->validate([
              'pharmacy_id' => 'required',
              'order_number' => 'required',
              'seller_id' => 'required',
              'accept_reject' => 'required',
        ]);

        if($validate){
            $find_data = new_orders::where('id',$request->order_number)->first();
            $find_data->order_status = "accept";
            $find_data->checking_by = $request->seller_id;
            $find_data->process_user_id = $request->seller_id;
            $find_data->process_user_type = "seller";
            if($request->accept_reject == "accept"){
                $find_data->order_amount = $request->order_amount;
                $find_data->accept_datetime =date('Y-m-d H:i:s');
                $destinationPath = 'storage/app/public/uploads/invoice/'; 
                        if($file=$request->file('invoice')){
                                $filename = time().'-'.$file->getClientOriginalName();
                                $tesw = $file->move($destinationPath, $filename);
                                $invoice_data = new invoice();
                                $invoice_data->order_id = $request->order_number;
                                $invoice_data->invoice = $filename;
                                $invoice_data->created_at = date('Y-m-d H:i:s');
                                $invoice_data->updated_at = date('Y-m-d H:i:s');
                                $invoice_data->save();
                            }
                $find_data->save();
                return redirect(route('acceptorder.create'))->with('success_message', trans('Accept Successfully'));
            }else{
                $find_data->reject_cancel_reason = $request->reject_reason;
                $find_data->reject_datetime = date('Y-m-d H:i:s');
                $find_data->rejectby_user = "seller";
                $find_data->reject_user_id =  $request->seller_id;
                $find_data->save();
                 return redirect(route('acceptorder.create'))->with('unsuccess_message', trans('Reject Successfully'));
            }

            
        }
    }
}
