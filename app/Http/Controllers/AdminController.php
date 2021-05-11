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

class AdminController extends Controller
{
    public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		$data = array();
		$data['page_title'] = 'Admin';
		$data['page_condition'] = 'page_admin';
		$data['site_title'] = 'Admin | ' . $this->data['site_title'];
        return view('admin.index', $data);
    }
   public function getlist()
    {
		$user_id = Auth::user()->id;
		$html='';
		$pagination='';
		$total_summary='';
		$user_type = Auth::user()->user_type;
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';
		$search_city=(isset($_POST['search_city']) && $_POST['search_city']!='')?$_POST['search_city']:'';
		//count total

		// $total_res = DB::table('users')->where('user_type','pharmacy')->where('parentuser_id',$user_id);
		$total_new_pharmacies = User::select('admin_panel_creds.*')->where('user_type','admin')->where('email', '<>', '')->where('mobile_number', '<>', '');

		if($searchtxt!=''){
			$total_new_pharmacies= $total_new_pharmacies->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
						->orWhere('email', 'like', '%'.$searchtxt.'%')
						->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
            });
		}
		$total_new_pharmacies= $total_new_pharmacies->get();
		$total = count($total_new_pharmacies);
		$total_page = ceil($total/$per_page);
		//count total
		
		$user_detail = $total_new_pharmacies;
		
		//get list
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				$image_url = '';
				if($user->profile_image!=''){
					$destinationPath = base_path() . '/storage/app/public/uploads/new_users/'.$user->profile_image;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/storage/app/public/uploads/new_users/'.$user->profile_image;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				$html.='<tr>
					<td><img src="'.$image_url.'" width="50"/></td>
					<td>'.$user->name.'</td>
					<td>'.$user->mobile_number.'</td>
					<td>'.$user->email.'</td>
					<td>'.$created_at.'</td>';
					$html.='<td><a class="btn btn-info waves-effect waves-light" href="'.url('/admin/edit/'.$user->id).'" title="Edit Admin"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteUser" href="javascript:;" title="Delete admin"><i class="fa fa-trash"></i></a>';
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
						<a class="page-link" onclick="getadminlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getadminlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
    	if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Create Admin';
		$data['page_condition'] = 'page_admin';
		$data['site_title'] = 'Create Admin | ' . $this->data['site_title'];
		return view('admin.create', array_merge($this->data, $data));
	}

	public function store(Request $request){
		$validation_arr = array(
			'name' => 'required',
			'email' => 'required|email|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,email|max:255',
			'mobile_number' => 'required|digits:10|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,mobile_number',
			'profile_image' => 'image|max:1024',
			'password' => 'required|min:8|max:255',
		);

		
		$validate = $request->validate($validation_arr);
		if($validate){
			$user = new User();
			if ($request->hasFile('profile_image')) {
				$file1 = $request->file('profile_image');
				$fileName = time().'.'.$file1->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_users';
				$file1->move($destinationPath, $fileName);
				$user->profile_image = $fileName;
				
			}else{
				$user->profile_image = (isset($request->profile_image))?$request->profile_image:'';
			}

			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->password = Hash::make($request->password);
			$user->created_at = date('Y-m-d H:i:s');
			$user->save();
				return redirect(route('admin.index'))->with('success_message', trans('Added Successfully'));
		}
	}
	public function edit($id)
    {
		$user_id = Auth::user()->user_id;
		
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}

		$user_detail = User::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}

		$data = array();
		$data['page_title'] = 'Edit Admin';
		$data['page_condition'] = 'page_admin';
		$data['user_detail'] = $user_detail;
		$data['site_title'] = 'Edit Admin | ' . $this->data['site_title'];
		return view('admin.create', array_merge($this->data, $data));
	}

	public function update(Request $request, $id){
		$validate = $request->validate([
			'name' => 'required',
			'email' => 'required|email|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,email|max:255',
			'mobile_number' => 'required|digits:10|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,mobile_number',
			'profile_image' => 'image|max:1024',
			'password' => 'required|min:8|max:255',
		]);
		if($validate){
			$user = User::find($id);
			if ($request->hasFile('profile_image')) {
				$file1 = $request->file('profile_image');
				$fileName = time().'.'.$file1->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_users';
				$file1->move($destinationPath, $fileName);
				$user->profile_image = $fileName;
				
			}else{
				$user->profile_image = (isset($request->profile_image))?$request->profile_image:$user->profile_image;
			}
			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->password = Hash::make($request->password);
			$user->created_at = date('Y-m-d H:i:s');
			$user->save();
			
			if($user->save()){
				return redirect(route('admin.index'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}
	public function delete($id)
    {
		$user_id = Auth::user()->user_id;
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$user_detail = User::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}
		$user = User::find($id);
		$user->mobile_number='';
		$user->email='';
		$user->save();
		return redirect(route('admin.index'))->with('success_message', trans('Deleted Successfully'));
	}

	public function delete_image(Request $request)
    {
		$id = $request->edit_id;
		$user = User::find($id);
		if (!empty($user->profile_image)) {

            $filename = 'storage/app/public/uploads/new_users' . $user->profile_image;
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		$user->profile_image = '';
        $user->save();

	}
}
