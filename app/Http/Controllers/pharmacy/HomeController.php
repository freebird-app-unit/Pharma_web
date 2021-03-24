<?php

namespace App\Http\Controllers\pharmacy;
use App\User;
use App\Incompletereason;
use App\Rejectreason;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Events\CreateNewOrder;
use App\Http\Controllers\Controller;

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
		$this->middleware('auth:new_pharmacies');
	}
	
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
		$user = auth()->user();
		$user->user_type == 'pharmacy';
		$data['all_pharmacy_ids'] = array();
		
		if($user->user_type=='pharmacy' || $user->user_type=='seller'){
			//accepted
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','accept');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
				array_push($data['all_pharmacy_ids'], $user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
				array_push($data['all_pharmacy_ids'], $parentuser_id);
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

			//canceled
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','cancel');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
			}
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_canceled'] = $total;
			//canceled
			
			//incomplete
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','incomplete');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
			}
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_incomplete'] = $total;
			//incomplete
			
			//rejected
			$total_res = DB::table('orders')->select('orders.*')
			->where('order_status','reject');
			if($user->user_type=='pharmacy'){
				$total_res = $total_res->where('pharmacy_id',$user->id);
			}else if($user->user_type=='seller'){
				$parentuser_id = Auth::user()->parentuser_id;
				$total_res = $total_res->where('pharmacy_id',$parentuser_id);
				$total_res = $total_res->where('process_user_id',$user->id);
			}
			$total_res= $total_res->get();
			$total = count($total_res);
			$data['total_rejected'] = $total;
			//rejected
		}
		if($user->user_type=='pharmacy' || $user->user_type=='seller'){
			$data['reject_reason'] = Rejectreason::get();
		}else{
			$data['reject_reason'] = Incompletereason::get();
		}
		$data['page_condition'] = 'page_dashboard';
		$data['site_title'] = 'Dashboard | ' . $this->data['site_title'];
        return view('home', array_merge($this->data, $data));
    }
}
