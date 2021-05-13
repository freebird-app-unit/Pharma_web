<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;

use App\new_orders;
use App\new_order_history;
use App\new_pharma_logistic_employee;
use App\vouchers;
use App\new_pharmacies;
use App\new_logistics;

class DeliveryChargesController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
	}
	
    public function index()
    {
        $user_id = Auth::user()->user_id;
        $user_type = Auth::user()->user_type;

		/*if($user_type != 'admin'){
			return redirect(route('home'));
		}*/

		$data = array();
		$data['user_type'] = $user_type;

		$data['page_title'] = 'Delivery Charges';
		$data['page_condition'] = 'page_delivery_charges';
		$data['site_title'] = 'Delivery Charges | ' . $this->data['site_title'];

		$data['logistics'] = new_logistics::all();
		$data['total_order_amount'] = new_order_history::where(['is_logistic_charge_collect'=>'0','is_external_delivery'=>'1'])->sum('order_amount');
		$data['total_order'] = new_order_history::where(['is_logistic_charge_collect'=>'0','is_external_delivery'=>'1'])->count();
		
		$data['total_order_amount'] = 0;
		$data['total_order'] = 0;
		
        return view('deliverycharges.index', $data);
	}
    public function getlogisticpendingamount($id){
		$total_order_amount = new_order_history::leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')->where(['new_order_history.is_logistic_charge_collect'=>0,'new_order_history.is_external_delivery'=>1,'new_order_history.order_status'=>'complete','logistic_user_id'=>$id])->sum('new_delivery_charges.delivery_price');
		$total_order = new_order_history::where(['new_order_history.is_logistic_charge_collect'=>0,'new_order_history.is_external_delivery'=>1,'new_order_history.order_status'=>'complete','logistic_user_id'=>$id])->count();
		echo $total_order.'##'.$total_order_amount;exit;
		
	}
    public function getdeliverychargesorderlist()
    {
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
        $order_type=(isset($_POST['order_type']) && $_POST['order_type']!='')?$_POST['order_type']:'';

        $filter_end_date=(isset($_POST['filter_end_date']) && $_POST['filter_end_date']!='')?$_POST['filter_end_date']:'';
        $filter_start_date=(isset($_POST['filter_start_date']) && $_POST['filter_start_date']!='')?$_POST['filter_start_date']:'';

        $order_detail = new_order_history::select('new_order_history.*','new_users.name as customer_name', 'new_logistics.name as seller_name', 'new_delivery_charges.delivery_price as delivery_price')
		->leftJoin('new_users', 'new_users.id', '=', 'new_order_history.customer_id')
        ->leftJoin('new_logistics', 'new_logistics.id', '=', 'new_order_history.logistic_user_id')
        ->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')
		->Where(['new_order_history.is_logistic_charge_collect'=> 0])
		->where('new_order_history.order_status','complete');

		//if(isset($_POST['logistic_id']) && $_POST['logistic_id'] != ''){
			$order_detail = $order_detail->where('new_order_history.is_external_delivery', 1);
			$order_detail = $order_detail->where('new_order_history.logistic_user_id', $_POST['logistic_id']);
			$order_detail = $order_detail->where('new_order_history.logistic_user_id', '!=' , '');
		//}

        if($filter_end_date != '' && $filter_start_date !== ''){
            $start_date = date('Y-m-d',strtotime(str_replace('/','-',$filter_start_date)));
            $end_date = date('Y-m-d',strtotime(str_replace('/','-',$filter_end_date)));

    		$order_detail = $order_detail->whereBetween(DB::raw('DATE(new_order_history.created_at)'), array($start_date, $end_date));
        }

        $total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_order_history.created_at','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
        //get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';
				
				$html.='<tr><td><a href="'.url('/orders/order_details/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				if($order->is_external_delivery){
					$html.=' <i class="ti-truck" style="color: orange;"></i> ';
				}
				$html.='</td><td>'.$order->customer_name.'</td>
				<td>'.$order->seller_name.'</td>
				<td>'.$order->order_amount.'</td>
				<td>'.$order->delivery_price.'</td>
				<td>'.$created_at.'</td>';
				$html.= '<td><span class="label label-warning"> Payment pending</span></td>'; 
				$html.= '<td><input class="selected_order" type="checkbox" name="orderIds[]" value="'.$order->id.'" id="'.$order->id.'"></td>';
				

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
						<a class="page-link" onclick="getreportorderlist('.($page-1).')" href="javascript:;" tabindex="-1"><i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getreportorderlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
	
	public function deliverycharges_payment_create(Request $request)
    {
        $user_id = Auth::user()->user_id;
        $user_type = Auth::user()->user_type;

		$orderIds = $request->orderIds;
		$array = explode(',', $request->orderIds);
		$amount = new_order_history::leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')->whereIn('new_order_history.id', $array)->sum('new_delivery_charges.delivery_price');
		

		if($orderIds != ''){
			$vouchers = new vouchers();
			$vouchers->voucher_type = $request->voucher_type;
			$vouchers->voucher_status = 'accept';
			$vouchers->voucher_number = $this->generate_unique_number();
			$vouchers->amount = $amount;
			$vouchers->payer_type = $user_type;
			$vouchers->payer_id = $user_id;
			$vouchers->receiver_type = 'logistic';
			$vouchers->receiver_id = $request->logistic_id;
			$vouchers->voucher_info = $request->voucher_info;
			$vouchers->orderIds = $orderIds;
			$vouchers->transation_number = $request->transation_number;
			$vouchers->created_at = date('Y-m-d H:i:s');
			if($vouchers->save()){
				new_order_history::whereIn('id', $array)->update(array('is_logistic_charge_collect' => 1));
			}
		}
	}

	public function logistic_payment_create(Request $request)
    {
        $user_id = Auth::user()->user_id;
        $user_type = Auth::user()->user_type;
		$orderIds = '';
		if($request->orderIds == ''){
			$order_array = new_order_history::leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')->where('logistic_user_id', $user_id)->select('new_order_history.order_id')->get();
			$amount = new_order_history::leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')->where('logistic_user_id', $user_id)->sum('new_delivery_charges.delivery_price');
			foreach($order_array as $key=>$val){
				$orderIds .= $val->order_id;
				if(count($order_array) > ($key+1)){
					$orderIds .= ',';
				}
			}
		} else {
			$orderIds = $request->orderIds;
			$array = explode(',', $request->orderIds);
			$amount = new_order_history::leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')->whereIn('new_order_history.id', $array)->sum('new_delivery_charges.delivery_price');
		}

		$vouchers = new vouchers();
		$vouchers->voucher_type = $request->voucher_type;
		$vouchers->voucher_status = 'new';
		$vouchers->voucher_number = $this->generate_unique_number();
		$vouchers->amount = $amount;
		$vouchers->payer_type = $user_type;
		$vouchers->payer_id = $user_id;
		$vouchers->receiver_type = 'admin';
		$vouchers->receiver_id = 1;
		$vouchers->voucher_info = $request->voucher_info;
		$vouchers->orderIds = $orderIds;
		$vouchers->transation_number = $request->transation_number;
		$vouchers->created_at = date('Y-m-d H:i:s');
		if($vouchers->save()){
			if($request->orderIds == ''){
				new_order_history::where(['logistic_user_id'=> $user_id])->update(array('is_admin_amount_collect' => 1));
			} else {
				new_order_history::whereIn('id', $array)->update(array('is_admin_amount_collect' => 1));
			}
		}
	}

	public function generate_unique_number()
	{
		$encryption = new \MrShan0\CryptoLib\CryptoLib();
		$secretyKey = env('ENC_KEY');
		
		$string = 'PAY_'.rand(1111111, 99999999); 
		$order = vouchers::where('voucher_number','=',$string)->first();
		if($order){
			$string = $this->generate_unique_number(); 
			return $string; 
		}else{
			return $string; 
		}
	}
}