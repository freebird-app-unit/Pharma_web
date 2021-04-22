<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\new_pharma_logistic_employee;
use App\new_order_history;
use App\new_orders;
use DB;
use Auth;

class PharmaDeliveryReportController extends Controller{

    public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    
    public function index()
    {
        if(Auth::user()->user_type!='pharmacy' ){
			return redirect(route('home'));
		}
		$user_id = Auth::user()->user_id;
		$data = array();

		$data = array();
		$data['page_title'] = 'Delivery Report';
		$data['page_condition'] = 'page_pharma_delivery_report';
        $data['site_title'] = 'Delivery Report | ' . $this->data['site_title'];
        return view('pharma_delivery_report.index', $data);
    }

    public function getDeliveryReport(){
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
		 $detail = new_pharma_logistic_employee::select('id','name','pharma_logistic_id')->where(['user_type'=> 'delivery_boy','is_active'=> 1])->where(['parent_type'=> 'pharmacy', 'pharma_logistic_id'=> $user_id]);
		 $total = $detail->count();
		 $total_page = ceil($total/$per_page);

	     $detail = $detail->orderby('new_pharma_logistic_employee.id','desc');
         $detail = $detail->paginate($per_page,'','',$page);

         if(count($detail)>0){
			foreach($detail as $data){
				$created_at = ($data->created_at!='')?date('d-M-Y',strtotime($data->created_at)):'';
                ///////////////////////////////////////////////////////////////////
                $number_of_delivery_new_order = new_orders::select('id')->where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                if($record_display == 'yearly'){
		          $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		          $start_date = date('Y-01-01');
		          $end_date = date('Y-12-31');
		          $number_of_delivery_new_order = $number_of_delivery_new_order->whereYear('accept_datetime','=',$record_yearly); 
		        }elseif($record_display == 'monthly'){
		            $query_date = date('Y-m-d');
		            $start_date = date('Y-m-01', strtotime($query_date));
		            $end_date = date('Y-m-t', strtotime($query_date));
		            $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		            $record_monthly = (isset($_REQUEST['record_monthly']))?$_REQUEST['record_monthly']:'1';
		            $start_date = $record_yearly.'-'.$record_monthly.'-01';
		            $end_date = $record_yearly.'-'.$record_monthly.'-31';
					$start_date = date('Y-m-d',strtotime($start_date));
					$end_date = date('Y-m-d',strtotime($end_date));
		            $number_of_delivery_new_order = $number_of_delivery_new_order->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }else{
		            if(date('D')!='Mon'){    
		                $start_date = date('Y-m-d',strtotime('last Monday'));    
		            }else{
		                $start_date = date('Y-m-d');   
		            }
		            if(date('D')!='Sun'){
		                $end_date = date('Y-m-d',strtotime('next Sunday'));
		            }else{
		                $end_date = date('Y-m-d');
		            }
		            $number_of_delivery_new_order = $number_of_delivery_new_order->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }
                $number_of_delivery_count = new_order_history::select('id')->where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                if($record_display == 'yearly'){
		          $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		          $start_date = date('Y-01-01');
		          $end_date = date('Y-12-31');
		          $number_of_delivery_count = $number_of_delivery_count->whereYear('accept_datetime','=',$record_yearly); 
		        }elseif($record_display == 'monthly'){
		            $query_date = date('Y-m-d');
		            $start_date = date('Y-m-01', strtotime($query_date));
		            $end_date = date('Y-m-t', strtotime($query_date));
		            $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		            $record_monthly = (isset($_REQUEST['record_monthly']))?$_REQUEST['record_monthly']:'1';
		            $start_date = $record_yearly.'-'.$record_monthly.'-01';
		            $end_date = $record_yearly.'-'.$record_monthly.'-31';
					$start_date = date('Y-m-d',strtotime($start_date));
					$end_date = date('Y-m-d',strtotime($end_date));
		            $number_of_delivery_count = $number_of_delivery_count->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }else{
		            if(date('D')!='Mon'){    
		                $start_date = date('Y-m-d',strtotime('last Monday'));    
		            }else{
		                $start_date = date('Y-m-d');   
		            }
		            if(date('D')!='Sun'){
		                $end_date = date('Y-m-d',strtotime('next Sunday'));
		            }else{
		                $end_date = date('Y-m-d');
		            }
		            $number_of_delivery_count = $number_of_delivery_count->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }
                $number_of_delivery_count = $number_of_delivery_count->union($number_of_delivery_new_order)->count();
                /////////////////////////////////////////////////////
                $delivered_return_new_order = new_orders::select('id')->where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                if($record_display == 'yearly'){
		          $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		          $start_date = date('Y-01-01');
		          $end_date = date('Y-12-31');
		          $delivered_return_new_order = $delivered_return_new_order->whereYear('accept_datetime','=',$record_yearly); 
		        }elseif($record_display == 'monthly'){
		            $query_date = date('Y-m-d');
		            $start_date = date('Y-m-01', strtotime($query_date));
		            $end_date = date('Y-m-t', strtotime($query_date));
		            $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		            $record_monthly = (isset($_REQUEST['record_monthly']))?$_REQUEST['record_monthly']:'1';
		            $start_date = $record_yearly.'-'.$record_monthly.'-01';
		            $end_date = $record_yearly.'-'.$record_monthly.'-31';
					$start_date = date('Y-m-d',strtotime($start_date));
					$end_date = date('Y-m-d',strtotime($end_date));
		            $delivered_return_new_order = $delivered_return_new_order->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }else{
		            if(date('D')!='Mon'){    
		                $start_date = date('Y-m-d',strtotime('last Monday'));    
		            }else{
		                $start_date = date('Y-m-d');   
		            }
		            if(date('D')!='Sun'){
		                $end_date = date('Y-m-d',strtotime('next Sunday'));
		            }else{
		                $end_date = date('Y-m-d');
		            }
		            $delivered_return_new_order = $delivered_return_new_order->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }
                $delivered_return_count = new_order_history::select('id')->where('deliveryboy_id','=',$data->id)->where('order_status','=','reject');
                if($record_display == 'yearly'){
		          $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		          $start_date = date('Y-01-01');
		          $end_date = date('Y-12-31');
		          $delivered_return_count = $delivered_return_count->whereYear('accept_datetime','=',$record_yearly); 
		        }elseif($record_display == 'monthly'){
		            $query_date = date('Y-m-d');
		            $start_date = date('Y-m-01', strtotime($query_date));
		            $end_date = date('Y-m-t', strtotime($query_date));
		            $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		            $record_monthly = (isset($_REQUEST['record_monthly']))?$_REQUEST['record_monthly']:'1';
		            $start_date = $record_yearly.'-'.$record_monthly.'-01';
		            $end_date = $record_yearly.'-'.$record_monthly.'-31';
					$start_date = date('Y-m-d',strtotime($start_date));
					$end_date = date('Y-m-d',strtotime($end_date));
		            $delivered_return_count = $delivered_return_count->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }else{
		            if(date('D')!='Mon'){    
		                $start_date = date('Y-m-d',strtotime('last Monday'));    
		            }else{
		                $start_date = date('Y-m-d');   
		            }
		            if(date('D')!='Sun'){
		                $end_date = date('Y-m-d',strtotime('next Sunday'));
		            }else{
		                $end_date = date('Y-m-d');
		            }
		            $delivered_return_count = $delivered_return_count->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }
                $delivered_return_count = $delivered_return_count->union($delivered_return_new_order)->count();
                ///////////////////////////////////////////////////////
                $total_amount_new_order = new_orders::where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                if($record_display == 'yearly'){
		          $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		          $start_date = date('Y-01-01');
		          $end_date = date('Y-12-31');
		          $total_amount_new_order = $total_amount_new_order->whereYear('accept_datetime','=',$record_yearly); 
		        }elseif($record_display == 'monthly'){
		            $query_date = date('Y-m-d');
		            $start_date = date('Y-m-01', strtotime($query_date));
		            $end_date = date('Y-m-t', strtotime($query_date));
		            $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		            $record_monthly = (isset($_REQUEST['record_monthly']))?$_REQUEST['record_monthly']:'1';
		            $start_date = $record_yearly.'-'.$record_monthly.'-01';
		            $end_date = $record_yearly.'-'.$record_monthly.'-31';
					$start_date = date('Y-m-d',strtotime($start_date));
					$end_date = date('Y-m-d',strtotime($end_date));
		            $total_amount_new_order = $total_amount_new_order->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }else{
		            if(date('D')!='Mon'){    
		                $start_date = date('Y-m-d',strtotime('last Monday'));    
		            }else{
		                $start_date = date('Y-m-d');   
		            }
		            if(date('D')!='Sun'){
		                $end_date = date('Y-m-d',strtotime('next Sunday'));
		            }else{
		                $end_date = date('Y-m-d');
		            }
		            $total_amount_new_order = $total_amount_new_order->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }
                $total_amount_new_order = $total_amount_new_order->sum('order_amount');
                $total_amount_new_order_history = new_order_history::where('deliveryboy_id','=',$data->id)->where('order_status','=','complete');
                if($record_display == 'yearly'){
		          $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		          $start_date = date('Y-01-01');
		          $end_date = date('Y-12-31');
		          $total_amount_new_order_history = $total_amount_new_order_history->whereYear('accept_datetime','=',$record_yearly); 
		        }elseif($record_display == 'monthly'){
		            $query_date = date('Y-m-d');
		            $start_date = date('Y-m-01', strtotime($query_date));
		            $end_date = date('Y-m-t', strtotime($query_date));
		            $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
		            $record_monthly = (isset($_REQUEST['record_monthly']))?$_REQUEST['record_monthly']:'1';
		            $start_date = $record_yearly.'-'.$record_monthly.'-01';
		            $end_date = $record_yearly.'-'.$record_monthly.'-31';
					$start_date = date('Y-m-d',strtotime($start_date));
					$end_date = date('Y-m-d',strtotime($end_date));
		            $total_amount_new_order_history = $total_amount_new_order_history->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }else{
		            if(date('D')!='Mon'){    
		                $start_date = date('Y-m-d',strtotime('last Monday'));    
		            }else{
		                $start_date = date('Y-m-d');   
		            }
		            if(date('D')!='Sun'){
		                $end_date = date('Y-m-d',strtotime('next Sunday'));
		            }else{
		                $end_date = date('Y-m-d');
		            }
		            $total_amount_new_order_history = $total_amount_new_order_history->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
		        }
                $total_amount_new_order_history = $total_amount_new_order_history->sum('order_amount');
                $total_amount = $total_amount_new_order + $total_amount_new_order_history;
                //////////////////////////////////////////////////////////////////////////////////////////////////
				$html.='<tr>
					<td>'.$data->name.'</td>
					<td>'.$number_of_delivery_count.'</td>
                    <td>'.$total_amount.'</td>
					<td>'.$delivered_return_count.'</td>';
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