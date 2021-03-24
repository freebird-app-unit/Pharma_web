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

class pickupController extends Controller
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
		Auth::user()->user_type='logistic';
		$user_id = Auth::user()->id;
		$data = array();
		$data['page_title'] = 'Pickup';
		$data['page_condition'] = 'page_logistic_pickup';
		$data['site_title'] = 'Pickup | ' . $this->data['site_title'];
		$data['deliveryboy_list'] = new_pharma_logistic_employee::where('pharma_logistic_id', $user_id)->where('user_type', 'delivery_boy')->where('parent_type','logistic')->get();
        return view('logistic.pickup.index', $data);
    }
	public function getlist()
    {
		$user_id = Auth::user()->id;
		$user_type = Auth::user()->user_type;
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		//count total
		$total_res = DB::table('orders')->select('orders.*','users.name as customer_name','users.mobile_number as customer_number')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->where('order_status','pickup');

		if($user_type=='pharmacy'){
			$total_res = $total_res->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$total_res = $total_res->where('pharmacy_id',$parentuser_id);
			$total_res = $total_res->where('process_user_id',$user_id);
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
		$order_detail = DB::table('orders')->select('orders.*','delivery_charges.delivery_type as delivery_type', 'address_new.address as delivery_address','users.name as deliveryboyname','pharmacy.address as pickup_address')
		->leftJoin('users', 'users.id', '=', 'orders.deliveryboy_id')
		->leftJoin('delivery_charges', 'delivery_charges.id', '=', 'orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'orders.address_id')
		->leftJoin('users AS pharmacy', 'pharmacy.id', '=', 'orders.pharmacy_id')
		->where('order_status','pickup');

		if($user_type == 'pharmacy'){
			$order_detail = $order_detail->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
		}
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
				
				//$assign_to = get_name('users','name',$order->deliveryboy_id);
				//$time = get_order_time($order->id,$order->deliveryboy_id);
				$html.='<tr>
					<td><a href="'.url('/logistic/pickup/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->delivery_type.'</td>
					<td>'.$order->pickup_address.'</td>
					<td>'.$order->delivery_address.'</td>
					<td>'.$order->deliveryboyname.'</td>';
					/*$html.='<td><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Reject order" data-toggle="modal" data-target="#assign_modal">Re Assign</a>';  */

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
						<a class="page-link" onclick="getpickuplist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getpickuplist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
		$order_detail = DB::table('orders')->select('orders.*','delivery_charges.delivery_type as delivery_type','delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress','users.name as deliveryboyname')
		->leftJoin('users', 'users.id', '=', 'orders.deliveryboy_id')
		->leftJoin('users as ua', 'ua.id', '=', 'orders.pharmacy_id')
		->leftJoin('delivery_charges', 'delivery_charges.id', '=', 'orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'orders.address_id')
		->where('order_status','pickup')
		->where('orders.id',$id)->first();
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
        return view('logistic.pickup.order_details', $data);
		
	}
}
