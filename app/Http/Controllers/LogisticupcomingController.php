<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\Orders;
use App\Rejectreason;
use App\Orderassign;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;

use App\new_pharma_logistic_employee;
use App\new_users;
use App\new_pharmacies;
use App\new_logistics;
use App\new_orders;
use App\new_delivery_charges;

class LogisticupcomingController extends Controller
{
    public function __construct()
    {
		parent::__construct();
		$this->middleware('auth');
    }
    public function index()
    {
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		$user_id = Auth::user()->user_id;
		$data = array();
		$data['page_title'] = 'Upcoming Orders';
		$data['page_condition'] = 'page_upcomingorders';
		$data['site_title'] = 'Upcoming Orders | ' . $this->data['site_title'];
		if(Auth::user()->user_type='logistic'){
			$data['deliveryboy_list'] = new_pharma_logistic_employee::where(['pharma_logistic_id'=> $user_id, 'is_active'=> 1])->where('user_type','delivery_boy')->where('parent_type','logistic')->get();
		}
		$data['reject_reason'] = Rejectreason::where('type', 'logistic')->get();
        return view('logisticupcoming.index', $data);
	}
}
