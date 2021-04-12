<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\Address;
use App\Orders;
use App\Rejectreason;
use App\Orderassign;
use App\delivery_charges;
use App\new_address;

use App\new_orders;
use App\new_order_history;
use App\new_users;
use App\new_pharmacies;

use DB;
use Auth;
use Illuminate\Support\Facades\Hash;

use App\Events\AssignOrderLogistic;
use App\new_pharma_logistic_employee;
use App\new_logistics;
use App\new_delivery_charges;
use Helper;


class OrdersController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
	}
	
    public function index()
    {
    	$user_id = Auth::user()->user_id;
		if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='seller'){
			return redirect(route('home'));
		}

		$data = array();
		$data['sellers'] = new_pharma_logistic_employee::where(['pharma_logistic_id'=> $user_id, 'parent_type'=> 'pharmacy', 'user_type'=> 'seller'])->get();
		$data['page_title'] = 'Orders';
		$data['page_condition'] = 'page_orders';
		$data['site_title'] = 'Orders | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::where('type', 'pharmacy')->get();
        return view('orders.index', $data);
	}
	
	public function getlist()
    {
		$user_id = Auth::user()->user_id;
		$user_type = Auth::user()->user_type;
		$html='';
		$pagination='';
		$total_summary='';
		$homepage = (isset($_REQUEST['home']))?$_REQUEST['home']:'';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		$pharmacy_seller_id=(isset($_POST['pharmacy_seller_id']) && $_POST['pharmacy_seller_id']!='')?$_POST['pharmacy_seller_id']:'';
		$order_status=(isset($_POST['order_status']) && $_POST['order_status']!='')?$_POST['order_status']:'';
		$order_delivery_type=(isset($_POST['order_delivery_type']) && $_POST['order_delivery_type']!='')?$_POST['order_delivery_type']:'';
		$filter_end_date=(isset($_POST['filter_end_date']) && $_POST['filter_end_date']!='')?$_POST['filter_end_date']:'';
		$filter_start_date=(isset($_POST['filter_start_date']) && $_POST['filter_start_date']!='')?$_POST['filter_start_date']:'';

		$order_detail = new_orders::select('new_orders.id','order_status','new_orders.created_at','order_number','is_external_delivery','deliveryboy_id','process_user_id','new_users.name as customer_name','new_users.mobile_number as customer_number','address_new.address as address', 'prescription.name as prescription_name', 'prescription.image as prescription_image')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id');//->where('new_orders.order_status','new')

		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('new_orders.pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
		}
		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('new_users.name', 'like', '%'.$searchtxt.'%')
						->orWhere('new_users.mobile_number', 'like', '%'.$searchtxt.'%')
						->orWhere('new_orders.order_number', 'like', '%'.$searchtxt.'%');
            });
		}
		if($pharmacy_seller_id != ""){
			$order_detail = $order_detail->where('new_orders.process_user_id', $pharmacy_seller_id)->where('process_user_type','=','seller');
        }
		if($order_status != ""){
			$order_detail = $order_detail->where('new_orders.order_status', $order_status);
        }
        if($order_delivery_type != ""){
        	if($order_delivery_type == "external_delivery"){
        		$order_detail = $order_detail->where('new_orders.is_external_delivery','!=', 0);
        	}
        	if($order_delivery_type == "internal_delivery"){
        		$order_detail = $order_detail->where('new_orders.is_external_delivery', 0);
        	}
        }
		if($filter_end_date != ""){
			$filter_end_date_array = explode("/", $filter_end_date);
			if(count($filter_end_date_array) == 3){
				$filter_end_date = $filter_end_date_array[2].'-'.$filter_end_date_array[1].'-'.$filter_end_date_array[0];
				$order_detail = $order_detail->whereDate('new_orders.created_at','<=',$filter_end_date); 	        	
			}
		}
		if($filter_start_date != ""){
			$filter_start_date_array = explode("/", $filter_start_date);
			if(count($filter_start_date_array) == 3){
				$filter_start_date = $filter_start_date_array[2].'-'.$filter_start_date_array[1].'-'.$filter_start_date_array[0];
				$order_detail = $order_detail->whereDate('new_orders.created_at','>=',$filter_start_date);
			}
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_orders.id','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				$html.='<tr><td><a href="'.url('/orders/order_details/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				if($order->is_external_delivery > 0){
					$html.=' <i class="ti-truck" style="color: orange;"></i>';
				}
				$deliveryboy_details = new_pharma_logistic_employee::select('name','pharma_logistic_id')->where('id','=',$order->deliveryboy_id)->first();
				$deliveryboy_name = "";
				$logistic_name = "";
				if($deliveryboy_details){
					$deliveryboy_name = $deliveryboy_details->name;
					$logistics = new_logistics::find($deliveryboy_details->pharma_logistic_id);
					if($logistics){
						$logistic_name = $logistics->code;
					}
				}
				$seller_details = new_pharma_logistic_employee::select('name')->where('id','=',$order->process_user_id)->first();
				$seller_name = "";
				if($seller_details){
					$seller_name = $seller_details->name;
				}
				$accept_date = ($order->created_at!='')?date('d-M-Y h:i a', strtotime($order->created_at)):'';
				$html.='</td><td>'.$order->customer_name.'</td>
				<td>'.$order->customer_number.'</td>
				<td>'.$order->address.'</td>
				<td>'.$logistic_name.'</td>
				<td>'.$accept_date.'</td>
				'; 
					
				$html.='<td>';
				if($homepage!=''){
					$html.='<a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$order->id.'?home').'" title="Accept order">Accept</a>';
				}else{
					$html.='<a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$order->id).'" title="Accept order">Accept</a>';
				}
				$html.='<a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';
				$html.='</td>';
				
				//$html.='<td>';
				//$html.= ucfirst($order->order_status);
				//$html.='</td>';	
				$html.='</tr>';
			}
			if($page==1){
				$prev='disabled';
			}else{
				$prev='';
			}
			if($total_page==$page){
				$next='disabled';
			}else{
				$next='';
			}
			$pagination.='<li class="page-item '.$prev.'">
						<a class="page-link" onclick="getorderslist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getorderslist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
					</li>';
					$from = ($per_page*($page-1));
					if($from<=0){$from=1;}
					$to = ($page*$per_page);
					if($to>=$total){$to= $total;}
			$total_summary.='&nbsp;&nbsp;'.$from.'-'.$to.' of '.$total;
		}else{
			$html.="<tr><td colspan='7'>No record found</td></tr>";
		}
		
		echo $html."##".$pagination."##".$total_summary;
	}

	public function accept($id)
    {
		$user_id = Auth::user()->user_id;
		$order = new_orders::find($id);
		$customer = new_users::find($order->customer_id);
		$order->process_user_type = 'pharmacy';
		$order->process_user_id = $user_id;
		$order->accept_datetime = date('Y-m-d H:i:s');

		if((isset($order->external_delivery_initiatedby)) && ($order->external_delivery_initiatedby !== 0) && ($order->logistic_user_id !== null) && ($order->logistic_user_id !== 0)){
			$orderAssign = new Orderassign();
			$orderAssign->order_id = $id;
			$orderAssign->logistic_id = $order->logistic_user_id;
			$orderAssign->order_status = 'new';
			$orderAssign->updated_at = date('Y-m-d H:i:s');
			$orderAssign->assign_date = date('Y-m-d H:i:s');
			$orderAssign->save();

			$order->assign_datetime = date('Y-m-d H:i:s');
			$order->order_status = 'assign';
		} else {
			$order->order_status = 'accept';
		}

		$order->save();
		$ids = array();
		$ids[] = $customer->fcm_token;
		$receiver_id = array();
		$receiver_id[] = $customer->id;
		if (count($ids) > 0) {					
			Helper::sendNotification($ids, 'Order Number :'.$order->order_number, 'Order Accepted', $user_id, 'pharmacy', $receiver_id, 'user', $ids);
		}
		if(isset($order->external_delivery_initiatedby) && ($order->external_delivery_initiatedby !== 0) && ($order->external_delivery_initiatedby !== null)){
			$assignOrderEmit = (object)[];
			$assignOrderEmit->pharmacy_id = $order->pharmacy_id;
			$assignOrderEmit->logistic_id = $order->logistic_user_id;

			$image_url = url('/').'/uploads/placeholder.png';

			// if (!empty($order->prescription_image)) {
			// 	if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
			// 		$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
			// 	}
			// }

			$assignOrderEmit->prescription_image = '<a href="'.url('/orders/prescription/'.$id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$id.'</span>';
			$assignOrderEmit->id = '<a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a>';
			$assignOrderEmit->order_number = $order->order_number;
			$assignOrderEmit->delivery_type = delivery_charges::where('id', $order->delivery_charges_id)->value('delivery_type');
			$assignOrderEmit->delivery_address = new_address::where('id', $order->address_id)->value('address');
			$assignOrderEmit->pickup_address = new_pharmacies::where('id',$order->pharmacy_id)->value('address');
			$assignOrderEmit->order_amount = $order->order_amount;
			$assignOrderEmit->action = '<a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';

			event(new AssignOrderLogistic($assignOrderEmit));
		}

		if(isset($_REQUEST['home'])){
			return redirect(route('home'))->with('success_message', trans('Order Successfully accepted'));
		}else{
			return redirect(route('orders.index'))->with('success_message', trans('Order Successfully accepted'));
		}
	}
	public function reject(Request $request)
    {
		$user_id = Auth::user()->user_id;
		$order = new_orders::find($request->reject_id);
		$order_current_status = $order->order_status;
		$order->process_user_id = $user_id;
		$order->order_status = 'reject';
		$order->rejectby_user = 'pharmacy';
		$order->reject_user_id = $user_id;
		$order->reject_datetime = date('Y-m-d H:i:s');
		$order->reject_cancel_reason = $request->reject_reason;

		if($order_current_status == 'complete' || $order_current_status == 'cancel'){
		$new_order_history = new new_order_history();
		$new_order_history->order_id = $request->reject_id;
		$new_order_history->customer_id = $order->customer_id;
		$new_order_history->prescription_id = $order->prescription_id;
		$new_order_history->order_number = $order->order_number;
		$new_order_history->order_status = $order->order_status;
		$new_order_history->order_note = $order->order_note;
		$new_order_history->address_id = $order->address_id;
		$new_order_history->audio = $order->audio;
		$new_order_history->audio_info = $order->audio_info;
		$new_order_history->order_type = $order->order_type;
		$new_order_history->total_days =  $order->total_days;
		$new_order_history->reminder_days = $order->reminder_days;
		$new_order_history->pharmacy_id = $order->pharmacy_id;
		$new_order_history->process_user_type = 'pharmacy';
		$new_order_history->process_user_id = $order->process_user_id;
		$new_order_history->logistic_user_id = $order->logistic_user_id;
		$new_order_history->deliveryboy_id = $order->deliveryboy_id;
		$new_order_history->create_datetime = $order->create_datetime;
		$new_order_history->accept_datetime = $order->accept_datetime;
		$new_order_history->assign_datetime = $order->assign_datetime;
		$new_order_history->pickup_datetime = $order->pickup_datetime;
		$new_order_history->deliver_datetime = $order->deliver_datetime;
		$new_order_history->reject_datetime = $order->reject_datetime;
		$new_order_history->return_datetime = $order->return_datetime;
		$new_order_history->refund_datetime = $order->refund_datetime;
		$new_order_history->refund_info = $order->refund_info;

		$new_order_history->rejectby_user = $order->rejectby_user;
		$new_order_history->reject_user_id = $order->reject_user_id;
		$new_order_history->reject_cancel_reason = $order->reject_cancel_reason;
		$new_order_history->logistic_reject_reason = $order->logistic_reject_reason;
		$new_order_history->leave_neighbour = $order->leave_neighbour;
		$new_order_history->neighbour_info = $order->neighbour_info;
		$new_order_history->is_external_delivery = $order->is_external_delivery;
		$new_order_history->external_delivery_initiatedby = $order->external_delivery_initiatedby;
		$new_order_history->order_amount = $order->order_amount;
		$new_order_history->delivery_charges_id = $order->delivery_charges_id;
		$new_order_history->is_delivery_charge_collect = $order->is_delivery_charge_collect;
		$new_order_history->is_amount_collect = $order->is_amount_collect;
		$new_order_history->is_refund_intiated = $order->is_refund_intiated;
		$new_order_history->external_delivery_initiatedby = $order->external_delivery_initiatedby;
		// $new_order_history->is_admin_amount_collect = $order->is_admin_amount_collect;
		// $new_order_history->is_pharmacy_amount_collect = $order->is_pharmacy_amount_collect;
		// $new_order_history->is_logistic_charge_collect = $order->is_logistic_charge_collect;
		// $new_order_history->is_admin_delivery_charge_collect = $order->is_admin_delivery_charge_collect;
		// $new_order_history->is_logistic_amount_collect = $order->is_logistic_amount_collect;
		$new_order_history->created_at = $order->created_at;
		$new_order_history->save();
		}
		$order->save();
		
		$customer = new_users::find($order->customer_id);
		$ids = array();
		$ids[] = $customer->fcm_token;
		$receiver_id = array();
		$receiver_id[] = $customer->id;
		if (count($ids) > 0) {					
			Helper::sendNotification($ids, 'Order Number :'.$order->order_number, 'Order Rejected', $user_id, 'pharmacy', $receiver_id, 'user', $ids);
		}
		
		if(isset($_REQUEST['home'])){
			return redirect(route('home'))->with('success_message', trans('Order Successfully rejected'));
		}else{
			return redirect(route('orders.index'))->with('success_message', trans('Order Successfully rejected'));
		}
	}
	public function prescription($id)
    {
		$user_id = Auth::user()->user_id;
		//$order = Orders::find($id);
		$order = new_orders::select('new_orders.*', 'prescription.name as prescription_name', 'prescription.image as prescription_image')->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')->where('new_orders.id', $id)->first();
		$order_detail = new_orders::select('new_orders.*', 'delivery_charges.delivery_type as delivery_type', 'delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress', 'new_pharma_logistic_employee.name as deliveryboyname')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
		->leftJoin('new_pharmacies as ua', 'ua.id', '=', 'new_orders.pharmacy_id')
		->leftJoin('delivery_charges', 'delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->where('new_orders.order_status','new')
		->where('new_orders.id',$id)->first();

		$customer = new_users::where('id',$order->customer_id)->first();
		$address = '';
		if(get_name('address','address',$order->address_id)!=''){
			$address.= get_name('address','address',$order->address_id).', ';
		}
		if(get_name('address','address2',$order->address_id)!=''){
			$address.= get_name('address','address2',$order->address_id).', ';
		}
		if(get_name('address','city',$order->address_id)!=''){
			$address.= get_name('address','city',$order->address_id).', ';
		}
		if(get_name('address','state',$order->address_id)!=''){
			$address.= get_name('address','state',$order->address_id).', ';
		}
		if(get_name('address','country',$order->address_id)!=''){
			$address.= get_name('address','country',$order->address_id).', ';
		}
		if(get_name('address','pincode',$order->address_id)!=''){
			$address.= get_name('address','pincode',$order->address_id).', ';
		}
		
		$address = rtrim($address,', ');
		$data = array();
		$data['order'] = $order;
		$data['order_detail'] = $order_detail;
		$data['customer'] = $customer;
		$data['address'] = $address;
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::where('type', 'pharmacy')->get();
        return view('orders.prescription', $data);
		
	}
	public function order_details($id)
    {
		$user_id = Auth::user()->user_id;

		if(new_order_history::find($id)){
			$order = new_order_history::select('new_order_history.*', 'prescription.name as prescription_name', 'prescription.image as prescription_image')->leftJoin('prescription', 'prescription.id', '=', 'new_order_history.prescription_id')->where('new_order_history.id', $id)->first();
			$order_detail = new_order_history::select('new_order_history.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname')
			->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_order_history.deliveryboy_id')
			->leftJoin('new_pharmacies as ua', 'ua.id', '=', 'new_order_history.pharmacy_id')
			->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')
			->leftJoin('address_new', 'address_new.id', '=', 'new_order_history.address_id')
			->where('new_order_history.id',$id)->first();
		} else {
			$order = new_orders::select('new_orders.*', 'prescription.name as prescription_name', 'prescription.image as prescription_image')->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')->where('new_orders.id', $id)->first();
			$order_detail = new_orders::select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname')
			->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
			->leftJoin('new_pharmacies as ua', 'ua.id', '=', 'new_orders.pharmacy_id')
			->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
			->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
			->where('new_orders.id',$id)->first();
		}
		
		$customer = new_users::where('id',$order->customer_id)->first();
		$address = '';
		if(get_name('address','address',$order->address_id)!=''){
			$address.= get_name('address','address',$order->address_id).', ';
		}
		if(get_name('address','address2',$order->address_id)!=''){
			$address.= get_name('address','address2',$order->address_id).', ';
		}
		if(get_name('address','city',$order->address_id)!=''){
			$address.= get_name('address','city',$order->address_id).', ';
		}
		if(get_name('address','state',$order->address_id)!=''){
			$address.= get_name('address','state',$order->address_id).', ';
		}
		if(get_name('address','country',$order->address_id)!=''){
			$address.= get_name('address','country',$order->address_id).', ';
		}
		if(get_name('address','pincode',$order->address_id)!=''){
			$address.= get_name('address','pincode',$order->address_id).', ';
		}
		$address = rtrim($address,', ');
		$data = array();
		$data['order'] = $order;
		$data['order_detail'] = $order_detail;
		$data['customer'] = new_users::where('id', $order->customer_id)->first();
		$data['address'] = $address;
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::where('type', 'pharmacy')->get();
        return view('orders.order_details', $data);
	}
}
