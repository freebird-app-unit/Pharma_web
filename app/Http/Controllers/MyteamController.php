<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\new_pharmacies;
use App\new_pharma_logistic_employee;
use DB;
use Auth;
use Storage;
use Image;
use File;
use App\new_order_history;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

use App\new_users;
use App\new_logistics;

class MyteamController extends Controller
{
     public function index()
    {
		$data = array();
		$data['page_title'] = 'My Team';
		$data['page_condition'] = 'page_myteam';
		$data['site_title'] = 'My team | ' . $this->data['site_title'];
        return view('myteam.index', $data);
    }

    public function getlist()
    {
		$user_id = Auth::user()->user_id;
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		$search_pharmacy=(isset($_POST['search_pharmacy']) && $_POST['search_pharmacy']!='')?$_POST['search_pharmacy']:'';
		//get list
			$user_detail = new_pharma_logistic_employee::select('new_pharma_logistic_employee.*')->where('pharma_logistic_id',$user_id)->where('is_active','1');
		
		if($searchtxt!=''){
			$user_detail= $user_detail->where(function ($query) use($searchtxt) {
                $query->where('name', 'like','%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
            });
		}
		if($search_pharmacy!=''){
			$user_detail= $user_detail->where('pharma_logistic_id', $search_pharmacy);
		}
		$total = $user_detail->count();
		$total_page = ceil($total/$per_page);

		$user_detail = $user_detail->orderby('new_pharma_logistic_employee.created_at','DESC');
		$user_detail = $user_detail->paginate($per_page,'','',$page);
		
		//get list
		if(count($user_detail)>0){
			foreach($user_detail as $user){
			
				$html.='<tr>
					<td>'.$user->name.'</td>
					<td>'.$user->user_type.'</td>
					<td>'.$user->email.'</td>
					<td>'.$user->mobile_number.'</td>
					<td>'.$user->address.'</td>';
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
						<a class="page-link" onclick="getmyteamlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getmyteamlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
