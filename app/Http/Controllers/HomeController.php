<?php

namespace App\Http\Controllers;
use App\User;
use App\Incompletereason;
use App\Rejectreason;
use App\new_logistics;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Events\CreateNewOrder;
use Session;

use App\new_pharma_logistic_employee;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
		parent::__construct();
		$this->middleware('auth');
	}
	
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
		$user = auth()->user();
		$data['all_pharmacy_ids'] = array();
		$data['page_condition'] = 'page_dashboard';
		if($user->user_type=='pharmacy'){
			array_push($data['all_pharmacy_ids'], $user->user_id);
			//Today Earning
			$current_date = date('Y-m-d');
			$today_earning = DB::table('new_order_history')->where('order_status','complete')->where('pharmacy_id', $user->user_id)->whereDate('accept_datetime','=',$current_date)->sum('order_amount');
			$data['today_earning'] = $today_earning;
			//Total Earning
			$total_earning = DB::table('new_order_history')->where('order_status','complete')->where('pharmacy_id', $user->user_id)->sum('order_amount');
			$data['total_earning'] = $total_earning;

			//Today Delivery
			$today_delivery = DB::table('new_order_history')->where('pharmacy_id', $user->user_id)->whereDate('accept_datetime','=',$current_date)->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id');
			$today_delivery = $today_delivery->Where('new_order_history.is_external_delivery','=',1)->WhereIN('new_order_history.is_admin_delivery_charge_collect',array(0,1));
			//['new_order_history.is_admin_delivery_charge_collect'=> 0, 'new_order_history.is_admin_delivery_charge_collect'=> 1]
			$today_delivery = $today_delivery->sum('new_delivery_charges.delivery_price');
			$data['today_delivery'] = $today_delivery;

			//Total Delivery
			$total_delivery = DB::table('new_order_history')->where('pharmacy_id', $user->user_id)->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id');
			$total_delivery = $total_delivery->Where('new_order_history.is_external_delivery','=',1)->WhereIN('new_order_history.is_admin_delivery_charge_collect',array(0,1));
			$total_delivery = $total_delivery->sum('new_delivery_charges.delivery_price');
			$data['total_delivery'] = $total_delivery;

			//Pending
			$total_res = DB::table('new_orders')->select('new_orders.*')->whereDate('create_datetime','=',$current_date)
			->where('order_status','new')->where('pharmacy_id', $user->user_id);
			$total= $total_res->count();
			$data['total_incomplete'] = $total;
			//Pending

			//accepted
			$total_res = DB::table('new_orders')->select('new_orders.*')->whereDate('accept_datetime','=',$current_date)
			->where('order_status','accept')->where('pharmacy_id', $user->user_id);
			$total= $total_res->count();
			//118653, 118657, 118665, 118667, 118669, 118681, 118682, 118683, 118684, 118688, 118735, 118791
			$data['total_accepted'] = $total;
			//accepted
			
			//completed
			$total_res = DB::table('new_order_history')->select('new_orders.*')->whereDate('deliver_datetime','=',$current_date)
			->where('order_status','complete')->where('pharmacy_id', $user->user_id);
			$total= $total_res->count();
			$data['total_complete'] = $total;
			//completed

			//Delivery
			$total_res = DB::table('new_orders')->select('new_orders.*')->whereDate('pickup_datetime','=',$current_date)
			->where('order_status','pickup')->where('pharmacy_id', $user->user_id);
			$total= $total_res->count();
			$data['total_outfordelivery'] = $total;
			//Delivery
			
			//canceled
			$total_res = DB::table('new_order_history')->select('new_orders.*')->whereDate('cancel_datetime','=',$current_date)
			->where('order_status','cancel')->where('pharmacy_id', $user->user_id);
			$total= $total_res->count();
			$data['total_canceled'] = $total;
			//canceled
			
			//rejected
			$total_res = DB::table('new_order_history')->select('new_orders.*')->whereDate('reject_datetime','=',$current_date)
			->where('order_status','reject')->where('pharmacy_id', $user->user_id);
			$total= $total_res->count();
			$data['total_rejected'] = $total;
			//rejected
		
		}
		if($user->user_type=='admin'){
			//orders
			$total_res = DB::table('new_orders');
			$total_res = $total_res->count();
			$data['total_orders'] = $total_res;
			//orders
			
			//pharmacy
			$total_res = DB::table('new_pharmacies');
			//->where('user_type','pharmacy');
			$total= $total_res->count();
			$data['total_pharmacy'] = $total;
			//pharmacy
			
			//seller
			$total_res = DB::table('new_pharma_logistic_employee')
			->where('user_type','seller');
			$total= $total_res->count();
			$data['total_seller'] = $total;
			//seller
			
			//delivery boy
			$total_res = DB::table('new_pharma_logistic_employee')
			->where('user_type','delivery_boy');
			$total= $total_res->count();
			$data['total_deliveryboy'] = $total;
			//delivery boy

			//customer
			$total_res = DB::table('new_users');
			//->where('user_type','customer');
			$total= $total_res->count();
			$data['total_customer'] = $total;
			//customer

			//admin
			$total_res = DB::table('admin_panel_creds')
			->where('user_type','admin');
			$total= $total_res->count();
			$data['total_admin'] = $total;
			//admin
			
			//users
			$total_res = DB::table('admin_panel_creds');
			$total= $total_res->count();
			$data['total_users'] = $total;
			//users
		}
		if($user->user_type=='logistic'){
			$data['page_condition'] = 'page_logistic_dashboard';
			array_push($data['all_pharmacy_ids'], $user->user_id);
			$data['logistic_id'] = $user->user_id;
			$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'logistic')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user->user_id)->get();
			
			//accepted
			$total_res = DB::table('new_orders')->select('new_orders.*')
			->where('order_status','accept')->where('logistic_user_id', $user->user_id);
			$total= $total_res->count();
			$data['total_accepted'] = $total;
			//accepted
			
			//out for delivery
			$total_res = DB::table('new_orders')->select('new_orders.*')->leftJoin('order_assign','order_assign.order_id','=','new_orders.id')
			->where(['new_orders.order_status'=>'assign','order_assign.order_status'=>'assign'])->where('logistic_user_id', $user->user_id);
			$total= $total_res->count();
			$data['total_outfordelivery'] = $total;
			//out for delivery

			//Ready For pickup
			$total_res = DB::table('new_orders')->select('new_orders.*')->leftJoin('order_assign','order_assign.order_id','=','new_orders.id')
			->where(['new_orders.order_status'=>'pickup','order_assign.order_status'=>'accept'])->where('logistic_user_id', $user->user_id);
			$total= $total_res->count();
			$data['total_readyforpickup'] = $total;
			//Ready For pickup
			
			//completed
			$total_res = DB::table('new_order_history')->select('new_order_history.*')
			->where('order_status','complete')->where('logistic_user_id', $user->user_id);
			$total= $total_res->count();
			$data['total_complete'] = $total;
			//completed

			//incompleted
			$total_res = DB::table('new_orders')->select('new_orders.*')
			->where('order_status','incomplete')->where('logistic_user_id', $user->user_id);
			$total= $total_res->count();
			$data['total_incomplete'] = $total;
			//incompleted

			//cancelled
			$total_res = DB::table('new_order_history')->select('new_order_history.*')
			->where('order_status','cancel')->where('logistic_user_id', $user->user_id);
			$total= $total_res->count();
			$data['total_canceled'] = $total;
			//cancelled

			//upcoming
			$total_res = DB::table('new_orders')->select('new_orders.*')->leftJoin('order_assign','order_assign.order_id','=','new_orders.id')
			->where(['new_orders.order_status'=>'assign','order_assign.order_status'=>'new'])->where('logistic_user_id', $user->user_id);
			$total= $total_res->count();
			$data['total_upcoming'] = $total;
			//upcoming
			$new_logistic = new_logistics::find($user->user_id);
			$data['total_deposit'] = $new_logistic->total_deposit;
			$data['current_deposit'] = $new_logistic->current_deposit;
			
		}
		if($user->user_type=='pharmacy'){
			$data['reject_reason'] = Rejectreason::where('type', 'pharmacy')->get();
		}else if($user->user_type=='logistic'){
			$data['reject_reason'] = Rejectreason::where('type', 'logistic')->get();
		}else{
			$data['reject_reason'] = Rejectreason::get();
		}

		$data['site_title'] = 'Dashboard | ' . $this->data['site_title'];
        return view('home', array_merge($this->data, $data));
    }
}