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
use App\new_users;
use App\new_pharmacies;
use App\new_logistics;
use App\new_orders;
use App\new_delivery_charges;
use File;
use Image;
use Storage;
use App\SellerModel\invoice;

class LogisticupcomingController extends Controller
{
    public function __construct()
    {
		parent::__construct();
		$this->middleware('auth');
    }
    public function index()
    {
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		$user_id = Auth::user()->user_id;
		$data = array();
		$data['page_title'] = 'Upcoming Orders';
		$data['page_condition'] = 'page_upcomingorders';
		$data['site_title'] = 'Upcoming Orders | ' . $this->data['site_title'];
		if(Auth::user()->user_type='logistic'){
			$data['deliveryboy_list'] = new_pharma_logistic_employee::where(['pharma_logistic_id'=> $user_id, 'is_active'=> 1])->where('user_type','delivery_boy')->where('parent_type','logistic')->get();
		}
		$data['reject_reason'] = Rejectreason::where('type', 'logistic')->get();
        return view('logisticupcoming.index', $data);
	}

	public function logistic_upcoming_getlist()
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
		$order_detail = new_orders::select('new_orders.*','new_delivery_charges.delivery_type as delivery_type', 'address_new.address as delivery_address','new_pharma_logistic_employee.name as sellername','new_pharmacies.address as pickup_address')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.process_user_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
		->leftJoin('order_assign', 'order_assign.order_id', '=', 'new_orders.id')
		->where(['new_orders.order_status'=>'assign','order_assign.order_status'=>'new'])
		->where('order_assign.logistic_id','<>',NULL)
		->where('order_assign.logistic_id','<>',-1);

		if($user_type == 'pharmacy'){
			$order_detail = $order_detail->where('new_orders.pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
		}
		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->Where('new_delivery_charges.delivery_type', 'like', '%'.$searchtxt.'%')
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
				$invoice = invoice::where('order_id',$order->id)->first();
                $image_url = '';
                if(!empty($invoice)){
	                	if($invoice->invoice!=''){
						$destinationPath = base_path() . '/storage/app/public/uploads/invoice/'.$invoice->invoice;
						if(file_exists($destinationPath)){
							$image_url = url('/').'/storage/app/public/uploads/invoice/'.$invoice->invoice;
						}else{
							$image_url = url('/').'/uploads/placeholder.png';
						}
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
                }
				$html.='<tr>
					<td style="text-align:center;"><a href="'.url('/logisticupcoming/order_details/'.$order->id).'"><img src="'.$image_url.'" width="40"/><span>'.$order->order_number.'</span></a></td>
					<td style="text-align:center;">'.$order->delivery_type.'</td>
					<td style="text-align:center;">'.$order->pickup_address.'</td>
					<td style="text-align:center;">'.$order->delivery_address.'</td>
					<td style="text-align:center;">'.$order->sellername.'</td>
					<td style="text-align:center;">'.$order->order_amount.'</td>
					<td style="text-align:center;">'.$order->assign_datetime.'</td>';
				$html.='<td style="text-align:center;"><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" data-toggle="modal" data-target="#assign_modal">Assign</a><a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Reject</a></td>';
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

	public function assign(Request $request)
    {
		$user_id = Auth::user()->id;
		$order = new_orders::find($request->assign_id);
		$delivery_boy = new_pharma_logistic_employee::find($request->delivery_boy);
		$order->deliveryboy_id = $request->delivery_boy;
		$order->order_status = 'assign';
		$order->assign_datetime = date('Y-m-d H:i:s');
		$order->save();
		DB::connection()->enableQueryLog();

		$ids = array();
		$ids[] = $delivery_boy->fcm_token;
		$receiver_id = array();
		$receiver_id[] = $delivery_boy->id;
		if (count($ids) > 0) {				
			Helper::sendNotificationDeliveryboy($ids, 'Order Number '.$order->order_number, 'Order Assign', $user_id, 'pharmacy', $delivery_boy->id, 'delivery_boy', $delivery_boy->fcm_token);
		}

		$orderAssignCount = Orderassign::where('order_status', 'new')->Where('order_id', $request->assign_id)->count();
		if($orderAssignCount > 0){
			$orderAssignId = Orderassign::where('order_status', 'new')->Where('order_id', $request->assign_id)->first();
			
			$orderAssign = Orderassign::find($orderAssignId->id);
			$orderAssign->deliveryboy_id = $delivery_boy->id;
			$orderAssign->order_id = $request->assign_id;
			$orderAssign->order_status = 'assign';
			$orderAssign->assign_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
			$orderAssign->save();
		} else {
			$orderAssign = new Orderassign();
			$orderAssign->deliveryboy_id = $request->delivery_boy;
			$orderAssign->logistic_id = $delivery_boy->parentuser_id;
			$orderAssign->created_at = date('Y-m-d H:i:s');
			$orderAssign->order_id = $request->assign_id;
			$orderAssign->order_status = 'assign';
			$orderAssign->assign_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
			$orderAssign->save();
		}
	
		return redirect(route('logisticupcoming.index'))->with('success_message', trans('Order Successfully assign'));
	}

	public function reject(Request $request)
    {
		$user_id = Auth::user()->id;

		$order = new_orders::find($request->reject_id);
		$customer = new_users::find($order->customer_id);
		$order->process_user_id = $user_id;
		$order->logistic_id = null;
		$order->deliveryboy_id = 0;
		$order->reject_datetime = null;
		$order->order_status = 'reject';
		// $order->save();
		if (count($ids) > 0) {					
			Helper::sendNotificationUser($ids, 'Order Number '.$order->order_number, 'Order Assigned', $user_id, 'pharmacy', $customer->id, 'user', $customer->fcm_token);
		}
		$orderAssignCount = Orderassign::whereNull('deliveryboy_id')->Where('order_id', $request->reject_id)->count();
		if($orderAssignCount > 0){
			$orderAssign = Orderassign::where('order_id',$request->reject_id)->first();
			$orderAssign->order_status = 'reject';
			$orderAssign->reject_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
		} else {
			$orderAssign = new Orderassign();
			$orderAssign->logistic_id = $user_id;
			$orderAssign->order_id = $request->reject_id;
			$orderAssign->order_status = 'reject';
			$orderAssign->logistic_reject_reason = $request->reject_reason;
			$orderAssign->reject_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
		}
		$orderAssign->save();

		if(isset($_REQUEST['home'])){
			return redirect(route('home'))->with('success_message', trans('Order Successfully rejected'));
		}else{
			return redirect(route('logisticupcoming.index'))->with('success_message', trans('Order Successfully rejected'));
		}
	}

	public function order_details($id)
    {
		$user_id = Auth::user()->id;
		$order = new_orders::select('new_orders.*')->where('new_orders.id', $id)->first();
		$order_detail = new_orders::select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as delivery_address','new_pharma_logistic_employee.name as deliveryboyname','new_pharmacies.address as pickup_address','new_users.name as name','new_users.mobile_number as mobile_number','new_pharmacies.name as pharmacyname','new_pharmacies.mobile_number as pharmacymobile_number','new_pharmacies.address as pharmacyaddress','prescription.image as preimage','prescription.name as prename')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
		->where(['new_orders.order_status'=>'assign','new_orders.id'=>$id])->first();
		
		$data = array();
		$data['order'] = $order;
		$data['order_detail'] = $order_detail;
		$data['page_title'] = 'Order Details';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'order detail | ' . $this->data['site_title'];
        return view('logisticupcoming.order_details', $data);
		
	}

}
