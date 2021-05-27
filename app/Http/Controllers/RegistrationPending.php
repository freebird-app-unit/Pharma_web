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


class RegistrationPending extends Controller
{
    public function index()
    {
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$user_city = new_address::select('city')->groupby('city')->get();
		$data['user_city'] = $user_city;
		$data['page_title'] = 'Registration Pending';
		$data['page_condition'] = 'page_registration_pending';
		$data['site_title'] = 'Registration Pending | ' . $this->data['site_title'];
        return view('registration_pending.index', $data);
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
		$total_new_users = new_users::select('id', 'referral_code','name', 'email', 'mobile_number','is_active','profile_image', DB::raw("'' as city"), 'created_at', DB::raw("'customer' as user_type"))->where('name','=','')->where('email','=','')->where('is_delete','1');
		//$total_new_pharmacies = new_pharmacies::select('id', 'name', 'email', 'mobile_number','is_active', 'city', 'created_at', DB::raw("'pharmacy' as user_type"));
		//$total_new_pharma_logistic_employee = new_pharma_logistic_employee::select('id', 'name', 'email', 'mobile_number','is_active', DB::raw("'' as city"), 'created_at', 'user_type');

		if($searchtxt!=''){
			$total_new_users = $total_new_users->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%')
				->orWhere('referral_code', 'like', '%'.$searchtxt.'%');
			});
			
			/*$total_new_pharmacies = $total_new_pharmacies->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
			});
			
			$total_new_pharma_logistic_employee = $total_new_pharma_logistic_employee->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
            }); */
		}
		if($search_city!=''){
			$user_city = new_address::select('user_id')->where('city', $search_city)->get();
			$selected_user = array();
			foreach ($user_city as $user_city_key => $user_city_value) {
				$selected_user[] = $user_city_value->user_id;
			}	
			if(count($selected_user) == 0){
				$selected_user = array('no_found');
			}
			$total_new_users = $total_new_users->whereIn('id', $selected_user);
		}
		if($user_type != ''){
			if($user_type == 'pharmacy'){
				$total_new_users = $total_new_users->where('id', 'XX');
				$total_new_pharma_logistic_employee = $total_new_pharma_logistic_employee->where('id', 'XX');
			}
			/*if($user_type == 'seller' || $user_type == 'delivery_boy'){
				$total_new_pharmacies = $total_new_pharmacies->where('id', 'XX');
				$total_new_users = $total_new_users->where('id', 'XX');
				$total_new_pharma_logistic_employee = $total_new_pharma_logistic_employee->where(function ($query) use($user_type) {
	                $query->where('user_type', 'like', '%'.$user_type.'%');
	            });
			}
			if($user_type == 'customer'){
				$total_new_pharmacies = $total_new_pharmacies->where('id', 'XX');
				$total_new_pharma_logistic_employee = $total_new_pharma_logistic_employee->where('id', 'XX');
			}*/
		}
		$total_result = $total_new_users->get();
		$total = count($total_result);
		$total_page = ceil($total/$per_page);

		$user_detail = $total_new_users->orderby('created_at', 'DESC')->paginate($per_page,'','',$page);
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				switch ($user->user_type) {
					case 'pharmacy':
						$user->user_type_detail_url = 'pharmacy';
						break;
					case 'seller':
						$user->user_type_detail_url = 'seller';
						break;
					case 'delivery_boy':
						$user->user_type_detail_url = 'deliveryboy';
						break;
					case 'customer':
						$user->user_type_detail_url = 'user';
						break;
				}
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				$image_url = '';
				if($user->profile_image!=''){
					if (file_exists(storage_path('app/public/uploads/new_user/'.$user->profile_image))){
						$image_url = asset('storage/app/public/uploads/new_user/' . $user->profile_image);
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				$order_detail = new_order_history::select('new_order_history.id')
					->where('new_order_history.customer_id',$user->id)
					->where('new_order_history.order_status','complete');
					$total_complete_order = $order_detail->count();
				$html.='<tr>
					<td>'.$user->mobile_number.'</td>
					<td>'.$created_at.'</td>';
					/*$html.='<td><a class="btn btn-success waves-effect waves-light" href="'.url('/'.$user->user_type_detail_url.'/detail/'.$user->id).'" title="Detail"><i class="fa fa-eye"></i></a><a class="btn btn-info waves-effect waves-light" href="'.url('/user/edit/'.$user->id.'/'.$user->user_type).'" title="Edit user"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteUser" href="javascript:;" title="Delete user"><i class="fa fa-trash"></i></a>';
					if($user->is_active == 1){
                        $html.='<a href="'.url('/user/'.$user->id.'/inactive/'.$user->user_type).'" onClick="return confirm(\'Are you sure you want to inactive this?\');" rel="tooltip" title="InActive" class="btn btn-default btn-xs"><i class="fa fa-circle text-success"></i></a>';  
                    }else{ 
                    	$html.='<a href="'.url('/user/'.$user->id.'/active/'.$user->user_type).'" onClick="return confirm(\'Are you sure you want to active this?\');" rel="tooltip" title="Active" class="btn btn-default btn-xs"><i class="fa fa-circle text-danger"></i></a>';
                    }
					$html.='</td>';*/
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
