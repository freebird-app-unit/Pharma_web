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

class LogisticincompleteController extends Controller
{
    public function logistic_index()
    {
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		Auth::user()->user_type='logistic';
		$user_id = Auth::user()->user_id;
		$data = array();
		$data['page_title'] = 'Incomplete order';
		$data['page_condition'] = 'page_incomplete_logistic';
		$data['site_title'] = 'Incomplete order | ' . $this->data['site_title'];
		$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'logistic')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->where('is_active', 1)->get();
		return view('logistic.incomplete.index', $data);
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

		//get list
		$order_detail = DB::table('new_orders')->select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','new_pharmacies.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->where('new_orders.order_status','incomplete');

		if($user_type=='pharmacy'){
			$order_detail = $order_detail->where('pharmacy_id',$user_id);
		}else if($user_type=='seller'){
			$parentuser_id = Auth::user()->parentuser_id;
			$order_detail = $order_detail->where('pharmacy_id',$parentuser_id);
			$order_detail = $order_detail->where('process_user_id',$user_id);
		}else if($user_type=='logistic'){
			$order_detail = $order_detail->where('new_orders.logistic_user_id',$user_id);
		}

		if($searchtxt!=''){
			$order_detail= $order_detail->where(function ($query) use($searchtxt) {
                $query->Where('new_delivery_charges.delivery_type', 'like', '%'.$searchtxt.'%')
						->orWhere('new_orders.order_number', 'like', '%'.$searchtxt.'%');
            });
		}

		$total = $order_detail->count();
		$total_page = ceil($total/$per_page);
		
		$order_detail = $order_detail->orderby('new_orders.return_datetime','desc');
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
					<td style="text-align:center;"><a href="'.url('/logistic/incomplete/order_details/'.$order->id).'"><img src="'.$image_url.'" width="40"/><span>'.$order->order_number.'</span></a></td>
					<td style="text-align:center;">'.$order->delivery_type.'</td>
					<td style="text-align:center;">'.$order->pharmacyaddress.'</td>
					<td style="text-align:center;">'.$order->address.'</td>
					<td style="text-align:center;">'.$order->order_amount.'</td>
					<td style="text-align:center;">'.$order->deliveryboyname.'</td>
					<td style="text-align:center;">'.$order->reject_datetime.'</td>';

					if($order->is_external_delivery > 0){
						$html.='<td style="text-align:center;"><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Reassign order" data-toggle="modal" data-target="#assign_modal">Re Delivery</a>';
						$html.='<a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Cancel</a>';
						$html.='</td>';
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
						<a class="page-link" onclick="getincompletelistlogistic('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getincompletelistlogistic('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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

	public function logistic_order_details($id)
    {
		$user_id = Auth::user()->user_id;
		$order = new_orders::select('new_orders.*')->where('new_orders.id', $id)->first();
		$order_detail = DB::table('new_orders')->select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','new_pharmacies.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname','new_pharmacies.name as pharmacyname','new_pharmacies.mobile_number as pharmacymobile_number','new_pharmacies.address as pharmacyaddress','prescription.image as preimage','prescription.name as prename')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')
		->where('new_orders.order_status','incomplete')
		->where('new_orders.id',$id)->first();
		$customer = new_users::where('id',$order->customer_id)->first();

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
		$data = array();
		$data['order'] = $order;
		$data['customer'] = $customer;
		$data['order_detail'] = $order_detail;
		$data['address'] = $address;
		$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'pharmacy')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->where('is_active', 1)->get();
		$data['page_title'] = 'Order Detail';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'order detail | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::get();
        return view('logistic.incomplete.order_details', $data);
		
	}
	public function logistic_assign(Request $request)
    {
		//echo $request->reject_reason.'--'.$request->reject_id;exit;
		$user_id = Auth::user()->user_id;
		$order = new_orders::find($request->assign_id);
		$order->deliveryboy_id = $request->delivery_boy;
		$order->order_status = 'assign';
		$order->assign_datetime = date('Y-m-d H:i:s');
		$order->save();
		
		$assign = new Orderassign();
		$assign->order_id = $request->assign_id;
		$assign->logistic_id = $user_id;
		$assign->order_status = 'assign';
		$assign->deliveryboy_id = $request->delivery_boy;
		$assign->assign_date = date('Y-m-d H:i:s');
		$assign->created_at = date('Y-m-d H:i:s');
		$assign->updated_at = date('Y-m-d H:i:s');
		$assign->save();

		return redirect(route('logistic.incomplete.index'))->with('success_message', trans('Order Successfully assign'));
	}

	public function logistic_reject(Request $request)
    {
		//echo $request->reject_reason.'--'.$request->reject_id;exit;
		$user_id = Auth::user()->id;

		$order = new_orders::find($request->reject_id);
		$order->logistic_user_id = null;
		$order->deliveryboy_id = 0;
		$order->logistic_reject_reason = $request->reject_reason;
		$order->assign_datetime = null;
		$order->order_status = 'accept';
		$order->save();

		$orderAssignCount = Orderassign::whereNull('deliveryboy_id')->Where('order_id', $request->reject_id)->count();
		if($orderAssignCount > 0){
			$orderAssign = Orderassign::Where('order_id', $request->reject_id)->whereNull('deliveryboy_id')->first();
			$orderAssign->order_status = 'reject';
			// $orderAssign->rejectreason_id = $request->reject_reason;
			$orderAssign->reject_date = date('Y-m-d H:i:s');
			$orderAssign->updated_at = date('Y-m-d H:i:s');
			$orderAssign->save();
		}

		return redirect(route('logistic.incomplete.index'))->with('success_message', trans('Order Successfully reject'));
	}
}
