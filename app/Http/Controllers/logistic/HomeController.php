<?php

namespace App\Http\Controllers\logistic;

use App\Http\Controllers\Controller;
use App\User;
use App\Incompletereason;
use App\Rejectreason;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Events\CreateNewOrder;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		parent::__construct();
		$this->middleware('auth:new_logistics');
	}
	
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
		$user = auth()->user();
		$data['logistic_id'] = $user->id;
		$data['all_pharmacy_ids'] = array();
		
		if($user->user_type='logistic'){
			//accepted
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','accept');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
			}
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_accepted'] = $total;
			//accepted
			
			//out for delivery
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','assign');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
			}
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_outfordelivery'] = $total;
			//out for delivery
			
			//completed
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','complete');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
			}
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_complete'] = $total;
			//completed

			//upcoming
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','new');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
			}
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_upcoming'] = $total;

			//upcoming
		}
		if($user->user_type=='delivery_boy'){
			//received
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','assign')
			->where('deliveryboy_id',$user->id);
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_received'] = $total;
			//received
			
			//completed
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','complete')
			->where('deliveryboy_id',$user->id);
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_complete'] = $total;
			//completed
			
			//incomplete
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','incomplete')
			->where('deliveryboy_id',$user->id);
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_incomplete'] = $total;
			//incomplete
		
		}
		$user_id = Auth::user()->id;
		$data['deliveryboy_list'] = User::where('parentuser_id',$user_id)->where('user_type','delivery_boy')->get();
		$data['reject_reason'] = Incompletereason::get();
		$data['page_condition'] = 'page_logistic_dashboard';
		$data['site_title'] = 'Dashboard | ' . $this->data['site_title'];
        return view('logistic.home', array_merge($this->data, $data));
    }
	
}
