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

class VoucherController extends Controller
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

		/*if($user_type != 'pharmacy' && $user_type != 'admin' && $user_type != 'logistic'){
			return redirect(route('home'));
		}
*/
		$data = array();
		$data['page_title'] = 'Voucher';
		$data['page_condition'] = 'page_voucher';
		$data['site_title'] = 'Voucher | ' . $this->data['site_title'];

        return view('transaction_report.voucher', $data);
	}
	
	public function history()
    {
        $user_id = Auth::user()->user_id;
        $user_type = Auth::user()->user_type;

		/*if($user_type != 'pharmacy' && $user_type != 'admin' && $user_type != 'logistic'){
			return redirect(route('home'));
		}*/

		$data = array();
		$data['page_title'] = 'Voucher History';
		$data['page_condition'] = 'page_voucher_history';
		$data['site_title'] = 'Voucher History | ' . $this->data['site_title'];

        return view('transaction_report.voucher_history', $data);
	}
    
    public function getvoucherlist()
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
		$search_text=(isset($_POST['search_text']) && $_POST['search_text']!='')?$_POST['search_text']:'';

        $filter_end_date=(isset($_POST['filter_end_date']) && $_POST['filter_end_date']!='')?$_POST['filter_end_date']:'';
        $filter_start_date=(isset($_POST['filter_start_date']) && $_POST['filter_start_date']!='')?$_POST['filter_start_date']:'';

        $order_detail = vouchers::select('vouchers.*','new_pharmacies.name as pharmacy_name', 'new_logistics.name as logistic_name')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'vouchers.payer_id')
		->leftJoin('new_logistics', 'new_logistics.id', '=', 'vouchers.payer_id');
		//->orWhere(['receiver_type'=> $user_type])
		//->orWhere(['receiver_id'=> $user_id]);
		
		$order_detail= $order_detail->where(function ($query) use($user_type,$user_id) {
                $query->where('vouchers.payer_type',$user_type)
				->orWhere('vouchers.payer_id',$user_id); 
            });
		
		if($search_text!=''){
			$order_detail= $order_detail->where(function ($query) use($search_text) {
                $query->where('vouchers.voucher_number', 'like', '%'.$search_text.'%')
				->orWhere('vouchers.transation_number', 'like', '%'.$search_text.'%')
				->orWhere('new_pharmacies.name', 'like', '%'.$search_text.'%')
				->orWhere('vouchers.amount', 'like', '%'.$search_text.'%')
				->orWhere('vouchers.voucher_info', 'like', '%'.$search_text.'%');
            });
		}
		
        $total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('vouchers.updated_at','desc');
        $order_detail = $order_detail->paginate($per_page,'','',$page);

        //get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';
				
				$html.='<tr><td><a href="'.url('/voucher/detail/'.$order->id).'"</a><span>'.$order->voucher_number.'</span></td>';
				$html.='<td>'.$order->transation_number.'</td><td>';
				if($order->payer_type == 'pharmacy'){
                    $payer_name = $order->pharmacy_name;
					$html.=' <i class="ti-home" style="color: orange;"></i> ';
                }
				if($order->payer_type == 'logistic'){
                    $payer_name = $order->logistic_name;
					$html.=' <i class="ti-truck" style="color: orange;"></i> ';
				}
				if($order->payer_type == 'admin'){
                    $payer_name = 'Admin';
					$html.=' <i class="ti-truck" style="color: orange;"></i> ';
                }
				$html.= $payer_name.'</td>';
				$html.='<td>'.$order->amount.'</td>
                <td>'.$order->voucher_info.'</td>
                <td>'.$created_at.'</td>';
				if($order->receiver_type == $user_type && $order->voucher_status == 'new'){
                    $html.='<td><a onclick="voucher_confirmed('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Confirmed voucher">Confirmed</a></td>';
                } else if ($order->voucher_status == 'accept'){
                    $html.='<td><span class="label label-success"> Confirmed </span></td>';
                } else {
                    $html.='<td><span class="label label-warning"> Pending </span></td>';
                }
				$html.='</tr>';
			}
			if($page==1){
				$prev='disabled';
				$pagination.='<li class="page-item '.$prev.'">
						<a class="page-link" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>';
			}else{
				$prev='';
				$pagination.='<li class="page-item '.$prev.'">
						<a class="page-link" onclick="getvoucherlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>';
			}
			if($total_page==$page){
				$next='disabled';
				$pagination.='<li class="page-item '.$next.'">
						<a class="page-link" href="javascript:;"><i class="fa fa-angle-right"></i></a>
					</li>';
			}else{
				$next='';
				$pagination.='<li class="page-item '.$next.'">
						<a class="page-link" onclick="getvoucherlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
					</li>';
			}
			/* $pagination.='<li class="page-item '.$prev.'">
						<a class="page-link" onclick="getvoucherlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getvoucherlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
					</li>'; */
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
	
	public function getvoucherhistorylist()
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
		
		$search_text=(isset($_POST['search_text']) && $_POST['search_text']!='')?$_POST['search_text']:'';

        $order_detail = vouchers::select('vouchers.*','new_pharmacies.name as pharmacy_name', 'new_logistics.name as logistic_name')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'vouchers.payer_id')
		->leftJoin('new_logistics', 'new_logistics.id', '=', 'vouchers.payer_id');
		//->where(['payer_type'=> $user_type, 'payer_id'=> $user_id]);
		
		$order_detail= $order_detail->where(function ($query) use($user_type,$user_id) {
            $query->where('vouchers.payer_type',$user_type)
			->orWhere('vouchers.payer_id',$user_id);
        });
			
		if($search_text!=''){
			$order_detail= $order_detail->where(function ($query) use($search_text) {
                $query->where('vouchers.voucher_number', 'like', '%'.$search_text.'%')
				->orWhere('vouchers.transation_number', 'like', '%'.$search_text.'%')
				->orWhere('new_pharmacies.name', 'like', '%'.$search_text.'%')
				->orWhere('vouchers.amount', 'like', '%'.$search_text.'%')
				->orWhere('vouchers.voucher_info', 'like', '%'.$search_text.'%');
            });
		}
		
        $total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('vouchers.updated_at','desc');
        $order_detail = $order_detail->paginate($per_page,'','',$page);

		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';
				
				$html.='<tr><td><a href="'.url('/voucher/detail/'.$order->id).'"</a><span>'.$order->voucher_number.'</span></td>';
				$html.='<td>'.$order->transation_number.'</td><td>';
				if($order->payer_type == 'pharmacy'){
                    $payer_name = $order->pharmacy_name;
					$html.=' <i class="ti-home" style="color: orange;"></i> ';
                }
				if($order->payer_type == 'logistic'){
                    $payer_name = $order->logistic_name;
					$html.=' <i class="ti-truck" style="color: orange;"></i> ';
				}
				if($order->payer_type == 'admin'){
                    $payer_name = 'Admin';
					$html.=' <i class="ti-truck" style="color: orange;"></i> ';
                }
				$html.= $payer_name.'</td>';
				$html.='<td>'.$order->amount.'</td>
                <td>'.$order->voucher_info.'</td>
                <td>'.$created_at.'</td>';
				if($order->receiver_type == $user_type && $order->voucher_status == 'new'){
                    $html.='<td><a onclick="voucher_confirmed('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Confirmed voucher">Confirmed</a></td>';
				} else if ($order->voucher_status == 'accept'){
                    $html.='<td><span class="label label-success"> Confirmed </span></td>';
                } else {
                    $html.='<td><span class="label label-warning"> Pending </span></td>';
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
				<a class="page-link" onclick="getvoucherlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
			</li>
			<li class="page-item '.$next.'">
				<a class="page-link" onclick="getvoucherlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
    
    public function voucher_detail(Request $request)
    {
        $user_id = Auth::user()->user_id;
        $user_type = Auth::user()->user_type;

		/*if($user_type != 'pharmacy' && $user_type != 'admin' && $user_type != 'logistic'){
			return redirect(route('home'));
		}
*/
        $data = array();
		$data['id'] = $request->id;
		$data['page_title'] = 'Voucher Detail';
		$data['page_condition'] = 'page_voucher_detail';
		$data['site_title'] = 'Voucher Detail | ' . $this->data['site_title'];

        return view('transaction_report.voucher_detail', $data);
	}

    public function get_voucher_orderlist(Request $request)
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

        $voucher_detail = vouchers::select('orderIds')->where('id', $request->voucher_id)->first();
        $orderid_array = explode(',', $voucher_detail->orderIds);

		$order_detail = new_order_history::select('new_order_history.*','new_users.name as customer_name', 'new_pharmacies.name as pharmacy_name', 'new_delivery_charges.delivery_price as delivery_price')
		->leftJoin('new_users', 'new_users.id', '=', 'new_order_history.customer_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_order_history.pharmacy_id')
		->whereIn('new_order_history.id', $orderid_array); 

        $total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_order_history.updated_at','desc');
        $order_detail = $order_detail->paginate($per_page,'','',$page);

        //get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';
				
				$html.='<tr><td><a href="'.url('/orders/order_details/'.$order->id).'"</a><span>'.$order->order_number.'</span></td>';
				$html.='<td>'.$order->customer_name.'</td>
                <td>'.$order->pharmacy_name.'</td>
                <td>'.$order->order_amount.'</td>
                <td>'.$order->delivery_price.'</td>
                <td>'.$created_at.'</td>';
				if($order->order_status == 'complete'){
                    $html.='<td><span class="label label-success"> complete </span></td>';
                } else {
                    $html.='<td><span class="label label-warning"> '.$order->order_status.' </span></td>';
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
						<a class="page-link" onclick="getvoucherorderlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getvoucherorderlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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

    public function voucher_confirmed()
    {
		$voucher = vouchers::where('id', $_REQUEST['voucher_id'])->first();
		$orderid_array = explode(',', $voucher->orderIds);
		if($voucher->payer_type == 'pharmacy'){
			new_order_history::whereIn('id', $orderid_array)->update(array('is_admin_delivery_charge_collect' => 2));
		} else if($voucher->payer_type == 'logistic'){
			new_order_history::whereIn('id', $orderid_array)->update(array('is_admin_amount_collect' => 2));
		}
		$voucher->voucher_status = 'accept';
        $voucher->save();
		return redirect(route('voucher.index'))->with('success_message', trans('Voucher Successfully Confirmed.'));
	}
}