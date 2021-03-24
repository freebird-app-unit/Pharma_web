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

class MyorderController extends Controller
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
		$data['page_title'] = 'My Orders';
		$data['page_condition'] = 'page_myorder';
		$data['site_title'] = 'My Orders | ' . $this->data['site_title'];
		//$data['deliveryboy_list'] = User::where('parentuser_id',$user_id)->where('user_type','delivery_boy')->get();
		if(Auth::user()->user_type=='seller'){
			$data['deliveryboy_list'] = User::where('parentuser_id',$user_id)->where('user_type','delivery_boy')->get();
		}else if(Auth::user()->user_type=='pharmacy'){
			$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'pharmacy')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->get();
		}
        return view('myorder.index', $data);
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
		$filter_start_date=(isset($_POST['filter_start_date']) && $_POST['filter_start_date']!='')?date('Y-m-d',strtotime(str_replace('/','-',$_POST['filter_start_date']))):'';
		$filter_end_date=(isset($_POST['filter_end_date']) && $_POST['filter_end_date']!='')?date('Y-m-d',strtotime(str_replace('/','-',$_POST['filter_end_date']))):'';

		//get list
		$order_detail = new_orders::select('new_orders.*','new_users.name as customer_name','new_users.mobile_number as customer_number', 'prescription.name as prescription_name', 'prescription.image as prescription_image')
		->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id');

		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('new_orders.pharmacy_id',$user_id);
			$order_detail = $order_detail->where('new_orders.process_user_id',$user_id);
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
		if($filter_start_date!='' && $filter_end_date!=''){
			$order_detail= $order_detail->where(function ($query) use($filter_start_date,$filter_end_date) {
				$query->whereRaw(
					  "(new_orders.created_at >= ? AND new_orders.created_at <= ?)", 
					  [$filter_start_date." 00:00:00", $filter_end_date." 23:59:59"]
					);
			});
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_orders.updated_at','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y h:i a',strtotime($order->created_at)):'';
				$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				$html.='<tr>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$created_at.'</td>';
					if($order->order_status=='new'){
						$html.='<td><a class="btn btn-success waves-effect waves-light" href="'.url('/orders/accept/'.$order->id).'" title="Accept order">Accept</a>
						<a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a></td>';
					}else if($order->order_status=='accept'){
						// if($order->process_user_id == $user_id){
						// 	$html.='<td><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" title="Assign order" data-toggle="modal" data-target="#assign_modal">Assign</a></td>';
						// }else{
							$html.='<td><a disabled class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" title="Assign order">Assign</a></td>';
						// }
					}else if($order->order_status=='assign'){
						$html.='<td><a class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" title="Out for delivery">Out for delivery</a></td>';
					}else if($order->order_status=='incomplete'){
						$html.='<td><a class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Incomplete">Incomplete</a></td>';
					}else if($order->order_status=='reject'){
						$html.='<td><a class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Rejected order">Rejected</a></td>';
					}else if($order->order_status=='complete'){
						$html.='<td><a class="btn btn-success btn-custom waves-effect waves-light" href="javascript:;" title="Delivered">Delivered</a></td>';
					}
					
					
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
						<a class="page-link" onclick="getmyorderlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getmyorderlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
	public function assign(Request $request)
    {
		//echo $request->reject_reason.'--'.$request->reject_id;exit;
		$user_id = Auth::user()->user_id;
		$order = Orders::find($request->assign_id);
		$order->deliveryboy_id = $request->delivery_boy;
		$order->order_status = 'assign';
		$order->save();
		
		$assign = new Orderassign();
		$assign->order_id = $request->assign_id;
		$assign->deliveryboy_id = $request->delivery_boy;
		$assign->created_at = date('Y-m-d H:i:s');
		$assign->updated_at = date('Y-m-d H:i:s');
		$assign->save();
		return redirect(route('acceptedorders.index'))->with('success_message', trans('Order Successfully assign'));
	}
	/* public function accept($id)
    {
		$user_id = Auth::user()->id;
		$order = Orders::find($id);
		$order->process_user_id = $user_id;
		$order->order_status = 'accept';
		$order->save();
		return redirect(route('orders.index'))->with('success_message', trans('Order Successfully accepted'));
	}
	 */
}
