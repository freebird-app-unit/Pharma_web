<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\new_pharma_logistic_employee;
use App\new_order_history;
use App\new_orders;
use DB;
use Auth;

class PharmaExternalDeliveryReportTestDirectController extends Controller{

    public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    
    public function index()
    {
        /*if(Auth::user()->user_type!='pharmacy' ){
			return redirect(route('home'));
		}*/
		$user_id = Auth::user()->user_id;
		$data = array();
		$user_type = Auth::user()->user_type;
		
         $ord_field=(isset($_REQUEST['ord_field']) && $_REQUEST['ord_field']!='')?$_REQUEST['ord_field']:'';
         $sortord=(isset($_REQUEST['sortord']) && $_REQUEST['sortord']!='')?$_REQUEST['sortord']:'';
         $page=(isset($_REQUEST['pageno']) && $_REQUEST['pageno']!='')?$_REQUEST['pageno']:1;
		 $per_page=(isset($_REQUEST['perpage']) && $_REQUEST['perpage']!='')?$_REQUEST['perpage']:10;
		 $record_display = (isset($_REQUEST['record_display']))?$_REQUEST['record_display']:'';

        //getlist
		 $detail = new_pharma_logistic_employee::select('new_pharma_logistic_employee.id')->where(['user_type'=> 'delivery_boy','is_active'=> 1])->where(['parent_type'=> 'logistic']);
		 $detail = $detail->leftJoin('new_orders', 'new_orders.deliveryboy_id', '=', 'new_pharma_logistic_employee.id')->where('new_orders.pharmacy_id','=',$user_id);
		 $detail = $detail->leftJoin('new_order_history', 'new_order_history.deliveryboy_id', '=', 'new_pharma_logistic_employee.id');
		 //->where('new_order_history.pharmacy_id','=',$user_id)
		 $detail = $detail->groupBy('new_pharma_logistic_employee.id');
		 $detail = $detail->orderby('new_pharma_logistic_employee.id','desc');
		 $total = $detail->count();
		 $total_page = ceil($total/$per_page);
         $deliveryboy_list = $detail->paginate($per_page);
         $data['deliveryboy_list'] = $deliveryboy_list;
         $data['user_id'] = $user_id;
		$data['page_title'] = 'External Delivery Report';
		$data['page_condition'] = 'page_pharma_external_delivery_test_report_direct';
        $data['site_title'] = 'External Delivery Report | ' . $this->data['site_title'];
        return view('pharma_external_delivery_report.index_test_direct', $data);
    }

    public function getExternalDeliveryReport(){
		$user_id = Auth::user()->user_id;
		
         
         
    }


}