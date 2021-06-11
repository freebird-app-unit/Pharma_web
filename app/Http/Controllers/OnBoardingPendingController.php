<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use DB;
use Auth;
use App\Onboardingrequest;
use App\new_pharmacies;
use App\User;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
use Paykun\Checkout\Payment;
use App\pass_data_image;
use App\Register;

class OnBoardingPendingController extends Controller
{
	public function index()
    {
	    $data = array();
		$data['page_title'] = 'Onboarding Pending';
		$data['page_condition'] = 'page_onboardingpending';
		$data['site_title'] = 'Onboarding Pending | ' . $this->data['site_title'];
	    return view('onboardingpending.index', $data);
	}

	public function getlist()
    {
		$html='';
		$pagination='';
		$total_summary='';
		$user_detail = array();
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		$user_type=(isset($_POST['user_type']) && $_POST['user_type']!='')?$_POST['user_type']:'';
		$search_city=(isset($_POST['search_city']) && $_POST['search_city']!='')?$_POST['search_city']:'';

		
		//count total
		$total_new_users =  Register::select('id','first_name','last_name','created_at','is_process','email','address','phone','step_one','step_two','step_three','step_four','step_five')->where('is_process','incomplete');

		if($searchtxt!=''){
			$total_new_users = $total_new_users->where(function ($query) use($searchtxt) {
                $query->where('first_name', 'like', '%'.$searchtxt.'%');
			});
		}

			
		$total_result = $total_new_users->get();
		$total = count($total_result);
		$total_page = ceil($total/$per_page);
		
		$user_detail = $total_new_users->orderby('created_at', 'ASC')->paginate($per_page,'','',$page);
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				
				$html.='<tr>
					<td>'.$user->first_name.'</td>
					<td>'.$user->last_name.'</td>
					<td>'.$user->address.'</td>
					<td>'.$user->email.'</td>
					<td>'.$user->phone.'</td>
					<td>'.$created_at.'</td>';
					if($user->step_one == 0){
						$html.='<td><a class="btn btn-info waves-effect waves-light" href="http://myhealthchart.in/registration" title="Detail" target="_blank">Process</a>';
					}elseif ($user->step_two == 0) {
						$html.='<td><a class="btn btn-info waves-effect waves-light" href="http://myhealthchart.in/registeration_step_two/'.$user->id.'" title="Detail" target="_blank">Process</a>';
					}elseif ($user->step_three == 0) {
						$html.='<td><a class="btn btn-info waves-effect waves-light" href="http://myhealthchart.in/registeration_step_three/'.$user->id.'" title="Detail" target="_blank">Process</a>';
					}elseif ($user->step_four == 0) {
						$html.='<td><a class="btn btn-info waves-effect waves-light" href="http://myhealthchart.in/registeration_step_four/'.$user->id.'" title="Detail" target="_blank">Process</a>';
					}elseif ($user->step_four == 0) {
						$html.='<td><a class="btn btn-info waves-effect waves-light" href="http://myhealthchart.in/registeration_step_five/'.$user->id.'" title="Detail" target="_blank">Process</a>';
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
						<a class="page-link" onclick="getcategorylist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getcategorylist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
