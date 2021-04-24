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
use App\Orderfeedback;
use App\new_address;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use File;
use Image;
use Storage;
use App\SellerModel\invoice;
use App\new_pharma_logistic_employee;
use App\new_logistics;
use App\new_orders;
use App\new_order_history;
use App\new_users;
use App\new_pharmacies;


class LogisticrejectController extends Controller
{
    public function logistic_index()
    {
		Auth::user()->user_type='logistic';
		$user_id = Auth::user()->user_id;
		$data = array();
		$data['page_title'] = 'Rejected orders';
		$data['page_condition'] = 'page_reject_logistic';
		$data['site_title'] = 'Rejected orders | ' . $this->data['site_title'];
        return view('logistic.reject.index', $data);
    }

 public function logistic_getlist()
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

		$order_detail = new_orders::select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','new_pharmacies.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->where('new_orders.order_status','reject');

		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
		}else if($user_type=='logistic'){
			$order_detail = $order_detail->where('logistic_user_id',$user_id);
		}

		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->Where('new_delivery_charges.delivery_type', 'like', '%'.$searchtxt.'%')
						->orWhere('new_orders.order_number', 'like', '%'.$searchtxt.'%');
            });
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_orders.id','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$invoice = invoice::where('order_id',$order->order_id)->first();
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
					<td><a href="'.url('/logistic/complete/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->delivery_type.'</td>
					<td>'.$order->pharmacyaddress.'</td>
					<td>'.$order->address.'</td>
					<td>'.$order->order_amount.'</td>
					<td>'.$order->deliveryboyname.'</td>
					<td class="text-danger">'.$order->reject_cancel_reason.'</td>
					<td>'.$order->reject_datetime.'</td>';
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
						<a class="page-link" onclick="getcompletelistlogistic('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getcompletelistlogistic('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
