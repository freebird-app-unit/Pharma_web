<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\new_orders;
use App\new_order_history;
use App\new_pharmacies;
use App\new_logistics;
use App\new_users;
use App\new_pharma_logistic_employee;
use Auth;
use DB;
use PhpParser\Node\Stmt\Else_;

class OrderfilterController extends Controller{
    public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
	}

    public function index()
    {	
        $user_id = Auth::user()->user_id;
        $user_type = Auth::user()->user_type;

		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
        }
        if($user_type != 'pharmacy' && $user_type != 'admin'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Order Filter';
		$data['page_condition'] = 'page_order_filter';
        $data['site_title'] = 'Order Filter | ' . $this->data['site_title'];
        
        if($user_type == 'pharmacy'){
            $data['total_order_amount'] = new_order_history::where(['pharmacy_id'=> $user_id])->sum('order_amount');
            $data['delivery_boy'] = new_pharma_logistic_employee::all();
            $data['sellers'] = new_pharma_logistic_employee::where(['pharma_logistic_id'=> $user_id, 'parent_type'=> 'pharmacy', 'user_type'=> 'seller'])->get();
            $data['delivery_boys'] = new_pharma_logistic_employee::where(['pharma_logistic_id'=> $user_id, 'parent_type'=> 'pharmacy', 'user_type'=> 'delivery_boy'])->get();
			$data['total_pending'] = new_order_history::where(['pharmacy_id'=> $user_id, 'is_pharmacy_amount_collect'=> 0])->sum('order_amount');
		} else {
            $data['pharmacies'] = new_pharmacies::all();
            $data['logistics'] = new_logistics::all();
            $data['sellers'] = new_pharma_logistic_employee::all();
            $data['delivery_boys'] = new_pharma_logistic_employee::all();
			$data['total_order_amount'] = new_order_history::sum('order_amount');
			$data['total_pending'] = new_order_history::where(['is_admin_amount_collect'=> 0])->sum('order_amount');
		}
        return view('order_filter.index', $data);
    }
    public function GetPharmacyWiseSeller(){
    	$pharmacy_array = isset($_POST['pharmacy_id']) ? $_POST['pharmacy_id'] : "";
		if($pharmacy_array != ""){
			$pharmacy_array = explode(",", $pharmacy_array);
		}
		$pharmacy_wise_seller_list_html = "";
		$pharmacy_wise_seller_detail = new_pharma_logistic_employee::select('id','name')->where('parent_type','=','pharmacy')->where('user_type','=','seller')->whereIn('pharma_logistic_id', $pharmacy_array)->get();
		foreach ($pharmacy_wise_seller_detail as $pharmacy_wise_seller_detail_key => $pharmacy_wise_seller_detail_value) {
			$pharmacy_wise_seller_detail_value_id = $pharmacy_wise_seller_detail_value->id;
			$pharmacy_wise_seller_detail_value_name = $pharmacy_wise_seller_detail_value->name;
			$pharmacy_wise_seller_list_html .= "<option value=".$pharmacy_wise_seller_detail_value_id.">".$pharmacy_wise_seller_detail_value_name."</option>";
		}
		$pharmacy_wise_delivery_list_html = "";
		$pharmacy_wise_delivery_detail = new_pharma_logistic_employee::select('id','name')->where('parent_type','=','pharmacy')->where('user_type','=','delivery_boy')->whereIn('pharma_logistic_id', $pharmacy_array)->get();
		foreach ($pharmacy_wise_delivery_detail as $pharmacy_wise_delivery_detail_key => $pharmacy_wise_delivery_detail_value) {
			$pharmacy_wise_delivery_detail_value_id = $pharmacy_wise_delivery_detail_value->id;
			$pharmacy_wise_delivery_detail_value_name = $pharmacy_wise_delivery_detail_value->name;
			$pharmacy_wise_delivery_list_html .= "<option value=".$pharmacy_wise_delivery_detail_value_id.">".$pharmacy_wise_delivery_detail_value_name."</option>";
		}
		echo $pharmacy_wise_seller_list_html."##".$pharmacy_wise_delivery_list_html;
    }
    public function GetLogisticWiseSeller(){
    	$logistic_array = isset($_POST['logistic_id']) ? $_POST['logistic_id'] : "";
		if($logistic_array != ""){
			$logistic_array = explode(",", $logistic_array);
		}
		$logistic_wise_seller_list_html = "";
		$logistic_wise_delivery_list_html = "";
		$logistic_wise_delivery_detail = new_pharma_logistic_employee::select('id','name')->where('parent_type','=','logistic')->where('user_type','=','delivery_boy')->whereIn('pharma_logistic_id', $logistic_array)->get();
		foreach ($logistic_wise_delivery_detail as $logistic_wise_delivery_detail_key => $logistic_wise_delivery_detail_value) {
			$logistic_wise_delivery_detail_value_id = $logistic_wise_delivery_detail_value->id;
			$logistic_wise_delivery_detail_value_name = $logistic_wise_delivery_detail_value->name;
			$logistic_wise_delivery_list_html .= "<option value=".$logistic_wise_delivery_detail_value_id.">".$logistic_wise_delivery_detail_value_name."</option>";
		}
		echo $logistic_wise_seller_list_html."##".$logistic_wise_delivery_list_html;
    }
    public function getorderfilter(){

        $user_id = Auth::user()->user_id;
		$user_type = Auth::user()->user_type;
		$html='';
		$pagination='';
		$total_summary='';
		$homepage = (isset($_REQUEST['home']))?$_REQUEST['home']:'';
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$order_history=(isset($_POST['order_history']) && $_POST['order_history']!='')?$_POST['order_history']:'';
        $order_type = (isset($_REQUEST['order_type']))?$_REQUEST['order_type']:'';
        $filter_end_date=(isset($_POST['filter_end_date']) && $_POST['filter_end_date']!='')?$_POST['filter_end_date']:'';
		$filter_start_date=(isset($_POST['filter_start_date']) && $_POST['filter_start_date']!='')?$_POST['filter_start_date']:'';
		if($filter_end_date != ""){
			$filter_end_date_array = explode("/", $filter_end_date);
			if(count($filter_end_date_array) == 3){
				$filter_end_date = $filter_end_date_array[2].'-'.$filter_end_date_array[1].'-'.$filter_end_date_array[0];
			}
		}
		if($filter_start_date != ""){
			$filter_start_date_array = explode("/", $filter_start_date);
			if(count($filter_start_date_array) == 3){
				$filter_start_date = $filter_start_date_array[2].'-'.$filter_start_date_array[1].'-'.$filter_start_date_array[0];
			}
		}
		$order_detail = new_orders::select('order_number','customer_id','pharmacy_id','logistic_user_id','deliveryboy_id','process_user_id','order_status','created_at');

		if($user_type == 'pharmacy'){
			$order_detail = $order_detail->where('new_orders.pharmacy_id', $user_id);
		}
		$pharmacy_array = isset($_POST['pharmacy_id']) ? $_POST['pharmacy_id'] : "";
		if($pharmacy_array != ""){
			$pharmacy_array = explode(",", $pharmacy_array);
		}

		$pharmacy_delivery_array = isset($_POST['pharmacy_delivery_id']) ? $_POST['pharmacy_delivery_id'] : "";
		if($pharmacy_delivery_array != ""){
			$pharmacy_delivery_array = explode(",", $pharmacy_delivery_array);
		}
		$pharmacy_seller_array = isset($_POST['pharmacy_seller_id']) ? $_POST['pharmacy_seller_id'] : "";
		if($pharmacy_seller_array != ""){
			$pharmacy_seller_array = explode(",", $pharmacy_seller_array);
		}
		$logisticd_array = isset($_POST['logistic_id']) ? $_POST['logistic_id'] : "";
		if($logisticd_array != ""){
			$logisticd_array = explode(",", $logisticd_array);
		}
		$logistic_delivery_array = isset($_POST['logistic_delivery_id']) ? $_POST['logistic_delivery_id'] : "";
		if($logistic_delivery_array != ""){
			$logistic_delivery_array = explode(",", $logistic_delivery_array);
		}
		$order_status_array = isset($_POST['order_status']) ? $_POST['order_status'] : "";
		if($order_status_array != ""){
			$order_status_array = explode(",", $order_status_array);
		}
		////////////////////////////////////////////////////////
		if($order_type == "new_order"){
			$order_detail = new_orders::select('order_number','customer_id','pharmacy_id','logistic_user_id','deliveryboy_id','process_user_id','order_status','created_at');
			if($pharmacy_array){
				$order_detail = $order_detail->whereIn('new_orders.pharmacy_id', $pharmacy_array);
			}
			if($pharmacy_delivery_array){
				$order_detail = $order_detail->whereIn('new_orders.deliveryboy_id', $pharmacy_delivery_array);
        	}
        	if($pharmacy_seller_array){
				$order_detail = $order_detail->whereIn('new_orders.process_user_id', $pharmacy_seller_array)->where('process_user_type','=','seller');
	        }
			if($logisticd_array){
				$order_detail = $order_detail->whereIn('new_orders.logistic_user_id', $logisticd_array);
	        }
	        if($logistic_delivery_array){
				$order_detail = $order_detail->whereIn('new_orders.deliveryboy_id', $logistic_delivery_array);
	        }
	        if($order_status_array){
				$order_detail = $order_detail->whereIn('new_orders.order_status', $order_status_array);
	        }
	        if($filter_start_date !== ''){
	        	$order_detail = $order_detail->whereDate('new_orders.created_at','>=',$filter_start_date); 

	        }
	        if($filter_end_date !== ''){
	        	$order_detail = $order_detail->whereDate('new_orders.created_at','<=',$filter_end_date); 

	        }
			$total = $order_detail->count();
			$total_page = ceil($total/$per_page);

			$order_detail = $order_detail->orderby('new_orders.created_at','desc');
			$order_detail = $order_detail->paginate($per_page,'','',$page);
		}elseif($order_type == "order_history"){
			$new_order_history= new_order_history::select('order_number','customer_id','pharmacy_id', 'logistic_user_id','deliveryboy_id','process_user_id','order_status','created_at');
			if($pharmacy_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.pharmacy_id', $pharmacy_array);
			}
			if($pharmacy_delivery_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.deliveryboy_id', $pharmacy_delivery_array);
	        }
	        if($pharmacy_seller_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.process_user_id', $pharmacy_seller_array)->where('process_user_type','=','seller');
	        }
			if($logisticd_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.logistic_user_id', $logisticd_array);
	        }
	        if($logistic_delivery_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.deliveryboy_id', $logistic_delivery_array);
	        }
	        if($order_status_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.order_status', $order_status_array);
	        }
	        if($filter_start_date !== ''){
	        	$new_order_history = $new_order_history->whereDate('new_order_history.created_at','>=',$filter_start_date); 

	        }
	        if($filter_end_date !== ''){
	        	$new_order_history = $new_order_history->whereDate('new_order_history.created_at','<=',$filter_end_date); 

	        }
			$total = $new_order_history->count();
			$total_page = ceil($total/$per_page);

			$new_order_history = $new_order_history->orderby('new_order_history.created_at','desc');
			$order_detail = $new_order_history->paginate($per_page,'','',$page);
		}else{
			$order_detail = new_orders::select('order_number','customer_id','pharmacy_id','logistic_user_id','deliveryboy_id','process_user_id','order_status','created_at');

			$new_order_history= new_order_history::select('order_number','customer_id','pharmacy_id', 'logistic_user_id','deliveryboy_id','process_user_id','order_status','created_at');

			if($pharmacy_array){
				$order_detail = $order_detail->whereIn('new_orders.pharmacy_id', $pharmacy_array);
			}
			
			if($pharmacy_delivery_array){
				$order_detail = $order_detail->whereIn('new_orders.deliveryboy_id', $pharmacy_delivery_array);
        	}
        	if($pharmacy_seller_array){
				$order_detail = $order_detail->whereIn('new_orders.process_user_id', $pharmacy_seller_array)->where('process_user_type','=','seller');
			}
		
			if($logisticd_array){
				$order_detail = $order_detail->whereIn('new_orders.logistic_user_id', $logisticd_array);
	        }
	        if($logistic_delivery_array){
				$order_detail = $order_detail->whereIn('new_orders.deliveryboy_id', $logistic_delivery_array);
	        }
	        if($order_status_array){
				$order_detail = $order_detail->whereIn('new_orders.order_status', $order_status_array);
	        }
	        if($filter_start_date !== ''){
	        	$order_detail = $order_detail->whereDate('new_orders.created_at','>=',$filter_start_date); 

	        }
	        if($filter_end_date !== ''){
	        	$order_detail = $order_detail->whereDate('new_orders.created_at','<=',$filter_end_date); 

	        }
	        
			//////////////// 
			if($pharmacy_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.pharmacy_id', $pharmacy_array);
			}
			if($pharmacy_delivery_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.deliveryboy_id', $pharmacy_delivery_array);
	        }
	        if($pharmacy_seller_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.process_user_id', $pharmacy_seller_array)->where('process_user_type','=','seller');
	        }
			if($logisticd_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.logistic_user_id', $logisticd_array);
	        }
	        if($logistic_delivery_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.deliveryboy_id', $logistic_delivery_array);
	        }
	        if($order_status_array){
				$new_order_history = $new_order_history->whereIn('new_order_history.order_status', $order_status_array);
			}
			if($filter_start_date !== ''){
	        	$new_order_history = $new_order_history->whereDate('new_order_history.created_at','>=',$filter_start_date); 

	        }
	        if($filter_end_date !== ''){
	        	$new_order_history = $new_order_history->whereDate('new_order_history.created_at','<=',$filter_end_date); 

	        }
	        $total_result = $order_detail->union($new_order_history)->count();
			$total = $total_result;
			$total_page = ceil($total/$per_page);

			$order_detail = $order_detail->union($new_order_history)->orderby('created_at', 'DESC')->paginate($per_page,'','',$page);
		}
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';

				$pharmacy_detail = new_pharmacies::select('name')->where('id','=',$order->pharmacy_id)->first();
				$pharmacy_detail_name = "";
				if($pharmacy_detail){
					$pharmacy_detail_name = $pharmacy_detail->name;
				}
				$customer_detail = new_users::select('name')->where('id',$order->customer_id)->first();
				$customer_detail_name = "";
				if($customer_detail){
					$customer_detail_name = $customer_detail->name;
				}
				$deliveryboy_details = new_pharma_logistic_employee::select('name')->where('id','=',$order->deliveryboy_id)->first();
				$deliveryboy_name = "";
				if($deliveryboy_details){
					$deliveryboy_name = $deliveryboy_details->name;
				}
				$logistics_details = new_logistics::select('name')->where('id','=',$order->logistic_user_id)->first();
				$logistic_name = "";
				if($logistics_details){
					$logistic_name = $logistics_details->name;
				}

				$html.='<tr><td><a href="'.url('/orders/order_details/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				if($order->is_external_delivery){
					$html.=' <i class="ti-truck" style="color: orange;"></i> ';
				}
				$html.='</td>
				<td>'.$customer_detail_name.'</td>
				<td>'.$pharmacy_detail_name.'</td>
				<td>'.$deliveryboy_name.'</td>
				<td>'.$logistic_name.'</td>
				<td>'.$order->created_at.'</td>';
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
						<a class="page-link" onclick="getorderfilter('.($page-1).')" href="javascript:;" tabindex="-1"><i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getorderfilter('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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