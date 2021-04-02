<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use DB;
use Auth;
use App\new_logistics;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
use Paykun\Checkout\Payment;
 
class CurrentdepositController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='admin' && Auth::user()->user_type!='logistic'){
			return redirect(route('home'));
		}
		$data = array();
		
		$logistic_user = new_logistics::find(Auth::user()->user_id);
		$data['logistic_user'] = $logistic_user;
		$data['page_title'] = 'Current Deposite';
		$data['page_condition'] = 'page_currentdeposit';
		$data['site_title'] = 'Current Deposite | ' . $this->data['site_title'];
		$logistic_list = new_logistics::get();
		$data['logistic_list'] = $logistic_list;
        return view('currentdeposit.index', $data);
    }
	public function list()
    {
		//$data = Deposit::get();
		$data = DB::table('new_orders')
            ->join('new_logistics', 'new_logistics.id', '=', 'new_orders.logistic_user_id')
			->join('new_users', 'new_users.id', '=', 'new_orders.customer_id')
			->join('address_new', 'address_new.id', '=', 'new_orders.address_id')
			->join('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
			->join('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
            ->select('new_orders.order_number','new_orders.order_status','new_orders.order_type','new_orders.order_amount', 'new_users.name as customer_name', 'new_users.mobile_number as customer_number', 'new_logistics.name as logistic_name', 'address_new.address as myaddress', 'new_delivery_charges.delivery_type as delivery_type', 'new_pharmacies.name as pharmacy_name','new_orders.created_at as created_at')
			->where('new_orders.is_external_delivery','1')
			//->where('new_orders.is_deposit_clear','0')
            ->latest();
	    $request = $_REQUEST;
        return Datatables::of($data)
            ->addIndexColumn()
			->filter(function ($instance) use ($request) {
				if ($request['logistic'] != '') {
					$instance->where('new_orders.logistic_user_id', $request['logistic']);
                }
			})
			/* ->addColumn('action', function ($row) {
                $btn = '<a class="action-icon" data-toggle="modal" href="javascript:void(0)" onclick="loadForm('.trim($row->id).');" data-id="'.$row->id.'"><i class="fa fa-pencil text-success"></i></a> ';
                   $btn .= '<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deleteAllergy" data-id="'.$row->id.'" ><i class="fa fa-trash text-danger"></i></a>';
                return $btn;
            }) */
            ->rawColumns(['action'])
            ->make(true);
	}
}
