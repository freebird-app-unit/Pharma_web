<?php

namespace App\Http\Controllers\logistic;

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

class upcomingController extends Controller
{
    public function __construct()
    {
		parent::__construct();
		$this->middleware('auth:new_logistics');
    }
    public function index()
    {
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		$user_id = Auth::user()->id;
		$data = array();
		$data['page_title'] = 'Upcoming Orders';
		$data['page_condition'] = 'page_upcomingorders';
		$data['site_title'] = 'Upcoming Orders | ' . $this->data['site_title'];
		if(Auth::user()->user_type='logistic'){
			$data['deliveryboy_list'] = new_pharma_logistic_employee::where('pharma_logistic_id',$user_id)->where('user_type','delivery_boy')->where('parent_type','logistic')->get();
		}
		$data['reject_reason'] = Rejectreason::get();
        return view('logistic.upcoming.index', $data);
	}
	
	public function getlist()
    {

		$user_id = Auth::user()->id;
		$user_type = 'logistic';

		$html='';
		$pagination='';
		$total_summary='';
		$homepage = (isset($_REQUEST['home']))?$_REQUEST['home']:'';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';

		//count total
		$total_res = DB::table('orders')->select('orders.*','users.name as customer_name','users.mobile_number as customer_number')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->where('order_status','assign');
		$total_res = $total_res->where('logistic_id',$user_id);
		
		if($searchtxt!=''){
			$total_res= $total_res->where(function ($query) use($searchtxt) {
                $query->where('users.name', 'like', $searchtxt.'%')
						->orWhere('users.mobile_number', 'like', $searchtxt.'%');
            });
		}

		$total_res = $total_res->get();
		$total = count($total_res);
		$total_page = ceil($total/$per_page);
		
		//count total
		//get list
		$order_detail = DB::table('orders')->select('orders.*','delivery_charges.delivery_type as delivery_type', 'address_new.address as DestAddress', 'u1.address as PickupAddress')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->leftJoin('users as u1', 'u1.id', '=', 'orders.logistic_id')
		->leftJoin('delivery_charges', 'delivery_charges.id', '=', 'orders.delivery_charges_id')
		->leftJoin('order_assign', 'order_assign.order_id', '=', 'orders.id')
		->leftJoin('address_new', 'address_new.id', '=', 'orders.address_id')
		->where('order_assign.deliveryboy_id', '=', null)
		->where('orders.order_status','assign')
		->where('orders.logistic_id',$user_id);

		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->Where('delivery_charges.delivery_type', 'like', $searchtxt.'%')
				->orWhere('orders.order_number', 'like', $searchtxt.'%');
            });
		}

		$order_detail = $order_detail->orderby('orders.id','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);

		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				
				$created_at = ($order->created_at!='')?date('d-M-Y',strtotime($order->created_at)):'';
				$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->invoice)) {
					if (file_exists(storage_path('app/public/uploads/invoice/'.$order->invoice))){
						$image_url = asset('storage/app/public/uploads/invoice/' . $order->invoice);
					}
				}
				/*<a href="'.url('/orders/prescription/'.$order->id).'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->order_number.'</span>
				<td>'.$order->prescription_name.'</td>
					<td>'.$order->order_note.'</td>*/
				$html.='<tr>
				<td><a href="'.url('/logistic/upcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a></td>
				<td>'.$order->delivery_type.'</td>
				<td>'.$order->PickupAddress.'</td>
				<td>'.$order->DestAddress.'</td>
				<td>'.$order->order_amount.'</td>'; //static order amount 
				$html.='<td><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a></td>';
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

	public function order_details($id)
    {
		$user_id = Auth::user()->id;
		$order = Orders::select('orders.*')->where('orders.id', $id)->first();
		$order_detail = DB::table('orders')->select('orders.*','delivery_charges.delivery_type as delivery_type','delivery_charges.delivery_price as deliveryprice', 'address_new.address as DestAddress', 'u1.address as PickupAddress')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->leftJoin('users as u1', 'u1.id', '=', 'orders.logistic_id')
		->leftJoin('delivery_charges', 'delivery_charges.id', '=', 'orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'orders.address_id')
		->where('logistic_id',$user_id)
		->where('orders.id',$id)
		->where('order_status','assign')->first();

		$customer = User::where('id',$order->customer_id)->first();
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
		$data['customer'] = $customer;
		$data['order_detail'] = $order_detail;
		$data['deliveryboy_list'] = User::where('parentuser_id',$user_id)->where('user_type','delivery_boy')->get();
		$data['address'] = $address;
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::get();
        return view('logistic.upcoming.order_details', $data);
		
	}

	public function assign(Request $request)
    {
		$user_id = Auth::user()->id;
		$order = Orders::find($request->assign_id);
		$delivery_boy = User::find($request->delivery_boy);
		$order->deliveryboy_id = $request->delivery_boy;
		$order->order_status = 'assign';
		$order->assign_date = date('Y-m-d H:i:s');
		$order->save();
		DB::connection()->enableQueryLog();

		$orderAssignCount = Orderassign::where('order_status', 'new')->Where('order_id', $request->assign_id)->count();
		if($orderAssignCount > 0){
			$orderAssignId = Orderassign::where('order_status', 'new')->Where('order_id', $request->assign_id)->first();
			
			$orderAssign = Orderassign::find($orderAssignId->id);
			$orderAssign->deliveryboy_id = $delivery_boy->id;
			$orderAssign->order_id = $request->assign_id;
			$orderAssign->order_status = 'assign';
			$orderAssign->assign_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
			$orderAssign->save();
		} else {
			$orderAssign = new Orderassign();
			$orderAssign->deliveryboy_id = $request->delivery_boy;
			$orderAssign->logistic_id = $delivery_boy->parentuser_id;
			$orderAssign->created_at = date('Y-m-d H:i:s');
			$orderAssign->order_id = $request->assign_id;
			$orderAssign->order_status = 'assign';
			$orderAssign->assign_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
			$orderAssign->save();
		}
	
		return redirect(route('logistic.outfordelivery.index'))->with('success_message', trans('Order Successfully assign'));
	}

	public function reject(Request $request)
    {
		dd($request->assign_id);
		$user_id = Auth::user()->id;

		$order = Orders::find($request->reject_id);
		$order->process_user_id = $user_id;
		$order->logistic_id = null;
		$order->deliveryboy_id = 0;
		$order->assign_date = null;
		$order->order_status = 'accept';
		// $order->save();

		$orderAssignCount = Orderassign::whereNull('deliveryboy_id')->Where('order_id', $request->reject_id)->count();

		if($orderAssignCount > 0){
			$orderAssign = Orderassign::find($request->assign_id)->whereNull('deliveryboy_id');
			$orderAssign->order_status = 'reject';
			$orderAssign->logistic_reject_reason = $request->reject_reason;
			$orderAssign->reject_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
		} else {
			$orderAssign = new Orderassign();
			$orderAssign->logistic_id = $user_id;
			$orderAssign->order_id = $request->reject_id;
			$orderAssign->order_status = 'reject';
			$orderAssign->logistic_reject_reason = $request->reject_reason;
			$orderAssign->reject_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
		}
		$orderAssign->save();

		if(isset($_REQUEST['home'])){
			return redirect(route('home'))->with('success_message', trans('Order Successfully rejected'));
		}else{
			return redirect(route('logistic.upcoming.index'))->with('success_message', trans('Order Successfully rejected'));
		}
	}
}
