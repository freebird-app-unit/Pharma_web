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

use App\new_pharma_logistic_employee;
use App\new_logistics;
use App\new_orders;
use App\new_order_history;
use App\new_users;
use App\new_pharmacies;

class AdmincompleteController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		$user_id = Auth::user()->id;
		$data = array();
		$data['page_title'] = 'Completed orders';
		$data['page_condition'] = 'page_admincomplete';
		$data['site_title'] = 'Completed orders | ' . $this->data['site_title'];
		$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'pharmacy')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->get();
        return view('admincomplete.index', $data);
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
		
		$order_detail = new_order_history::select('new_order_history.id','new_order_history.neighbour_info','pharmacy_id','deliver_datetime','deliveryboy_id','order_number','new_users.name as customer_name','new_users.mobile_number as customer_number','address_new.address as address','new_delivery_charges.delivery_type as delivery_type', 'prescription.name as prescription_name', 'prescription.image as prescription_image','is_external_delivery')
		->leftJoin('new_users', 'new_users.id', '=', 'new_order_history.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_order_history.address_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')
		->leftJoin('prescription', 'prescription.id', '=', 'new_order_history.prescription_id')
		->where('new_order_history.order_status','complete');
		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('new_users.name', 'like', $searchtxt.'%')
						->orWhere('new_users.mobile_number', 'like', $searchtxt.'%')
						->orWhere('new_order_history.order_number', 'like', $searchtxt.'%');
            });
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);

		$order_detail = $order_detail->orderby('new_order_history.id','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				
				$created_at = ($order->deliver_datetime!='')?date('d-M-Y h:i a',strtotime($order->deliver_datetime)):'';
				//$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				$assign_to = get_name('new_pharma_logistic_employee','name',$order->deliveryboy_id);
				$pharmacy_name = get_name('new_pharmacies','name',$order->pharmacy_id);
				$time = get_order_delivered_time($order->id,$order->deliveryboy_id);
				
				$order_feedback = Orderfeedback::where('order_id',$order->id)->avg('rating');

				/*<img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span><td>'.$order->prescription_name.'</td>
					<td>'.$order->order_note.'</td>*/
				if($order->is_external_delivery == 1){
					$order_type = 'Paid';
				}else{
					$order_type = 'Free';
				}
				$html.='<tr>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->neighbour_info.'</td>
					<td>'.$order->customer_number.'</td>
					<td>'.$order->address.'</td>
					<td>'.$pharmacy_name.'</td>
					<td class="text-warning">'.$assign_to.'</td>
					<td>'.$order_feedback.'</td>
					<td>'.$order_type.'</td>
					<td><span class="label label-warning">'.$created_at.'</span></td>';
					
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
						<a class="page-link" onclick="getcompletelist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getcompletelist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
