<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Deposit;
use App\new_pharma_logistic_employee;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
use Paykun\Checkout\Payment;
 
class DepositController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Deposite transaction';
		$data['page_condition'] = 'page_deposittransaction';
		$data['site_title'] = 'Deposite transaction | ' . $this->data['site_title'];
        return view('deposit.index', $data);
    }
	public function list()
    {
		//$data = Deposit::get();
		$data = DB::table('deposit_transaction')
            ->join('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'deposit_transaction.logistic_id')
            ->select('deposit_transaction.*', 'new_pharma_logistic_employee.name as delivery_boy_name')
            ->get();
	      
        return Datatables::of($data)
            ->addIndexColumn()
			/* ->addColumn('action', function ($row) {
                $btn = '<a class="action-icon" data-toggle="modal" href="javascript:void(0)" onclick="loadForm('.trim($row->id).');" data-id="'.$row->id.'"><i class="fa fa-pencil text-success"></i></a> ';
                   $btn .= '<a data-toggle="modal" href="#delete_modal" class="m-l-10 action-icon deleteAllergy" data-id="'.$row->id.'" ><i class="fa fa-trash text-danger"></i></a>';
                return $btn;
            }) */
            ->rawColumns(['action'])
            ->make(true);
	}
	public function loadForm($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		 
        $data = Deposit::find($id);
		$delivery_boy_list = new_pharma_logistic_employee::where('user_type','delivery_boy')->get();
		
        $html = view('deposit.create')->with(["data" => $data,"delivery_boy_list" => $delivery_boy_list])->render();

        return response()->json([
            'html'         	=> $html,
            'msg'           => ''
        ]);
	}
	
	public function save(Request $request)
    {   
    	$params = $request->all();

        $validator = Validator::make($params, [
            'logistic_id' => 'required',
			'amount' => 'required',
        ]);

        if ($validator->fails()) {
        	return response()->json([
	            'status_code' => 400,
	            'message'     => $validator->errors()->all(),
	        ]);
        }
		
		$total_deposit= 0;
		$deposit_plus = Deposit::where('logistic_id',$params['logistic_id'])->where('transaction_type','plus')->sum('amount');
		$deposit_minus = Deposit::where('logistic_id',$params['logistic_id'])->where('transaction_type','minus')->sum('amount');
		
		$total_deposit = ($deposit_plus - $deposit_minus) + $params['amount'];
		
        $updateData = [
         	'logistic_id' => $params['logistic_id'],
			'reference_number' => $params['reference_number'],
			'amount' => $params['amount'],
			'transaction_datetime' => date('Y-m-d H:i:s'),
			'total_deposit' => $total_deposit,
			'transaction_type' => 'plus',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
        ];

        /* if (!empty($params['package_id'])) {
            $msg = 'Record updated successfully';
			$updateData['is_active'] = $params['is_active'];
			$updateData['updated_at'] = date('Y-m-d H:i:s');
        } else {
			$updateData['is_active'] = 1;
			$updateData['is_delete'] = 0;
			$updateData['created_at'] = date('Y-m-d H:i:s');
            $msg = 'Record saved successfully';
        } */
		$msg = 'Record saved successfully';
        Deposit::updateOrCreate(['id' => $request->deposit_id], $updateData );

        return response()->json([
            'status_code' => 200,
            'message'     => $msg,
        ], 200);
    }
	
	public function delete($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$Packages = Packages::find($id);
		if($Packages){
			$Packages->is_delete = 1;
			$Packages->save();
		}
		return redirect(route('user.index'))->with('success_message', trans('Deleted Successfully'));
	}
	
	public function payment($package_id){
		$package = Packages::find($package_id);
		$user_id = Auth::user()->id;
		$user = User::find($user_id);
		$pharmacy = new_pharmacies::find($user->user_id); 
		$obj = new Payment('675253164797390', '90581EA5C3C3089E0A031BD7385A8F44', '85E8069EB99C0284467190FB26C28276');
 
		// Initializing Order
		$obj->initOrder('<orderId>', 'Package Purchase', "<amount>", '<successUrl.example.com>',  '<failUrl.example.com>');
		 
		// Add Customer
		$obj->addCustomer($user->name, $user->email, $user->mobile_number);
		 
		// Add Shipping address
		$obj->addShippingAddress($pharmacy->country, $pharmacy->state, $pharmacy->city, '<postalCode>', '<fullAddress>');
		 
		// Add Billing Address
		$obj->addBillingAddress('<country>', '<state>', '<city>', '<postalCode>', '<fullAddress>');
		 
		echo $obj->submit();
	}
}
