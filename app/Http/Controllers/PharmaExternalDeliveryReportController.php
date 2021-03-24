<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\new_pharma_logistic_employee;
use App\new_order_history;
use App\new_orders;
use DB;
use Auth;

class PharmaExternalDeliveryReportController extends Controller{

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

		$data = array();
		$data['page_title'] = 'External Delivery Report';
		$data['page_condition'] = 'page_pharma_external_delivery_report';
        $data['site_title'] = 'External Delivery Report | ' . $this->data['site_title'];
        return view('pharma_external_delivery_report.index', $data);
    }

    public function getExternalDeliveryReport(){
		$user_id = Auth::user()->user_id;
		$user_type = Auth::user()->user_type;
         
         $html='';
         $pagination='';
         $total_summary='';

         $ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
         $sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
         $page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		 $per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		 $record_display = (isset($_REQUEST['record_display']))?$_REQUEST['record_display']:'';
		 

        //getlist
		 $detail = new_pharma_logistic_employee::select('new_pharma_logistic_employee.id')->where(['user_type'=> 'delivery_boy','is_active'=> 1])->where(['parent_type'=> 'logistic']);
		 $detail = $detail->leftJoin('new_orders', 'new_orders.deliveryboy_id', '=', 'new_pharma_logistic_employee.id')->where('new_orders.pharmacy_id','=',$user_id);
		 $detail = $detail->leftJoin('new_order_history', 'new_order_history.deliveryboy_id', '=', 'new_pharma_logistic_employee.id');//->where('new_order_history.pharmacy_id','=',$user_id)
		 $detail = $detail->groupBy('new_pharma_logistic_employee.id');
		 $total = $detail->count();
		 $total_page = ceil($total/$per_page);

	     //$detail = $detail->orderby('new_pharma_logistic_employee.id','desc');
         $detail = $detail->paginate($per_page,'','',$page);
         if(count($detail)>0){
			foreach($detail as $data){
				$created_at = ($data->created_at!='')?date('d-M-Y',strtotime($data->created_at)):'';
                ///////////////////////////////////////////////////////////////////
                $number_of_delivery_new_order = new_orders::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');//
                $number_of_delivery_count = new_order_history::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                $number_of_delivery_count = $number_of_delivery_count->union($number_of_delivery_new_order)->count();
                /////////////////////////////////////////////////////
                $delivered_return_new_order = new_orders::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$data->id)->where('order_status','=','reject');
                $delivered_return_count = new_order_history::select('id')->where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$data->id)->where('order_status','=','reject');
                $delivered_return_count = $delivered_return_count->union($delivered_return_new_order)->count();
                ///////////////////////////////////////////////////////
                $total_amount_new_order = new_orders::where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                $total_amount_new_order = $total_amount_new_order->sum('order_amount');
                $total_amount_new_order_history = new_order_history::where('pharmacy_id','=',$user_id)->where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                $total_amount_new_order_history = $total_amount_new_order_history->sum('order_amount');
                $total_amount = $total_amount_new_order + $total_amount_new_order_history;
                $delivery_boy_name = get_name('new_pharma_logistic_employee','name',$data->id);
                //////////////////////////////////////////////////////////////////////////////////////////////////
				$html.='<tr>
					<td>'.$delivery_boy_name.'</td>
					<td>'.$number_of_delivery_count.'</td>
                    <td>'.$delivered_return_count.'</td>
					<td>'.$total_amount.'</td>';
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
						<a class="page-link" onclick="getDeliveryReport('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getDeliveryReport('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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