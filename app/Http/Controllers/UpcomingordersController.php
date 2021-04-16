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
use App\delivery_charges;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;

use App\new_pharma_logistic_employee;
use App\new_logistics;
use App\new_orders;
use App\new_users;
use App\new_pharmacies;
use App\new_delivery_charges;

class UpcomingordersController extends Controller
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
		$data['page_title'] = 'Live Orders';
		$data['page_condition'] = 'page_upcomingorder';
		$data['site_title'] = 'Accepted Orders | ' . $this->data['site_title'];
		
		if(Auth::user()->user_type=='seller'){
			$data['deliveryboy_list'] = User::where('parentuser_id', $user_id)->where('user_type', 'delivery_boy')->get();
		}else if(Auth::user()->user_type=='pharmacy'){
			$data['deliveryboy_list'] = new_pharma_logistic_employee::where(['parent_type'=> 'pharmacy', 'is_active'=> 1])->where('user_type','delivery_boy')->where('pharma_logistic_id', $user_id)->get();
		}

		$pharmacy = new_pharmacies::select('lat', 'lon', 'city')->where(['id'=> Auth::user()->user_id, 'is_active'=> 1])->first();
		$logistics = new_logistics::where(['city' => $pharmacy->city, 'is_active'=> 1])->get();

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

		$data['id'] = $user_id;
		$data['logistic_list'] = json_encode($logistic_list);
		$data['reject_reason'] = Rejectreason::where('type', 'pharmacy')->get();
        return view('upcomingorders.index', $data);
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
		
		$order_detail = new_orders::select('new_orders.id','new_orders.created_at','accept_datetime','order_number','new_users.name as customer_name','new_users.mobile_number as customer_number','new_users.id as customerid','address_new.address as myaddress','new_delivery_charges.delivery_type as delivery_type', 'process_user.name as process_user_name', 'process_employee.name as process_employee_name','new_orders.process_user_id','new_orders.process_user_type')
		->leftJoin('new_users', 'new_users.id', '=', 'new_orders.customer_id')
		->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
		->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
		->leftJoin('new_pharmacies AS process_user', 'process_user.id', '=', 'new_orders.process_user_id')
		->leftJoin('new_pharma_logistic_employee AS process_employee', 'process_employee.id', '=', 'new_orders.process_user_id')
		->where('new_orders.order_status','new')
		->where('new_orders.is_external_delivery','0');
		
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
		$queries = DB::getQueryLog();
		//get list
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$accept_date = ($order->created_at!='')?date('d-M-Y h:i a', strtotime($order->created_at)):'';

				$process_user_name = '';
				if($order->process_user_type == 'pharmacy'){
					$process_user_name = $order->process_user_name;
				} else {
					$process_user_name = $order->process_employee_name;
				}
				$seller_name = "";
				//echo $order->id.'-'.$order->process_user_type.'-<br>';
				if($order->process_user_type == 'seller'){
					$seller_data = new_pharma_logistic_employee::find($order->process_user_id);
					$seller_name = $order->process_employee_name;;
				}
				$html.='<tr>
					<td>'.ucwords(strtolower($order->customer_name)).'</td>
					<td><a href="'.url('/orders/order_details/'.$order->id).'"><span>'.$order->order_number.'</span></a></td>
					<td>'.$order->myaddress.'</td>
					<td>'.$accept_date.'</td>';
					//$html.='<td><a onclick="assign_order('.$order->id.')" class="btn btn-warning btn-custom waves-effect waves-light" title="Reject order" data-toggle="modal" data-target="#assign_modal">Assign</a>';
					$html.='<td><a href="'.url('/orders/accept/'.$order->id).'" class="btn btn-warning btn-custom waves-effect waves-light" title="Accept order">Accept</a>';
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
						<a class="page-link" onclick="getupcomingorderlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getupcomingorderlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
		//echo $request->reject_reason.'--'.$request->reject_id;exit;
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
		}
		
		return redirect(route('acceptedorders.index'))->with('success_message', trans('Order Successfully assign'));
	}
	public function accept($id)
    {
		$user_id = Auth::user()->id;
		$order = Orders::find($id);
		$order->process_user_id = $user_id;
		$order->order_status = 'accept';
		$order->save();
		return redirect(route('orders.index'))->with('success_message', trans('Order Successfully accepted'));
	}
	
}
