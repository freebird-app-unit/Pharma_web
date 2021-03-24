<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
class DeliveryreportController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='pharmacy'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Delivery report';
		$data['page_condition'] = 'page_deliveryreport';
		$data['site_title'] = 'Delivery report | ' . $this->data['site_title'];
        return view('deliveryreport.index', $data);
    }
	public function getlist()
    {
		$user_id = Auth::user()->id;
		$html='';
		$pagination='';
		$total_summary='';
		
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		$filter_start_date=(isset($_POST['filter_start_date']) && $_POST['filter_start_date']!='')?date('Y-m-d',strtotime(str_replace('/','-',$_POST['filter_start_date']))):'';
		$filter_end_date=(isset($_POST['filter_end_date']) && $_POST['filter_end_date']!='')?date('Y-m-d',strtotime(str_replace('/','-',$_POST['filter_end_date']))):'';
		//count total
		$total_res = DB::table('users')->where('user_type','delivery_boy')->where('parentuser_id',$user_id);
		if($searchtxt!=''){
			$total_res= $total_res->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', $searchtxt.'%')
						->orWhere('email', 'like', $searchtxt.'%')
						->orWhere('mobile_number', 'like', $searchtxt.'%');
            });
		}
		$total_res= $total_res->get();
		$total = count($total_res);
		$total_page = ceil($total/$per_page);
		//count total
		
		//get list
		$user_detail = DB::table('users')->select('users.*')->where('user_type','delivery_boy')->where('parentuser_id',$user_id);
		if($searchtxt!=''){
			$user_detail= $user_detail->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', $searchtxt.'%')
						->orWhere('email', 'like', $searchtxt.'%')
						->orWhere('mobile_number', 'like', $searchtxt.'%');
            });
		}
		$user_detail = $user_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				$updated_at = ($user->updated_at!='')?date('d-M-Y',strtotime($user->updated_at)):'';
				$image_url = '';
				if($user->profile_image!=''){
					$destinationPath = base_path() . '/uploads/'.$user->profile_image;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/uploads/'.$user->profile_image;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				
				$order_completed = get_deliveryboy_completed_order($user->id,$filter_start_date,$filter_end_date);
				$order_incomplete = get_deliveryboy_incomplete_order($user->id,$filter_start_date,$filter_end_date);
				$order_rejected = get_deliveryboy_rejected_order($user->id,$filter_start_date,$filter_end_date);
				$total_order = get_deliveryboy_total_order($user->id,$filter_start_date,$filter_end_date);
				$html.='<tr>
					<td><img class="img-responsive img-circle" src="'.$image_url.'" width="50"/></td>
					<td>'.$user->name.'</td>
					<td>'.$order_completed.'</td>
					<td>'.$order_incomplete.'</td>
					<td>'.$order_rejected.'</td>
					<td>'.$total_order.'</td>';
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
						<a class="page-link" onclick="getallorderlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getallorderlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
