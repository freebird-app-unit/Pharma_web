<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\new_orders;
use App\new_users;
use App\new_pharma_logistic_employee;
use DB;
use Auth;
use Carbon;

class PharmaOrderReportController extends Controller
{
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
		$data['page_title'] = 'Orders Report';
		$data['page_condition'] = 'page_pharma_order_report';
        $data['site_title'] = 'Orders Report | ' . $this->data['site_title'];
        return view('pharma_order_report.index', $data);
    }
    public function getPharmaOrderReport(){

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
        
        $detail = new_orders::select('customer_id','id','order_number','process_user_id','deliveryboy_id','accept_datetime')->where('pharmacy_id','=',$user_id);

        if($record_display == 'yearly'){
          $record_yearly = (isset($_REQUEST['record_yearly']))?$_REQUEST['record_yearly']:'2000';
          $start_date = date('Y-01-01');
          $end_date = date('Y-12-31');
          $detail = $detail->whereYear('accept_datetime','=',$record_yearly); 
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
            $detail = $detail->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
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
            $detail = $detail->whereDate('accept_datetime','>=',$start_date)->whereDate('accept_datetime','<=',$end_date); 
        }
        $total = $detail->count();
        $total_page = ceil($total/$per_page);

        $detail = $detail->orderby('new_orders.id','desc');
        $detail = $detail->paginate($per_page,'','',$page);

        if(count($detail)>0){
           foreach($detail as $data){
             
            $customer_detail = new_users::select('new_users.name','address_new.address')->where('new_users.id',$data->customer_id)->leftJoin('address_new', 'address_new.user_id', '=', 'new_users.id')->first();
            $customer_name = "";
            $customer_address = "";
            if($customer_detail){
                $customer_name = $customer_detail->name;
                $customer_address = $customer_detail->address;
            }

            $seller_details = new_pharma_logistic_employee::select('name')->where('user_type','=','seller')->where('id','=',$data->process_user_id)->first();
            $seller_name = "Not accepted";
            if($seller_details){
                $seller_name = $seller_details->name;
            }
            $delivery_details = new_pharma_logistic_employee::select('name')->where('user_type','=','delivery_boy')->where('id','=',$data->deliveryboy_id)->first();
            $delievry_name = "Not Assign";
            if($delivery_details){
                $delievry_name = $delivery_details->name;
            }
                   $html.='<tr>
                   <td><a href="'.url('/orders/order_details/'.$data->id).'"><span>'.$data->order_number.'</span></a></td>
                   <td>'.$customer_name.'</td>
                   <td>'.$customer_address.'</td>
                   <td>'.$seller_name.'</td>
                   <td>'.$delievry_name.'</td>
				   <td>'.$data->accept_datetime.'</td>';
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
                       <a class="page-link" onclick="getPharmaOrderReport('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
                   </li>
                   <li class="page-item '.$next.'">
                       <a class="page-link" onclick="getPharmaOrderReport('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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

