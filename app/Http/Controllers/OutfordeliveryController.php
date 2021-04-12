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

class OutfordeliveryController extends Controller
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
		$data['page_title'] = 'Live Orders';
		$data['page_condition'] = 'page_outfordelivery';
		$data['site_title'] = 'Out for delivery | ' . $this->data['site_title'];
		$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'pharmacy')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->get();
        return view('outfordelivery.index', $data);
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
		
		$order_detail = new_orders::select('new_orders.id','assign_datetime','order_number','deliveryboy_id','new_users.name as customer_name','new_users.mobile_number as customer_number','new_users.id as customerid', 'prescription.name as prescription_name', 'prescription.image as prescription_image', 'delivery_user.name as assign_to', 'logistic.name as logistic_name', 'logistic.code as logistic_code')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('new_logistics AS logistic', 'logistic.id', '=', 'new_orders.logistic_user_id')
		->leftJoin('new_pharma_logistic_employee AS delivery_user', 'delivery_user.id', '=', 'new_orders.deliveryboy_id')
		->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')
		->where('new_orders.order_status','assign');
		
		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('new_orders.pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
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

		$order_detail = $order_detail->orderby('new_orders.assign_datetime','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = (isset($order->assign_datetime) && $order->assign_datetime!='')?date('d-M-Y h:i a',strtotime($order->assign_datetime)):'';
				//$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				$time = get_order_time($order->id, $order->deliveryboy_id);
				//$assign_to = ($order->assign_to !== null)?($order->assign_to):($order->logistic_code);
				$assign_to = $order->logistic_code;
				$html.='<tr>
					<td>'.$order->customer_name.'</td>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td class="text-warning">'.$assign_to.'</td>
					<td><span class="label label-warning">'.$created_at.'</span></td>';
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
						<a class="page-link" onclick="getoutfordeliverylist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getoutfordeliverylist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
