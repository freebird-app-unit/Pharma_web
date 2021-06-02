<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\Orders;
use App\Cancelreason;
use App\Orderassign;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;

use App\new_pharma_logistic_employee;
use App\new_logistics;
use App\new_orders;
use App\new_order_history;
use App\new_users;
use App\new_pharmacies;

class CanceledController extends Controller
{
    public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='seller'){
			return redirect(route('home'));
		}
		$user_id = Auth::user()->user_id;
		$data = array();
		$data['page_title'] = 'Canceled Orders';
		$data['page_condition'] = 'page_canceled';
		$data['site_title'] = 'Canceled order | ' . $this->data['site_title'];
        return view('canceled.index', $data);
    }
	public function getlist()
    {
		$user_id = Auth::user()->user_id;
		$user_type = Auth::user()->user_type;
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		
		$order_detail = new_order_history::select('new_order_history.id','new_order_history.order_type','order_number','reject_cancel_reason','new_users.name as customer_name','new_users.mobile_number as customer_number','address_new.address as myaddress', 'prescription.name as prescription_name', 'prescription.image as prescription_image','new_order_history.rejectby_user','new_order_history.reject_user_id')//'cancelreason_id',
		->leftJoin('new_users', 'new_users.id', '=', 'new_order_history.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_order_history.address_id')
		->leftJoin('prescription', 'prescription.id', '=', 'new_order_history.prescription_id')
		->where('new_order_history.order_status','cancel');
		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('new_order_history.pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
		}
		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('new_users.name', 'like', $searchtxt.'%')
						->orWhere('new_users.mobile_number', 'like', $searchtxt.'%')
						->orWhere('new_order_history.order_number', 'like', $searchtxt.'%');
            });
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_order_history.cancel_datetime','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				//$created_at = ($order->created_at!='')?date('d-M-Y',strtotime($order->created_at)):'';
				//$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				$reason = get_cancel_reason($order->cancelreason_id);
				
				$cancelled_by = "";
				if($order->rejectby_user == 'seller'){
					$cancelled_by = get_name('new_pharma_logistic_employee','name',$order->reject_user_id);
				}else if($order->rejectby_user == 'customer'){
					$cancelled_by = get_name('new_users','name',$order->reject_user_id);
				}elseif ($order->rejectby_user == 'pharmacy') {
					$cancelled_by = get_name('new_pharmacies','name',$order->reject_user_id);
				}
				
				/*<a href="'.url('/orders/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a><td>'.$order->prescription_name.'</td>
					<td>'.$order->order_note.'</td>*/
				if($order->order_type == "manual_order"){
					$html.='<tr><td><a href="'.url('/orders/order_details_manual/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				}else{
					$html.='<tr><td><a href="'.url('/orders/order_details/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				}
				$html.='<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$order->myaddress.'</td>
					<td class="text-danger">'.$order->reject_cancel_reason.'</td>
					<td class="text-danger">'.$cancelled_by.'</td>';
					
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
						<a class="page-link" onclick="getcanceledlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getcanceledlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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

	public function logistic_index()
    {
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		Auth::user()->user_type='logistic';
		$user_id = Auth::user()->user_id;
		$data = array();
		$data['page_title'] = 'Canceled order';
		$data['page_condition'] = 'page_canceled_logistic';
		$data['site_title'] = 'Canceled order | ' . $this->data['site_title'];
        return view('logistic.canceled.index', $data);
    }
	public function logistic_getlist()
    {
		$user_id = Auth::user()->user_id;
		$user_type = Auth::user()->user_type;
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		
		//get list
		$order_detail = new_order_history::select('new_order_history.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','new_pharmacies.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname', 'new_users.name AS customer_name', 'new_users.mobile_number AS customer_number')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_order_history.deliveryboy_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_order_history.pharmacy_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_order_history.address_id')
		->leftJoin('new_users', 'new_users.id', '=', 'new_order_history.customer_id')
		->where('new_order_history.order_status','cancel');

		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
		}else if($user_type=='logistic'){
			$order_detail = $order_detail->where('new_order_history.logistic_user_id', $user_id);
		}

		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('new_users.name', 'like', '%'.$searchtxt.'%')
						->orWhere('new_users.mobile_number', 'like', '%'.$searchtxt.'%')
						->orWhere('new_order_history.order_number', 'like', '%'.$searchtxt.'%');
            });
		}

		$total= $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_order_history.cancel_datetime','desc');
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
					<td><a href="'.url('/logistic/canceled/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a></td>
					<td>'.str_replace('_',' ',$order->order_type).'</td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$order->myaddress.'</td>
					<td class="text-danger">'.$order->logistic_reject_reason.'</td>';
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
						<a class="page-link" onclick="getcanceledlistlogistic('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getcanceledlistlogistic('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
	public function logistic_order_details($id)
    {
		$user_id = Auth::user()->user_id;
		$order = new_order_history::select('new_order_history.*')->where('new_order_history.id', $id)->first();
		$order_detail = DB::table('new_order_history')->select('new_order_history.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','new_pharmacies.address as pharmacyaddress',
		'new_pharmacies.name as pharmacyname','new_pharmacies.mobile_number as pharmacymobilenumber','new_pharma_logistic_employee.name as deliveryboyname')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_order_history.deliveryboy_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_order_history.pharmacy_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_order_history.address_id')
		->where('new_order_history.order_status','cancel')
		->where('new_order_history.id',$id)->first();

		$customer = new_users::where('id',$order->customer_id)->first();

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
		$data['order_detail'] = $order_detail;
		// $data['deliveryboy_list'] = User::where('parentuser_id',$user_id)->where('user_type','delivery_boy')->get();
		$data['customer'] = $customer;
		$data['address'] = $address;
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
		// $data['reject_reason'] = Rejectreason::get();
        return view('logistic.canceled.order_details', $data);
		
	}
}
