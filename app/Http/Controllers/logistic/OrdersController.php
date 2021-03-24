<?php

namespace App\Http\Controllers\logistic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\Address;
use App\Orders;
use App\Rejectreason;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
class OrdersController extends Controller
{
	public function __construct()
    {
		parent::__construct();
		$this->middleware('auth:new_logistics');
    }
    public function index()
    {
		if(Auth::user()->user_type!='logistic'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Orders';
		$data['page_condition'] = 'page_orders';
		$data['site_title'] = 'Orders | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::get();
        return view('logistic.orders.index', $data);
    }
	public function getlist()
    {
		$user_id = Auth::user()->id;
		$user_type = Auth::user()->user_type;
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
		->where('order_status','new');
		if($user_type=='pharmacy'){
			$total_res = $total_res->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$total_res = $total_res->where('pharmacy_id',$parentuser_id);
		}
		if($searchtxt!=''){
			$total_res= $total_res->where(function ($query) use($searchtxt) {
                $query->where('users.name', 'like', $searchtxt.'%')
						->orWhere('users.mobile_number', 'like', $searchtxt.'%');
            });
		}
		$total_res= $total_res->get();
		$total = count($total_res);
		$total_page = ceil($total/$per_page);
		//count total
		
		//get list
		$order_detail = DB::table('orders')->select('orders.*','users.name as customer_name','users.mobile_number as customer_number', 'prescription.name as prescription_name', 'prescription.image as prescription_image')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->leftJoin('prescription', 'prescription.id', '=', 'orders.prescription_id')
		->where('order_status','new');
		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
		}
		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('users.name', 'like', $searchtxt.'%')
						->orWhere('users.mobile_number', 'like', $searchtxt.'%')
						->orWhere('prescription.name', 'like', $searchtxt.'%');
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

				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				
				$html.='<tr>
					<td><a href="'.$order->order_number.'"></td>
					<td>'.str_replace('_',' ',$order->order_type).'</td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$created_at.'</td>'; 

					$html.='<td><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a> <a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a></td>';
						
					// $html.='<td>';
					// if($homepage!=''){
					// 	$html.='<a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$order->id.'?home').'" title="Accept order">Accept</a>';
					// }else{
					// 	$html.='<a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$order->id).'" title="Accept order">Accept</a>';
					// }
					// $html.='<a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';
					// $html.='</td>';
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
	public function accept($id)
    {
		$user_id = Auth::user()->id;
		$order = Orders::find($id);
		$order->process_user_id = $user_id;
		$order->order_status = 'accept';
		$order->accept_date=date('Y-m-d H:i:s');
		$order->save();
		if(isset($_REQUEST['home'])){
			return redirect(route('home'))->with('success_message', trans('Order Successfully accepted'));
		}else{
			return redirect(route('logistic.orders.index'))->with('success_message', trans('Order Successfully accepted'));
		}
	}
	public function reject(Request $request)
    {
		//echo $request->reject_reason.'--'.$request->reject_id;exit;
		$user_id = Auth::user()->id;
		$order = Orders::find($request->reject_id);
		$order->process_user_id = $user_id;
		$order->order_status = 'reject';
		$order->accept_date=date('Y-m-d H:i:s');
		$order->rejectreason_id = $request->reject_reason;
		$order->save();
		if(isset($_REQUEST['home'])){
			return redirect(route('home'))->with('success_message', trans('Order Successfully rejected'));
		}else{
			return redirect(route('logistic.orders.index'))->with('success_message', trans('Order Successfully rejected'));
		}
	}
	public function prescription($id)
    {
		$user_id = Auth::user()->id;
		$order = Orders::find($id);
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
		$data['address'] = $address;
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::get();
        return view('logistic.orders.prescription', $data);
		
	}
	public function order_details($id)
    {
		$user_id = Auth::user()->id;
		$order = Orders::select('orders.*', 'prescription.name as prescription_name', 'prescription.image as prescription_image')->leftJoin('prescription', 'prescription.id', '=', 'orders.prescription_id')->where('orders.id', $id)->first();
		
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
		$data['address'] = $address;
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::get();
        return view('logistic.orders.order_details', $data);
		
	}
}
