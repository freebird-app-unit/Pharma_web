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
use App\new_order_history;
use App\new_users;
use App\new_pharmacies;

class RejectedController extends Controller
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
		$data['page_title'] = 'Rejected Orders';
		$data['page_condition'] = 'page_rejected';
		$data['site_title'] = 'Rejected order | ' . $this->data['site_title'];
        return view('rejected.index', $data);
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
		//count total
		// $total_res = DB::table('orders')->select('orders.*','users.name as customer_name','users.mobile_number as customer_number')
		// ->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		// ->where('order_status','reject');
		// if($user_type=='pharmacy'){
		// 	$total_res = $total_res->where('pharmacy_id',$user_id);
		// }else if($user_type=='seller'){
		// 	$parentuser_id = Auth::user()->parentuser_id;
		// 	$total_res = $total_res->where('pharmacy_id',$parentuser_id);
		// 	$total_res = $total_res->where('process_user_id',$user_id);
		// }
		
		// if($searchtxt!=''){
		// 	$total_res= $total_res->where(function ($query) use($searchtxt) {
        //         $query->where('users.name', 'like', $searchtxt.'%')
		// 				->orWhere('users.mobile_number', 'like', $searchtxt.'%');
        //     });
		// }
		// $total_res= $total_res->get();
		// $total = count($total_res);
		// $total_page = ceil($total/$per_page);
		//count total
		
		//get list
		$order_detail = new_order_history::select('new_order_history.id','order_number','reject_cancel_reason','new_users.name as customer_name','new_users.mobile_number as customer_number','address_new.address as myaddress')
		->leftJoin('new_users', 'new_users.id', '=', 'new_order_history.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_order_history.address_id')
		->where('new_order_history.order_status','reject');
		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('new_order_history.pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
		}

		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('new_users.name', 'like', '%'.$searchtxt.'%')
						->orWhere('new_users.mobile_number', 'like','%'.$searchtxt.'%')
						->orWhere('new_order_history.order_number', 'like','%'.$searchtxt.'%');
            });
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_order_history.updated_at','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$html.='<tr>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$order->myaddress.'</td>
					<td class="text-danger">'.$order->reject_cancel_reason.'</td></tr>';
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
						<a class="page-link" onclick="getrejectedlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getrejectedlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
