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
use App\new_order_history;
use App\new_pharma_logistic_employee;
use App\new_logistics;
use App\new_orders;
use App\new_users;
use App\new_pharmacies;
use App\new_delivery_charges;
use Helper;

class IncompleteController extends Controller
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
		$data['page_title'] = 'Incomplete order';
		$data['page_condition'] = 'page_incomplete';
		$data['site_title'] = 'Incomplete order | ' . $this->data['site_title'];
		
		if(Auth::user()->user_type=='seller'){
			$data['deliveryboy_list'] = User::where('parentuser_id', $user_id)->where('user_type', 'delivery_boy')->get();
		}else if(Auth::user()->user_type=='pharmacy'){
			$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'pharmacy')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->get();
			$data['reject_reason'] = Rejectreason::get();
		}

		$logistics = new_logistics::where('city', '=', Auth::user()->city)->get();
		$pharmacy = new_pharmacies::select('lat', 'lon')->where('id', Auth::user()->user_id)->first();

		$content[0] = $pharmacy->lat;
		$content[1] = $pharmacy->lon;
		$logistic_list = $this->getLogisticList($logistics, $content);
		$data['delivery_charges'] = json_encode(array());

		if(count($logistic_list)){
			$logistic_ids = array();
			
			foreach ($logistic_list as $key => $value) { 
				array_push($logistic_ids, $value->id);
			}

			$data['delivery_charges'] = new_delivery_charges::whereIn('logistic_id', $logistic_ids)->get();
		}

		$data['logistic_list'] = json_encode($logistic_list);
        return view('incomplete.index', $data);
	}
	public function getLogisticList($logistics, $content)
	{
		$coordinates = array();
		$logisticChecked = array();
		$coordinates[0] = $content[0];
		$coordinates[1] = $content[1];

		foreach ($logistics as $key => $value) { 
			$logistic = $value;
			$geo_fencings = \DB::table('geo_fencings')->select('*')->where('user_id', '=', $value->id)->get();

			$result = 'false';
			if(count($geo_fencings)>0){
				foreach ($geo_fencings as $key => $value) { 
					switch ($value->type) {
						case 'circle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRound($coords, $value->radius, $coordinates);
							break;
						
						case 'polygon':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinPolygon($coords, $coordinates);
							break;
	
						case 'rectangle':
							$coordsSet = str_replace(array( '(', ')' ), '', $value->coordinates);
							$coords = explode(",", $coordsSet);
							$result = $this->checkWithinRectangle($coords, $coordinates);
							break;
					}
				}
			}

			if($result == 'true'){
				$logisticChecked[count($logisticChecked)] = $logistic;
			}
		}

		return $logisticChecked;
	}

	public function checkWithinRound($center, $radius, $coordinates)
	{
		// https://stackoverflow.com/questions/12439801/how-to-check-if-a-certain-coordinates-fall-to-another-coordinates-radius-using-p
		try {
			$earth_radius = 6371;
			
			$dLat = deg2rad($center[0] - $coordinates[0]);  
			$dLon = deg2rad($center[1] - $coordinates[1]);
			
			$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($coordinates[0])) * cos(deg2rad($center[0])) * sin($dLon/2) * sin($dLon/2);  
			$c = 2 * asin(sqrt($a));  
			$d = $earth_radius * $c;  
			
			if($d <  $radius) {
				return 'true';
			} else {
				return 'false';
			}
		} catch (Exception $e) {
			return 'false';
		}
	}

	public function checkWithinPolygon($coords, $coordinates)
	{
		// https://stackoverflow.com/questions/5065039/find-point-in-polygon-php
		try {
			$vertices_x = array();
			$vertices_y = array();

			foreach ($coords as $key => $value) { 
				if(!ctype_space($value)){
					if (($key%2) == 0) array_push($vertices_x, $value);
					else array_push($vertices_y, $value);
				}
			}

			$points_polygon = count($vertices_x) - 1;

			$longitude_x = $coordinates[0];  // x-coordinate of the point to test
			$latitude_y = $coordinates[1]; 

			$i = $j = $c = 0;
			for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
				if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
				($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
			}

			if ($c){
				return 'true';
			}
			else return 'false';

		} catch (Exception $e) {
			return 'false';
		}
	}

	public function checkWithinRectangle($coords, $coordinates)
	{
		// https://stackoverflow.com/questions/5065039/find-point-in-polygon-php
		try {
			$vertices_x = array();
			$vertices_y = array();

			foreach ($coords as $key => $value) { 
				if(!ctype_space($value)){
					if (($key%2) == 0) array_push($vertices_x, $value);
					else array_push($vertices_y, $value);
				}
			}

			$points_polygon = count($vertices_x) - 1;

			$longitude_x = $coordinates[0];  // x-coordinate of the point to test
			$latitude_y = $coordinates[1]; 

			$i = $j = $c = 0;
			for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
				if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
				($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
				$c = !$c;
			}

			if ($c){
				return 'true';
			}
			else return 'false';

		} catch (Exception $e) {
			return 'false';
		}
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
		
		//get list
		$order_detail = new_orders::select('new_orders.id','new_orders.order_type','deliveryboy_id','order_number','reject_cancel_reason','is_external_delivery','new_users.name as customer_name','new_users.mobile_number as customer_number','new_users.id as customerid', 'prescription.name as prescription_name', 'prescription.image as prescription_image','address_new.address as address','new_pharma_logistic_employee.name as deliveryboyname')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
		->where('order_status','incomplete');
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

		$order_detail = $order_detail->orderby('new_orders.id','desc');
		$order_detail = $order_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				//$created_at = ($order->created_at!='')?date('d-M-Y',strtotime($order->created_at)):'';
				//$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				$assign_to = get_name('new_pharma_logistic_employee','name',$order->deliveryboy_id);
				$reason = get_incomplete_reason($order->incompletereason_id);
				if($order->order_type == "manual_order"){
					$html.='<tr><td><a href="'.url('/orders/order_details_manual/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				}else{
					$html.='<tr><td><a href="'.url('/orders/order_details/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				}
				$html.='<td style="text-align:center;">'.$order->customer_name.'</td>
					<td style="text-align:center;">'.$order->customer_number.'</td>
					<td style="text-align:center;">'.$order->address.'</td>
					<td style="text-align:center;" class="text-danger">'.$order->reject_cancel_reason.'</td>
					<td style="text-align:center;">'.$order->deliveryboyname.'</td>';
						$html.='<td style="text-align:center;"><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Reject order" data-toggle="modal" data-target="#assign_modal">Re Delivery</a>';
						$html.='<a onclick="reject_order('.$order->id.')" class="btn btn-danger btn-custom waves-effect waves-light" href="javascript:;" title="Reject order" data-toggle="modal" data-target="#reject_modal">Cancel</a>';
						$html.='</td>';
					
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
						<a class="page-link" onclick="getincompletelist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getincompletelist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
		if($request->delivery_assign_type == 'deliveryboy'){
			$order = new_orders::find($request->assign_id);
			$order->deliveryboy_id = $request->delivery_boy;
			$order->assign_datetime = date('Y-m-d H:i:s');
			$order->order_status = 'assign';
			$order->save();

			$assign = new Orderassign();
			$assign->order_id = $request->assign_id;
			$assign->deliveryboy_id = $request->delivery_boy;
			$assign->order_status = 'assign';
			
			$assign->assign_date = date('Y-m-d H:i:s');
			$assign->created_at = date('Y-m-d H:i:s');
			$assign->updated_at = date('Y-m-d H:i:s');
	
			$assign->save();
		} else if($request->delivery_assign_type == 'logistic') {
			$order = new_orders::find($request->assign_id);
			$order->logistic_user_id = $request->delivery_boy;
			$order->assign_datetime =  date('Y-m-d H:i:s');
			$order->delivery_charges_id = $request->delivery_charges_id;
			$order->order_status = 'assign';
			$order->is_external_delivery = 1;
			$order->external_delivery_initiatedby = 'pharmacy';
			$order->save();
			
			$assign = new Orderassign();
			$assign->order_id = $request->assign_id;
			$assign->logistic_id = $request->delivery_boy;
			$assign->order_status = 'assign';
			
			$assign->assign_date = date('Y-m-d H:i:s');
			$assign->created_at = date('Y-m-d H:i:s');
			$assign->updated_at = date('Y-m-d H:i:s');
		} else {
			$order = new_orders::find($request->assign_id);
			$order->second_attempt_delivery_id = $request->delivery_boy;
			$order->assign_datetime = date('Y-m-d H:i:s');
			
			$order->order_status = 'assign';
			$order->save();
			
			$assign = new Orderassign();
			$assign->order_id = $request->assign_id;
			$assign->second_attempt_delivery_id = $request->delivery_boy;
			$assign->order_status = 'assign';
			
			$assign->assign_date = date('Y-m-d H:i:s');
			$assign->created_at = date('Y-m-d H:i:s');
			$assign->updated_at = date('Y-m-d H:i:s');
	
			$assign->save();
			// PHAR319451
		}
		
		$user_id = Auth::user()->user_id;
		$user_type = Auth::user()->user_type;
		$delivery = new_pharma_logistic_employee::find($request->delivery_boy);
		$ids = array();
		$ids[] = $delivery->fcm_token;
		$receiver_id = array();
		$receiver_id[] = $delivery->id;
		if (count($ids) > 0) {				
			Helper::sendNotificationDeliveryboy($ids, 'Order Number '.$order->order_number, 'Order Assign', $user_id, 'pharmacy', $delivery->id, 'delivery_boy', $delivery->fcm_token);
		}
		
		return redirect(route('incomplete.index'))->with('success_message', trans('Order Successfully assign'));
	}

	public function reject(Request $request)
    {
		//echo $request->reject_reason.'--'.$request->reject_id;exit;
		$user_id = Auth::user()->user_id;
		$order = new_orders::find($request->reject_id);
		$order->process_user_id = $user_id;
		// $order->order_status = 'cancel';
		$order->order_status = 'cancel';
		$order->rejectby_user = 'pharmacy';
		$order->reject_user_id = $user_id;
		// $order->cancel_date = date('Y-m-d H:i:s');
		$order->cancel_datetime = date('Y-m-d H:i:s');
		$order->reject_cancel_reason = $request->rejectreason;
		$order->save();
		$order = new_orders::where('id',$request->reject_id)->first();
         	$order_history = new new_order_history();
            $order_history->order_id = $order->id;
            $order_history->customer_id = $order->customer_id;
            $order_history->prescription_id = $order->prescription_id;
            $order_history->order_number = $order->order_number;
            $order_history->order_status = $order->order_status;
            $order_history->order_note = $order->order_note;
            $order_history->address_id = $order->address_id;
            $order_history->audio = $order->audio;
            $order_history->audio_info = $order->audio_info;
            $order_history->order_type = $order->order_type;
            $order_history->total_days = $order->total_days;
            $order_history->reminder_days = $order->reminder_days;
            $order_history->pharmacy_id = $order->pharmacy_id;
            $order_history->process_user_type = $order->process_user_type;
            $order_history->process_user_id = $order->process_user_id;
            $order_history->logistic_user_id = $order->logistic_user_id;
            $order_history->deliveryboy_id = $order->deliveryboy_id;
            $order_history->second_attempt_delivery_id = $order->second_attempt_delivery_id;
            $order_history->create_datetime  = $order->create_datetime;
            $order_history->accept_datetime  = $order->accept_datetime;
            $order_history->assign_datetime  = $order->assign_datetime;
            $order_history->pickup_datetime  = $order->pickup_datetime;
            $order_history->deliver_datetime = $order->deliver_datetime;
            $order_history->second_attempt_delivery_datetime = $order->second_attempt_delivery_datetime;
            $order_history->return_datetime  = $order->return_datetime;
            $order_history->cancel_datetime  = $order->cancel_datetime;
            $order_history->rejectby_user  = $order->rejectby_user;
            $order_history->reject_user_id  = $order->reject_user_id;
            $order_history->reject_cancel_reason  = $order->reject_cancel_reason;
            $order_history->leave_neighbour  = $order->leave_neighbour;
            $order_history->neighbour_info  = $order->neighbour_info;
            $order_history->is_external_delivery  = $order->is_external_delivery;
            $order_history->external_delivery_initiatedby  = $order->external_delivery_initiatedby;
            $order_history->order_amount  = $order->order_amount;
            $order_history->delivery_charges_id   = $order->delivery_charges_id;
            $order_history->is_delivery_charge_collect  = $order->is_delivery_charge_collect;
            $order_history->is_amount_collect  = $order->is_amount_collect;
            $order_history->is_refund_intiated  = $order->is_refund_intiated;
            $order_history->refund_datetime  = $order->refund_datetime;
            /*$order_history->refund_info  = $order->refund_info;
            $order_history->is_admin_amount_collect  = $order->is_admin_amount_collect;
            $order_history->is_pharmacy_amount_collect  = $order->is_pharmacy_amount_collect;
            $order_history->is_logistic_charge_collect  = $order->is_logistic_charge_collect;
            $order_history->is_admin_delivery_charge_collect  = $order->is_admin_delivery_charge_collect;
            $order_history->is_logistic_amount_collect  = $order->is_logistic_amount_collect;*/
            $order_history->created_at    = $order->created_at;
            $order_history->updated_at  = $order->updated_at;
            $order_history->save();
            $order_delete = new_orders::find($order->id);
            $order_delete->delete();
		return redirect(route('incomplete.index'))->with('success_message', trans('Order Successfully cancel'));
	}

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
		$data['deliveryboy_list'] = new_pharma_logistic_employee::where('parent_type', 'logistic')->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->where('is_active','1')->where('is_active', 1)->get();
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
				$created_at = ($order->created_at!='')?date('d-M-Y',strtotime($order->created_at)):'';
				$updated_at = ($order->updated_at!='')?date('d-M-Y',strtotime($order->updated_at)):'';
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order->prescription_image);
					}
				}
				//$assign_to = get_name('users','name',$order->deliveryboy_id);
				//$reason = get_incomplete_reason($order->incompletereason_id);
				$html.='<tr>
					<td><a href="'.url('/logistic/incomplete/order_details/'.$order->id).'"><img src="'.$image_url.'" width="50"/><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->delivery_type.'</td>
					<td>'.$order->pharmacyaddress.'</td>
					<td>'.$order->address.'</td>
					<td>'.$order->order_amount.'</td>
					<td>'.$order->deliveryboyname.'</td>';
					if($order->is_external_delivery > 0){
						$html.='<td><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Reassign order" data-toggle="modal" data-target="#assign_modal">Re Delivery</a>';
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
		$order_detail = DB::table('new_orders')->select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','new_pharmacies.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname')
		->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
		->leftJoin('new_pharmacies', 'new_pharmacies.id', '=', 'new_orders.pharmacy_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
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
		$data['page_title'] = 'Prescription';
		$data['page_condition'] = 'page_prescription';
		$data['site_title'] = 'Prescription | ' . $this->data['site_title'];
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
