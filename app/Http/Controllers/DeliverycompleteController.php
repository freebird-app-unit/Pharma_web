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
class DeliverycompleteController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='delivery_boy'){
			return redirect(route('home'));
		}
		$user_id = Auth::user()->id;
		$data = array();
		$data['page_title'] = 'Completed orders';
		$data['page_condition'] = 'page_deliverycomplete';
		$data['site_title'] = 'Completed orders | ' . $this->data['site_title'];
		$data['deliveryboy_list'] = User::where('parentuser_id',$user_id)->where('user_type','delivery_boy')->get();
        return view('deliverycomplete.index', $data);
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
		->where('order_status','complete');
		
		$total_res = $total_res->where('deliveryboy_id',$user_id);
		
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
		$order_detail = DB::table('orders')->select('orders.*','users.name as customer_name','users.mobile_number as customer_number')
		->leftJoin('users', 'users.id', '=', 'orders.customer_id')
		->where('order_status','complete');
		
		$order_detail = $order_detail->where('deliveryboy_id',$user_id);
		
		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->where('users.name', 'like', $searchtxt.'%')
						->orWhere('users.mobile_number', 'like', $searchtxt.'%');
            });
		}
		$order_detail = $order_detail->orderby('orders.id','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y',strtotime($order->created_at)):'';
				$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = '';
				if($order->prescription!=''){
					$destinationPath = base_path() . '/uploads/prescription/'.$order->prescription;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/uploads/prescription/'.$order->prescription;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				$address = '';
				if(get_name('address','address',$order->address_id)!=''){
					$address.= get_name('address','address',$order->address_id).', ';
				}
				if(get_name('address','address2',$order->address_id)!=''){
					$address.= get_name('address','address2',$order->address_id).', ';
				}
				if(get_name('address','city',$order->address_id)!=''){
					$address.= get_name('address','city',$order->address_id).', ';
				}
				if(get_name('address','state',$order->address_id)!=''){
					$address.= get_name('address','state',$order->address_id).', ';
				}
				if(get_name('address','country',$order->address_id)!=''){
					$address.= get_name('address','country',$order->address_id).', ';
				}
				if(get_name('address','pincode',$order->address_id)!=''){
					$address.= get_name('address','pincode',$order->address_id).', ';
				}
				$address = rtrim($address,', ');
				$time = get_order_delivered_time($order->id,$order->deliveryboy_id);
				$html.='<tr>
					<td><a href="'.url('/deliverycomplete/invoice').'/'.$order->id.'"><img src="'.$image_url.'" width="50"/></a><span>'.$order->order_number.'</span></td>
					<td>'.str_replace('_',' ',$order->order_type).'</td>
					<td>'.$order->order_note.'</td>
					<td>'.$order->customer_name.'</td>
					<td>'.$order->customer_number.'</td>
					<td class="text-warning">'.$address.'</td>
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
	public function invoice($id)
    {
		$user_id = Auth::user()->id;
		$order = Orders::find($id);
		$customer = User::where('id',$order->customer_id)->first();
		$address = '';
		if(get_name('address','address',$order->address_id)!=''){
			$address.= get_name('address','address',$order->address_id).', ';
		}
		if(get_name('address','address2',$order->address_id)!=''){
			$address.= get_name('address','address2',$order->address_id).', ';
		}
		if(get_name('address','city',$order->address_id)!=''){
			$address.= get_name('address','city',$order->address_id).', ';
		}
		if(get_name('address','state',$order->address_id)!=''){
			$address.= get_name('address','state',$order->address_id).', ';
		}
		if(get_name('address','country',$order->address_id)!=''){
			$address.= get_name('address','country',$order->address_id).', ';
		}
		if(get_name('address','pincode',$order->address_id)!=''){
			$address.= get_name('address','pincode',$order->address_id).', ';
		}
		$address = rtrim($address,', ');
		
		$assign_by = get_name('users','name',$order->process_user_id);
		$assign_time = get_order_time($order->id,$order->deliveryboy_id);
		$delivered_time = get_order_delivered_time($order->id,$order->deliveryboy_id);
		
		$data = array();
		$data['order'] = $order;
		$data['customer'] = $customer;
		$data['assign_by'] = $assign_by;
		$data['assign_time'] = $assign_time;
		$data['delivered_time'] = $delivered_time;
		$data['address'] = $address;
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
        return view('deliverycomplete.invoice', $data);
		
	}
}
