<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use DB;
use Auth;
use App\Onboardingrequest;
use App\new_pharmacies;
use App\User;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
use Paykun\Checkout\Payment;
 
class OnboardingrequestController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$data['page_title'] = 'Onboarding Request';
		$data['page_condition'] = 'page_onboarfingrequest';
		$data['site_title'] = 'Onboarding Request | ' . $this->data['site_title'];
        return view('onboardingrequest.index', $data);
    }
	public function list()
    {
		//$data = Deposit::get();
		$data = DB::table('pharmacy_onboarding_requests')
            ->select('id','name','first_name','last_name','address', 'email', 'phone', 'dateofapplication', 'dateofapproval')
			->where('is_registration_complete','1')
			->where('dateofapproval',null)
			->where('account_status','0')
            ->latest();
	    $request = $_REQUEST;
        return Datatables::of($data)
            ->addIndexColumn()
			->addColumn('action', function ($row) {
                $btn = '<a class="action-icon" href="'.url('/onboardingrequest/view/'.$row->id).'"><i class="fa fa-eye text-success"></i></a> ';
				$btn .= '<a class="action-icon approverequest" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fa fa-check text-success"></i></a> ';
				$btn .= '<a class="action-icon rejectrequest" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fa fa-ban text-danger"></i></a> ';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
	}
	public function approve($id){
		$Onboardingrequest = Onboardingrequest::find($id);
		if($Onboardingrequest){
			$new_pharmacies = new new_pharmacies();
			$new_pharmacies->name = $Onboardingrequest->name;
			$new_pharmacies->email = $Onboardingrequest->email;
			$new_pharmacies->mobile_number = $Onboardingrequest->phone;
			$new_pharmacies->password = $Onboardingrequest->password;
			$new_pharmacies->owner_name = $Onboardingrequest->first_name.' '.$Onboardingrequest->last_name;
			$new_pharmacies->address = $Onboardingrequest->address;
			$new_pharmacies->country = $Onboardingrequest->country;
			$new_pharmacies->state = $Onboardingrequest->state;
			$new_pharmacies->city = $Onboardingrequest->city;
			$new_pharmacies->pincode = $Onboardingrequest->pincode;
			$new_pharmacies->start_time = $Onboardingrequest->open_time;
			$new_pharmacies->close_time = $Onboardingrequest->close_time;
			$new_pharmacies->radius = $Onboardingrequest->radius;
			$new_pharmacies->pancard_image = $Onboardingrequest->pan_card;
			$new_pharmacies->profile_image = $Onboardingrequest->photo;
			$new_pharmacies->created_at = date('Y-m-d H:i:s');
			$new_pharmacies->save();
			
			$user = new User;
			$user->user_id = $new_pharmacies->id;
			$user->user_type = 'pharmacy';
			$user->name = $new_pharmacies->owner_name;
			$user->email = $new_pharmacies->email;
			$user->mobile_number = $new_pharmacies->mobile_number;
			$user->password = $new_pharmacies->password;
			$user->created_at = date('Y-m-d H:i:s');
			$user->save();
			
			$Onboardingrequest->dateofapproval = date('Y-m-d H:i:s');
			$Onboardingrequest->save();
		}
	}
	public function reject($id){
		$reject_reason = (isset($_REQUEST['reject_reason']))?$_REQUEST['reject_reason']:'';
		$Onboardingrequest = Onboardingrequest::find($id);
		$Onboardingrequest->reject_reason = $reject_reason;
		$Onboardingrequest->account_status = '2';
		$Onboardingrequest->save();
	}
	public function view($id){
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$Onboardingrequest = Onboardingrequest::find($id);
		$data = array();
		$data['onboardingrequest'] = $Onboardingrequest;
		$data['page_title'] = 'Onboarding Request detail';
		$data['page_condition'] = 'page_onboarfingrequestdetail';
		$data['site_title'] = 'Onboarding Request detail | ' . $this->data['site_title'];
        return view('onboardingrequest.detail', $data);
	}
}
