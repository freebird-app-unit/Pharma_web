<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Packages;
use App\Packagetransaction;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
 
class PackageshistoryController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='pharmacy'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Package History';
		$data['page_condition'] = 'page_packagehistory';
		$data['site_title'] = 'Package History | ' . $this->data['site_title'];
        return view('packagehistory.index', $data);
    }
	public function list()
    {
		$user_id = Auth::user()->id;
		$data = Packagetransaction::select('package_transaction.payment_id','package_transaction.order_number','package_transaction.total_delivery','package_transaction.package_purchase_date','package_transaction.package_amount','package.name')
		->leftJoin('package', 'package.id', '=', 'package_transaction.package_id')->where('package_transaction.user_id',$user_id)->get();
		
	      
        return Datatables::of($data)
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
	}
}
