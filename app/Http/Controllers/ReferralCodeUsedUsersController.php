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
use Storage;
use Image;
use File;

use App\new_pharma_logistic_employee;
use App\new_users;
use App\new_address;
use App\new_pharmacies;
use App\new_logistics;
use Illuminate\Validation\Rule;
use App\new_countries;
use App\new_states;
use App\new_cities;
use App\new_orders;
use App\new_order_history;
use App\DeliveryboyModel\new_order_images;
use App\Rejectreason;

class ReferralCodeUsedUsersController extends Controller
{
      public function index()
    {
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$data['page_title'] = 'Referral Code Used Users';
		$data['page_condition'] = 'page_referral_code_used_users';
		$data['site_title'] = 'Referral Code Used Users | ' . $this->data['site_title'];
        return view('referral_code_used_users.index', $data);
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
		$pharmacy_data =  new_pharmacies::where('id',Auth::user()->user_id)->first();

		$total_new_users = new_users::select('id','referral_code','name','created_at')->where('referral_code','=',$pharmacy_data->referral_code)->where(['is_active'=>'1','is_delete'=>'1']);
		
		if($searchtxt!=''){
			$total_new_users = $total_new_users->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%');
			});
		}
		
		
		$total_result = $total_new_users->get();
		$total = count($total_result);
		$total_page = ceil($total/$per_page);

		$user_detail = $total_new_users->orderby('new_users.created_at', 'DESC')->paginate($per_page,'','',$page);
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				
				$html.='<tr>
					<td>'.$user->name.'</td>
					<td>'.$created_at.'</td>';
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
						<a class="page-link" onclick="getregistrationpendinglist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getregistrationpendinglist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
