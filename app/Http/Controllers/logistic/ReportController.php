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

class ReportController extends Controller
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
		$user_id = Auth::user()->id;
		$data = array();
		$data['page_title'] = 'Reports';
		$data['page_condition'] = 'page_report';
		$data['site_title'] = 'Reports | ' . $this->data['site_title'];
		$data['deliveryboy_list'] = User::where('parentuser_id',$user_id)->where('user_type','delivery_boy')->get();
        return view('logistic.report.index', $data);
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
		->where('order_status','assign');
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
		$order_detail = DB::table('orders')->select('orders.*','users.name as customer_name','users.mobile_number as customer_number', 'prescription.name as prescription_name', 'prescription.image as prescription_image')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->leftJoin('prescription', 'prescription.id', '=', 'orders.prescription_id')
		->where('order_status','assign');
		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
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
				$assign_to = get_name('users','name',$order->deliveryboy_id);
				$time = get_order_time($order->id,$order->deliveryboy_id);
				$html.='<tr>
					<td><a href="'.url('logistic/orders/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a></td>
					<td>'.str_replace('_',' ',$order->order_type).'</td>
					<td>'.$order->prescription_name.'</td>
					<td>'.$order->order_note.'</td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td class="text-warning">'.$assign_to.'</td>
					<td><span class="label label-warning">'.$time.'</span></td>';
					
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
						<a class="page-link" onclick="getupcominglist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getupcominglist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
