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
use App\new_sellers;
class PharmacyrejectedController extends Controller
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
		$data['page_condition'] = 'page_pharmacyrejected';
		$data['site_title'] = 'Rejected order | ' . $this->data['site_title'];
        return view('pharmacyrejected.index', $data);
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
		
		
		//get list
		$order_detail = new_orders::select('new_orders.id','order_number','reject_cancel_reason','new_users.name as customer_name','new_users.mobile_number as customer_number','address_new.address as myaddress','new_orders.rejectby_user','new_orders.reject_user_id')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->where('new_orders.order_status','reject');
		
		$order_detail = $order_detail->where('new_orders.pharmacy_id',$user_id);

		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('new_users.name', 'like', '%'.$searchtxt.'%')
						->orWhere('new_users.mobile_number', 'like','%'.$searchtxt.'%')
						->orWhere('new_orders.order_number', 'like','%'.$searchtxt.'%');
            });
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_orders.updated_at','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$reject_by = $order->rejectby_user;
				if($order->rejectby_user == 'pharmacy'){
					$pharmacy = new_pharmacies::where('id',$order->reject_user_id)->first();
					$reject_by = $pharmacy->name;
				}else if($order->rejectby_user == 'seller'){
					$seller = new_sellers::where('id',$order->reject_user_id)->first();
					$reject_by = $seller->name;
				}else if($order->rejectby_user == 'deliveryboy'){
					$deliveryboy = new_pharma_logistic_employee::where('id',$order->reject_user_id)->first();
					if($deliveryboy->parent_type=='logistic'){
						$logi_code = new_logistics::where('id',$deliveryboy->pharma_logistic_id)->first();
						$reject_by = $logi_code->code;
					}else{
						$reject_by = $deliveryboy->name;
					}
					
				}
				$html.='<tr>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$order->myaddress.'</td>
					<td>'.$reject_by.'</td>
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
