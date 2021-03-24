<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\report;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;
use Validator;
 
class ReportController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
        }
        
		$data = array();
		$data['page_title'] = 'Report';
		$data['page_condition'] = 'page_report';
		$data['site_title'] = 'Report | ' . $this->data['site_title'];
        return view('report.index', $data);
    }

	public function list()
    {
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
        $searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
        
		//get list
		$detail = report::select('report.*','new_users.name AS customer_name','new_order_history.order_number AS order_number','new_users.profile_image','report_images.image as report_image')
		->leftJoin('new_order_history', 'new_order_history.order_id', '=', 'report.order_id')
		->leftJoin('new_users', 'new_users.id', '=', 'report.user_id')
		->leftJoin('report_images', 'report_images.report_id', '=', 'report.id')
		->where('report.status', '=', 'new');

		if($searchtxt!=''){
			$detail= $detail->where(function ($query) use($searchtxt) {
				$query->where('new_users.name', 'like', '%'.$searchtxt.'%')
				->orWhere('report.description', 'like', '%'.$searchtxt.'%')
				->orWhere('report.mobile_number', 'like', '%'.$searchtxt.'%')
				->orWhere('new_order_history.order_number', 'like', '%'.$searchtxt.'%');
            });
		}
		$total = $detail->count();
        $total_page = ceil($total/$per_page);

		$detail = $detail->orderby('report.created_at','desc');
		$detail = $detail->paginate($per_page,'','',$page);
		//get list
		if(count($detail)>0){
			foreach($detail as $data){
				$created_at = ($data->created_at!='')?date('d-M-Y h:i a',strtotime($data->created_at)):'';
				$image_url = '';
				if($data->profile_image!=''){
					if (file_exists(storage_path('app/public/uploads/new_user/'.$data->profile_image))){
						$image_url = asset('storage/app/public/uploads/new_user/' . $data->profile_image);
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				
				$report_image_url = '';
				if($data->report_image!=''){
					if (file_exists(storage_path('app/public/uploads/new_user/'.$data->report_image))){
						$report_image_url = asset('storage/app/public/uploads/new_user/' . $data->report_image);
					}else{
						$report_image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$report_image_url = url('/').'/uploads/placeholder.png';
				}
				
				$html.='<tr>
					<td><img class="img-responsive" src="'.$image_url.'" width="50"/></td>
					<td><img class="img-responsive" src="'.$report_image_url.'" width="50"/></td>
					<td>'.$data->customer_name.'</td>
					<td>'.$data->order_number.'</td>
					<td>'.$data->mobile_number.'</td>
					<td>'.$data->description.'</td>
					<td>'.$created_at.'</td>
					<td><a onclick="resolve('.$data->id.', this)" class="btn btn-warning btn-custom waves-effect waves-light" href="javascript:;" title="Resolve Report" data-toggle="modal" data-target="#assign_modal">Resolve</a></td>';
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
						<a class="page-link" onclick="getreportlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getreportlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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

	public function resolve(Request $request)
	{
		$report = report::find($request->id);
		$report->status = 'resolve';
		$report->save();

		$response['status'] = 200;
		$response['message'] = 'Success';

        return response($response, 200);
    }
    public function resolveindex()
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
        }
        
		$data = array();
		$data['page_title'] = 'Report';
		$data['page_condition'] = 'page_resolveindex';
		$data['site_title'] = 'Report | ' . $this->data['site_title'];
        return view('report.resolveindex', $data);
    }

	public function resolvelist()
    {
        $user_id = Auth::user()->id;
        $user_type = Auth::user()->user_type;
        
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
        $searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
        
		//get list
		$detail = report::select('report.*','new_users.name AS customer_name','new_order_history.order_number AS order_number','new_users.profile_image')
		->leftJoin('new_order_history', 'new_order_history.order_id', '=', 'report.order_id')
		->leftJoin('new_users', 'new_users.id', '=', 'report.user_id')
		->where('report.status', '!=', 'new');

		if($searchtxt!=''){
			$detail= $detail->where(function ($query) use($searchtxt) {
				$query->where('new_users.name', 'like', '%'.$searchtxt.'%')
				->orWhere('report.description', 'like', '%'.$searchtxt.'%')
				->orWhere('report.mobile_number', 'like', '%'.$searchtxt.'%')
				->orWhere('new_order_history.order_number', 'like', '%'.$searchtxt.'%');
            });
		}
		$total = $detail->count();
        $total_page = ceil($total/$per_page);

		$detail = $detail->orderby('report.created_at','desc');
		$detail = $detail->paginate($per_page,'','',$page);
		//get list
		if(count($detail)>0){
			foreach($detail as $data){
				$created_at = ($data->created_at!='')?date('d-M-Y h:i a',strtotime($data->created_at)):'';
				$updated_at = ($data->updated_at!='')?date('d-M-Y h:i a',strtotime($data->updated_at)):'';
				$image_url = '';
				if($data->profile_image!=''){
					if (file_exists(storage_path('app/public/uploads/new_user/'.$data->profile_image))){
						$image_url = asset('storage/app/public/uploads/new_user/' . $data->profile_image);
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}

				$html.='<tr>
					<td><img class="img-responsive" src="'.$image_url.'" width="50"/></td>
					<td>'.$data->customer_name.'</td>
					<td>'.$data->order_number.'</td>
					<td>'.$data->mobile_number.'</td>
					<td>'.$data->description.'</td>
					<td>'.$created_at.'</td>
					<td>'.$updated_at.'</td>';
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
						<a class="page-link" onclick="getreportlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getreportlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
