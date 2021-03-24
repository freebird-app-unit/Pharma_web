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
use App\new_logistics;
use App\new_orders;
use App\new_users;
use App\new_pharmacies;

class LogisticpickupController extends Controller
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
		Auth::user()->user_type='logistic';
		$user_id = Auth::user()->id;
		$data = array();
		$data['page_title'] = 'Pickup';
		$data['page_condition'] = 'page_logistic_pickup';
		$data['site_title'] = 'Pickup | ' . $this->data['site_title'];
		$data['deliveryboy_list'] = new_pharma_logistic_employee::where('pharma_logistic_id', $user_id)->where('user_type', 'delivery_boy')->where('parent_type','logistic')->where('is_active', 1)->get();
        return view('logisticpickup.index', $data);
	}
}
