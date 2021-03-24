<?php

namespace App\Http\Controllers\logistic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\new_logistics;
use App\new_pharma_logistic_employee;
use Str;
class DeliveryboyController extends Controller
{
	public function __construct()
    {
		parent::__construct();
		$this->middleware('auth:new_logistics');
    }
    public function index()
    {
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		Auth::user()->user_type='logistic';

		$data = array();
		$data['page_title'] = 'Delivery boy';
		$data['page_condition'] = 'page_deliveryboy_logistic';
		$data['site_title'] = 'Delivery boy | ' . $this->data['site_title'];
        return view('logistic.deliveryboy.index', $data);
    }
    
	public function getlist()
    {
		$user_id = Auth::user()->id;
		$user_type = 'logistic';
		$html='';
		$pagination='';
		$total_summary='';
		
		$ord_field=(isset($_POST['ord_field']) && $_POST['ord_field']!='')?$_POST['ord_field']:'';
		$sortord=(isset($_POST['sortord']) && $_POST['sortord']!='')?$_POST['sortord']:'';
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$per_page=(isset($_POST['perpage']) && $_POST['perpage']!='')?$_POST['perpage']:10;
		$searchtxt=(isset($_POST['searchtxt']) && $_POST['searchtxt']!='')?$_POST['searchtxt']:'';

		$new_pharma_logistic_employee = new_pharma_logistic_employee::select('*')->where('pharma_logistic_id', $user_id)->where('user_type', 'delivery_boy')->where('parent_type','logistic')->where('is_active',1);

		if($searchtxt!=''){
			$new_pharma_logistic_employee= $new_pharma_logistic_employee->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
            });
		}

		$total_res = $new_pharma_logistic_employee->get();
		$total = count($total_res);
		$total_page = ceil($total/$per_page);
		//count total
		
		//get list
		$user_detail = $new_pharma_logistic_employee->paginate($per_page,'','',$page);
		
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				$created_at = ($user->created_at!='')?date('d-M-Y',strtotime($user->created_at)):'';
				$updated_at = ($user->updated_at!='')?date('d-M-Y',strtotime($user->updated_at)):'';
				$image_url = '';
				if($user->profile_image!=''){
					$destinationPath = base_path() . '/storage/app/public/uploads/new_delivery_boy/'.$user->profile_image;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/storage/app/public/uploads/new_delivery_boy/'.$user->profile_image;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}


				$order_assign = get_deliveryboy_total_order($user->id,'','');
				$html.='<tr>
					<td><img src="'.$image_url.'" width="50"/></td>
					<td>'.$user->name.'</td>
					<td>'.$user->email.'</td>
					<td>'.$user->mobile_number.'</td>
					<td>'.$order_assign.'</td>';
						
					$html.='<td><a class="btn btn-info waves-effect waves-light" href="'.url('logistic/deliveryboy/edit/'.$user->id).'" title="Edit user"><i class="fa fa-pencil"></i></a>
					<a onclick="delete_row('.$user->id.')" class="btn btn-danger waves-effect waves-light" href="javascript:;" title="Delete user"><i class="fa fa-trash"></i></a></td>';
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
						<a class="page-link" onclick="getuserlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getuserlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		$data = array();
		Auth::user()->user_type='logistic';

		$data['page_title'] = 'Create delivery boy';
		$data['page_condition'] = 'page_deliveryboy_create_logistic';
		$data['site_title'] = 'Create delivery boy | ' . $this->data['site_title'];
		//$data['pharmacy_list'] = User::where('user_type','pharmacy')->get();
		return view('logistic.deliveryboy.create', array_merge($this->data, $data));
	}
	public function store(Request $request){
		$validate = $request->validate([
			'name' => 'required',
			'email' => 'required|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,email|max:255',
			'mobile_number' => 'required|min:10|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,mobile_number',
			'address' => 'required',
			'profile_image' => 'image|max:1024',
			'password' => 'required|min:4|max:255',
			'confirm_password' => 'required|min:4|max:255|same:password',
			'address' => 'required',
			'block' => 'required',
			'street' => 'required',
			'pincode' => 'required',
		]);
		if($validate){
			$user = new new_pharma_logistic_employee();
			
			$user->profile_image = '';
			if ($request->hasFile('profile_image')) {
				$file1 = $request->file('profile_image');
				$fileName = time().'.'.$file1->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_delivery_boy';
				$file1->move($destinationPath, $fileName);
				$user->profile_image = $fileName;
			}

			$user->pharma_logistic_id = Auth::user()->id;
			$user->parent_type = 'logistic';
			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->address  = $request->address;
			$user->block  = $request->block;
			$user->street  = $request->street;
			$user->pincode  = $request->pincode;
			$hashed_random_password = Hash::make($request->password);
			$user->password = $hashed_random_password;
			$user->created_at = date('Y-m-d H:i:s');
			if($user->save()){
				return redirect(route('logistic.deliveryboy.index'))->with('success_message', trans('Added Successfully'));
			}
		}
	}
	public function edit($id)
    {
		$user_id = Auth::user()->id;
		Auth::user()->user_type='logistic';
		// if(Auth::user()->user_type!='logistic'){
		// 	return redirect(route('home'));
		// }
		$user_detail = User::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}
		$user_detail = new_pharma_logistic_employee::find($id);
		$data = array();
		$data['page_title'] = 'Edit delivery boy';
		$data['page_condition'] = 'page_deliveryboy_logistic';
		$data['user_detail'] = $user_detail;
		$data['site_title'] = 'Edit delivery boy | ' . $this->data['site_title'];
		//$data['pharmacy_list'] = User::where('user_type','pharmacy')->get();
		return view('logistic.deliveryboy.create', array_merge($this->data, $data));
	}
	public function update(Request $request, $id){
		$validate = $request->validate([
			'user_type' => 'required',
			'name' => 'required',
			'email' =>  [
				'required',
				Rule::unique('new_pharma_logistic_employee','email')->ignore($id),Rule::unique('new_users','email')->ignore($id),Rule::unique('new_pharmacies','email')->ignore($id),Rule::unique('new_logistics','email')->ignore($id)
			],
						'mobile_number' =>  [
				'required',
				Rule::unique('new_pharma_logistic_employee','mobile_number')->ignore($id),Rule::unique('new_users','mobile_number')->ignore($id),Rule::unique('new_pharmacies','mobile_number')->ignore($id),Rule::unique('new_logistics','mobile_number')->ignore($id)
			],
			'image' => 'image|max:1024',
			'address' => 'required',
			'block' => 'required',
			'street' => 'required',
			'pincode' => 'required',
		]);
		if($validate){
			$user = new_pharma_logistic_employee::find($id);
			if ($request->hasFile('profile_image')) {
				$file1 = $request->file('profile_image');
				$fileName = time().'.'.$file1->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_user';
				$file1->move($destinationPath, $fileName);
				$user->profile_image = $fileName;
				
			}else{
				$user->profile_image = (isset($request->profile_image))?$request->profile_image:$user->profile_image;
			}
			$user->user_type = $request->user_type;
			$user->pharma_logistic_id = Auth::user()->id;
			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->address  = $request->address;
			$user->block  = $request->block;
			$user->street  = $request->street;
			$user->pincode  = $request->pincode;
			$user->updated_at = date('Y-m-d H:i:s');
			if($user->save()){
				return redirect(route('logistic.deliveryboy.index'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}
	public function delete($id)
    {
		Auth::user()->user_type='logistic';
		$user = new_pharma_logistic_employee::find($id);
		$user->is_active=0;
		$user->save();
		return redirect(route('logistic.deliveryboy.index'))->with('success_message', trans('Deleted Successfully'));
	}
}
