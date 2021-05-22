<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\new_logistics;
use App\new_orders;
use App\new_pharmacies;
use App\new_pharma_logistic_employee;
use Auth;
use App\Orderassign;
use App\Rejectreason;
use App\new_users;
use App\new_address;
use DB;
use Helper;
class LogisticordersController extends Controller
{
    public function index()
    {
    	$data = array();
		$data['page_title'] = 'Logistic Orders';
		$data['page_condition'] = 'page_logisticorders';
		$data['site_title'] = 'Logistic Orders | ' . $this->data['site_title'];
		//$data['logistic_list'] = new_logistics::where('is_active','1')->get();
		$data['reject_reason'] = Rejectreason::where('type', 'pharmacy')->get();
		return view('logisticorders.index', $data);
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
		
		$order_detail = new_orders::select('new_orders.id','accept_datetime','order_number','new_users.name as customer_name','new_orders.process_user_id','new_orders.process_user_type','new_users.mobile_number as customer_number','new_users.id as customerid','address_new.address as myaddress','new_delivery_charges.delivery_type as delivery_type', 'process_user.name as process_user_name', 'process_employee.name as process_employee_name')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('new_pharmacies AS process_user', 'process_user.id', '=', 'new_orders.process_user_id')
		->leftJoin('new_pharma_logistic_employee AS process_employee', 'process_employee.id', '=', 'new_orders.process_user_id')
		->where('new_orders.order_status','accept')
		->where('new_orders.is_external_delivery','1')
		->where('new_orders.logistic_user_id','-1');
		
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

		$order_detail = $order_detail->orderby('new_orders.accept_datetime','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		$queries = DB::getQueryLog();
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$accept_date = ($order->accept_datetime!='')?date('d-M-Y h:i a', strtotime($order->accept_datetime)):'';

				$process_user_name = '';
				if($order->process_user_type == 'pharmacy'){
					$name = new_pharmacies::where('id',$order->process_user_id)->first();
					$process_user_name = $name->name;
				} else {
					$name = new_pharma_logistic_employee::where('id',$order->process_user_id)->first();
					$process_user_name = $name->name;
				}

				$html.='<tr>
					<td>'.ucwords(strtolower($order->customer_name)).'</td>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->myaddress.'</td>
					<td>'.$process_user_name.'</td>
					<td>'.$accept_date.'</td>';
					/*$html.='<td><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Reject order" data-toggle="modal" data-target="#assign_modal">Assign</a>';*/
					$html.='<td><a href="'.url('/logisticorders/assign/'.$order->id).'" class="btn btn-warning btn-custom waves-effect waves-light" title="Accept order">Assign</a>';
					$html.='<a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a>';
					$html.='</td></tr>';
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
						<a class="page-link" onclick="getlogisticorderslist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getlogisticorderslist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
	public function assign($id)
    {
    	$order = new_orders::where('id',$id)->first();
    	$delivery_address_check = new_address::where('id',$order->address_id)->first();
    	if($delivery_address_check->city == 'Rajkot'){
    		
    	}
    	$assign->logistic_user_id=$request->logistic_id;
    	$assign->order_status='assign';
    	$assign->save();

    	$assign_order = Orderassign::where('order_id',$request->assign_id)->first();
    	$assign_order->logistic_id = $request->logistic_id;
    	$assign_order->save();

    	return redirect(route('logisticorders.index'))->with('success_message', trans('Order Successfully Assign'));
    }

    public function reject(Request $request)
    {
		$user_id = Auth::user()->id;
		$order = new_orders::find($request->reject_id);
		$order->process_user_id = $user_id;
		$order->order_status = 'reject';
		$order->rejectby_user = 'pharmacy';
		$order->reject_user_id = $user_id;
		$order->reject_datetime = date('Y-m-d H:i:s');
		$order->reject_cancel_reason = $request->reject_reason;
		$order->save();

		$customer = new_users::find($order->customer_id);
		$ids = array();
		$ids[] = $customer->fcm_token;
		$receiver_id = array();
		$receiver_id[] = $customer->id;
		if (count($ids) > 0) {					
			Helper::sendNotificationUser($ids, 'Order Number '.$order->order_number, 'Order Rejected', $user_id, 'pharmacy', $customer->id, 'user', $customer->fcm_token);
		}

		return redirect(route('logisticorders.index'))->with('success_message', trans('Order Successfully Reject'));
	}
}
