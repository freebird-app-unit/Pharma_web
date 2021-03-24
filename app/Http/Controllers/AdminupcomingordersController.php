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
use App\delivery_charges;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;

use App\new_pharma_logistic_employee;
use App\new_logistics;
use App\new_orders;
use App\new_users;
use App\new_pharmacies;
use App\new_delivery_charges;

class AdminupcomingordersController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		$user_id = Auth::user()->user_id;
		$data = array();
		$data['page_title'] = 'Live Orders';
		$data['page_condition'] = 'page_adminupcomingorders';
		$data['site_title'] = 'Accepted Orders | ' . $this->data['site_title'];
		$data['pharmacies'] = new_pharmacies::where('is_active',1)->get();
        $data['logistics'] = new_logistics::where('is_active',1)->get();
		$data['id'] = $user_id;
		return view('adminupcomingorders.index', $data);
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
		$pharmacy_id=(isset($_POST['pharmacy_id']) && $_POST['pharmacy_id']!='')?$_POST['pharmacy_id']:'';
		$logistic_id=(isset($_POST['logistic_id']) && $_POST['logistic_id']!='')?$_POST['logistic_id']:'';
		$show_order = array('new','payment_pending');
		$order_detail = new_orders::select('new_orders.*','new_users.name as customer_name','new_users.mobile_number as customer_number','address_new.address as myaddress','new_delivery_charges.delivery_type as delivery_type','new_pharmacies.name as pharmacy_name')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
		->whereIn('new_orders.order_status',$show_order);
		
		if($pharmacy_id != ''){
			$order_detail = $order_detail->where('new_orders.pharmacy_id',$pharmacy_id);
		}
		if($logistic_id !=''){
			$order_detail = $order_detail->where('new_orders.logistic_user_id', $logistic_id);
		}

		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('new_users.name', 'like', '%'.$searchtxt.'%')
				->orWhere('new_users.mobile_number', 'like', '%'.$searchtxt.'%')
				->orWhere('new_orders.order_number', 'like', '%'.$searchtxt.'%');
            });
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_orders.id','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		$queries = DB::getQueryLog();
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y h:i a', strtotime($order->created_at)):'';
				if($order->is_external_delivery == 1){
					$order_type = 'Paid';
				}else{
					$order_type = 'Free';
				}
				$html.='<tr>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$order->myaddress.'</td>
					<td>'.$order->pharmacy_name.'</td>
					<td>'.$order_type.'</td>
					<td>'.$created_at.'</td></tr>';
			}
			$prev_disabled_style = "";
			if($page==1){
				$prev='disabled';
				$prev_disabled_style = "pointer-events: none;";
			}else{
				$prev='';
			}
			$next_disabled_style = "";
			if($total_page==$page){
				$next='disabled';
				$next_disabled_style = "pointer-events: none;";
			}else{
				$next='';
			}
			$pagination.='<li class="page-item '.$prev.'">
						<a class="page-link" onclick="getupcomingorderslist('.($page-1).')" style="'.$prev_disabled_style.'" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getupcomingorderslist('.($page+1).')" style="'.$next_disabled_style.'" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
}
