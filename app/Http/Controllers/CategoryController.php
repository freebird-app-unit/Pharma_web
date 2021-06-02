<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\category;
use DB;
use Auth;
use Storage;
use Image;
use File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class CategoryController extends Controller
{
    public function index()
    {
		$data = array();
		$data['page_title'] = 'Category';
		$data['page_condition'] = 'page_category';
		$data['site_title'] = 'Category | ' . $this->data['site_title'];
        return view('category.index', $data);
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
		$total_new_users =  category::select('id','name','is_delete','created_at')->where('is_delete','1');

		if($searchtxt!=''){
			$total_new_users = $total_new_users->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%');
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
					<td>'.$user->name.'</td>
					<td>'.$created_at.'</td>';
					$html.='<td><a class="btn btn-info waves-effect waves-light" href="'.url('/category/edit/'.$user->id).'" title="Edit user"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteUser" href="javascript:;" title="Delete user"><i class="fa fa-trash"></i></a>';
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
	public function create()
    {
		$data = array();
		$data['page_title'] = 'Create Category';
		$data['page_condition'] = 'page_category';
		$data['site_title'] = 'Create Category | ' . $this->data['site_title'];
		return view('category.create', array_merge($this->data, $data));
	}
	public function store(Request $request){
		$validate = $request->validate([
			'name' => 'required',
		]);
		
		if($validate){
			$user = new category();
			$user->name = $request->name;
			
			if($user->save()){
				return redirect(route('category.index'))->with('success_message', trans('Added Successfully'));
			}
		}
	}
	public function edit($id)
    {
		$user_id = Auth::user()->user_id;
		
		$user_detail = category::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}

		$data = array();
		$data['page_title'] = 'Edit Category';
		$data['page_condition'] = 'page_category';
		$data['user_detail'] = $user_detail;
		$data['site_title'] = 'Edit Category | ' . $this->data['site_title'];
		return view('category.create', array_merge($this->data, $data));
	}
	public function update(Request $request, $id){
		$validate = $request->validate([
			'name' => 'required',
		]);
		if($validate){
			$user = category::find($id);
			$user->name = $request->name;
			if($user->save()){
				return redirect(route('category.index'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}

	public function delete($id)
    {
		$user_id = Auth::user()->user_id;
		$user_detail = category::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}
		$user = category::find($id);
		$user->is_delete='0';
		$user->save();
		return redirect(route('category.index'))->with('success_message', trans('Deleted Successfully'));
	}
}
