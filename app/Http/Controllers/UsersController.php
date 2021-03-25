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

class UsersController extends Controller
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
		$user_city = new_address::select('city')->groupby('city')->get();
		$data['user_city'] = $user_city;
		$data['page_title'] = 'Users';
		$data['page_condition'] = 'page_users';
		$data['site_title'] = 'Users | ' . $this->data['site_title'];
        return view('users.index', $data);
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
		$total_new_users = new_users::select('id', 'name', 'email', 'mobile_number','is_active','profile_image', DB::raw("'' as city"), 'created_at', DB::raw("'customer' as user_type"));
		//$total_new_pharmacies = new_pharmacies::select('id', 'name', 'email', 'mobile_number','is_active', 'city', 'created_at', DB::raw("'pharmacy' as user_type"));
		//$total_new_pharma_logistic_employee = new_pharma_logistic_employee::select('id', 'name', 'email', 'mobile_number','is_active', DB::raw("'' as city"), 'created_at', 'user_type');

		if($searchtxt!=''){
			$total_new_users = $total_new_users->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
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
					<td><img src="'.$image_url.'" width="50"/></td>
					<td>'.$user->name.'</td>
					<td>'.$user->email.'</td>
					<td>'.$user->mobile_number.'</td>
					<td>'.$total_complete_order.'</td>
					<td>'.$created_at.'</td>';
					$html.='<td><a class="btn btn-success waves-effect waves-light" href="'.url('/'.$user->user_type_detail_url.'/detail/'.$user->id).'" title="Detail"><i class="fa fa-eye"></i></a><a class="btn btn-info waves-effect waves-light" href="'.url('/user/edit/'.$user->id.'/'.$user->user_type).'" title="Edit user"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteUser" href="javascript:;" title="Delete user"><i class="fa fa-trash"></i></a>';
					if($user->is_active == 1){
                        $html.='<a href="'.url('/user/'.$user->id.'/inactive/'.$user->user_type).'" onClick="return confirm(\'Are you sure you want to inactive this?\');" rel="tooltip" title="InActive" class="btn btn-default btn-xs"><i class="fa fa-circle text-success"></i></a>';  
                    }else{ 
                    	$html.='<a href="'.url('/user/'.$user->id.'/active/'.$user->user_type).'" onClick="return confirm(\'Are you sure you want to active this?\');" rel="tooltip" title="Active" class="btn btn-default btn-xs"><i class="fa fa-circle text-danger"></i></a>';
                    }
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
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$data = array();
		$data['page_title'] = 'Create User';
		$data['page_condition'] = 'page_user_create';
		$data['site_title'] = 'Create User | ' . $this->data['site_title'];
		$data['countries'] = new_countries::all();
		$data['pharmacies'] = new_pharmacies::where('is_active', 1)->get();
		$data['logistics'] = new_logistics::where('is_active', 1)->get();
		return view('users.create', array_merge($this->data, $data));
	}
	public function store(Request $request){
		$validation_arr = array(
			'user_type' => 'required',
			'name' => 'required',
			'email' => 'required|email|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,email|max:255',
			'mobile_number' => 'required|digits:10|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,mobile_number',
			'profile_image' => 'image|max:1024',
			'password' => 'required|min:8|max:255',
		);

		if($request->user_type=='seller' || $request->user_type=='delivery_boy'){
			$validation_arr['parentuser_id'] = 'required';
		}
		
		if($request->parentuser_id==''){
			$request->parentuser_id = 0;
		}

		$validate = $request->validate($validation_arr);
		if($validate){
			
			switch ($request->user_type) {
				case 'pharmacy':
					$put = 'uploads/new_pharmacy/';
					$user = new new_pharmacies();
					$user->country  = $request->country;
					$user->state  = $request->state;
					$user->city  = $request->city;
					$user->lat  = $request->lat;
					$user->lon  = $request->lon;
					$user->radius  = $request->radius;
					$user->discount  = $request->discount;
					$user->owner_name  = $request->owner_name;
					$user->start_time  = $request->start_time;
					$user->close_time  = $request->close_time;
					$user->address  = $request->address;
					$user->block  = $request->block;
					$user->street  = $request->street;
					$user->pincode  = $request->pincode;
				break;

				case 'seller':
					$put = 'uploads/new_seller/';
					$user = new new_pharma_logistic_employee();
					$user->user_type  = $request->user_type;
					$user->parent_type  = 'pharmacy';
					$user->pharma_logistic_id  = $request->parentuser_id;
					$user->address  = $request->address;
					$user->block  = $request->block;
					$user->street  = $request->street;
					$user->pincode  = $request->pincode;
				break;

				case 'delivery_boy':
					$put = 'uploads/new_delivery_boy/';
					$user = new new_pharma_logistic_employee();
					$user->user_type  = $request->user_type;
					$user->parent_type  = $request->parent_type;
					$user->pharma_logistic_id  = $request->parentuser_id;
					$user->address  = $request->address;
					$user->block  = $request->block;
					$user->street  = $request->street;
					$user->pincode  = $request->pincode;
				break;

				case 'customer':
					$put = 'uploads/new_user/';
					$user = new new_users();
				break;
			}

			if ($request->hasFile('profile_image')) {
				$image = $request->file('profile_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();

				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point

				Storage::disk('public')->put($put.$image_name, $img, 'public');
				$user->profile_image = $image_name;
			}

			if ($request->hasFile('license_image')) {
				$image = $request->file('license_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();

				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point

				Storage::disk('public')->put($put.'license/'.$image_name, $img, 'public');
				$user->license_image = $image_name;
			}

			if ($request->hasFile('pancard_image')) {
				$image = $request->file('pancard_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();

				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point

				Storage::disk('public')->put($put.'pancard/'.$image_name, $img, 'public');
				$user->pancard_image = $image_name;
			}

			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->password = Hash::make($request->password);
			$user->created_at = date('Y-m-d H:i:s');

			if($user->save()){
				if($request->user_type == 'pharmacy'){
					$auth_user = new User();
					$auth_user->user_id = $user->id;
					$auth_user->user_type = 'pharmacy';
					$auth_user->name = $request->name;
					$auth_user->email = $request->email;
					$auth_user->mobile_number = $request->mobile_number;
					$auth_user->password = $user->password;
					$auth_user->created_at = $user->created_at;
					$auth_user->save();
				}
				return redirect(route('user.index'))->with('success_message', trans('Added Successfully'));
			}
		}
	}
	public function edit($id, $user_type)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}

		switch ($user_type) {
			case 'pharmacy':
				$user_detail = new_pharmacies::where('id', $id)->first();
				$user_detail->user_type = 'pharmacy';
				$data['parent_user_type'] = '';
				break;
			case 'seller':
				$user_detail = new_pharma_logistic_employee::where('id', $id)->first();
				$user_detail->user_type = 'seller';
				$data['parent_user_type'] = 'pharmacy';
				break;
			case 'delivery_boy':
				$user_detail = new_pharma_logistic_employee::where('id', $id)->first();
				$user_detail->user_type = 'delivery_boy';
				$data['parent_user_type'] = 'logistic';
				break;
			case 'customer':
				$user_detail = new_users::find($id);
				$user_detail->user_type = 'customer';
				break;
		}

		$data = array();
		$data['countries'] = new_countries::all();
		$data['pharmacies'] = new_pharmacies::all();
		$data['logistics'] = new_logistics::all();
		$data['page_title'] = 'Edit User';
		$data['page_condition'] = 'page_client_create';
		$data['user_detail'] = $user_detail;
		$data['site_title'] = 'Edit User | ' . $this->data['site_title'];
		return view('users.create', array_merge($this->data, $data));
	}

	public function update(Request $request, $id){
		$validation_arr = array(
			'user_type' => 'required',
			'name' => 'required',
			'profile_image' => 'image|max:1024',
			//'password' => 'required|min:8|max:255',
		);

		switch ($request->user_type) {
			case 'pharmacy':
				$validation_arr['email'] = 'required|email|unique:new_users,email|unique:new_pharma_logistic_employee,email|unique:new_logistics,email|max:255|unique:new_pharmacies,email,'.$id;
				$validation_arr['mobile_number'] = 'required|digits:10|unique:new_users,mobile_number|unique:new_pharma_logistic_employee,mobile_number|unique:new_logistics,mobile_number|max:255|unique:new_pharmacies,mobile_number,'.$id;
				break;

			case 'customer':
				$validation_arr['email'] = 'required|email|unique:new_pharmacies,email|unique:new_pharma_logistic_employee,email|unique:new_logistics,email|max:255|unique:new_users,email,'.$id;
				$validation_arr['mobile_number'] = 'required|digits:10|unique:new_pharmacies,mobile_number|unique:new_pharma_logistic_employee,mobile_number|unique:new_logistics,mobile_number|max:255|unique:new_users,mobile_number,'.$id;
				break;

			case 'seller':
				$validation_arr['email'] = 'required|email|unique:new_users,email|unique:new_pharmacies,email|unique:new_logistics,email|max:255|unique:new_pharma_logistic_employee,email,'.$id;
				$validation_arr['mobile_number'] = 'required|digits:10|unique:new_users,mobile_number|unique:new_pharmacies,mobile_number|unique:new_logistics,mobile_number|max:255|unique:new_pharma_logistic_employee,mobile_number,'.$id;
				$validation_arr['parentuser_id'] = 'required';
				break;

			case 'delivery_boy':
				$validation_arr['email'] = 'required|email|unique:new_users,email|unique:new_pharmacies,email|unique:new_logistics,email|max:255|unique:new_pharma_logistic_employee,email,'.$id;
				$validation_arr['mobile_number'] = 'required|digits:10|unique:new_users,mobile_number|unique:new_pharmacies,mobile_number|unique:new_logistics,mobile_number|max:255|unique:new_pharma_logistic_employee,mobile_number,'.$id;
				$validation_arr['parentuser_id'] = 'required';
				break;
		}

		if($request->parentuser_id==''){
			$request->parentuser_id = 0;
		}

		$validate = $request->validate($validation_arr);
		if($validate){
			switch ($request->user_type) {
				case 'pharmacy':
					$put = 'uploads/new_pharmacy/';
					$storage_path = storage_path('app/public/uploads/new_pharmacy/');
					$user = new_pharmacies::where('id', $id)->first();
					$user->country  = $request->country;

					if(isset($request->state)){
						$user->state  = $request->state;
					}

					if(isset($request->city)){
						$user->city  = $request->city;
					}

					$user->lat  = $request->lat;
					$user->lon  = $request->lon;
					$user->radius  = $request->radius;
					$user->discount  = $request->discount;
					$user->owner_name  = $request->owner_name;
					$user->start_time  = $request->start_time;
					$user->close_time  = $request->close_time;
					$license_image = $user->license_image;
					$pancard_image = $user->pancard_image;
					$image_name = $user->profile_image;
					$user->address  = $request->address;
					$user->block  = $request->block;
					$user->street  = $request->street;
					$user->pincode  = $request->pincode;
					if($request->password != ''){
						$user->password = Hash::make($request->password);
					}

					$auth_user = User::where(['user_id'=> $id, 'user_type'=> 'pharmacy'])->first();
					if(isset($auth_user)){
						$auth_user->name = $request->name;
						$auth_user->email = $request->email;
						$auth_user->mobile_number = $request->mobile_number;
						$auth_user->password = $user->password;
						$auth_user->save();
					}
				break;

				case 'seller':
					$put = 'uploads/new_seller/';
					$storage_path = storage_path('app/public/uploads/new_seller/');
					$user = new_pharma_logistic_employee::where('id', $id)->first();
					$user->user_type  = $request->user_type;
					$user->parent_type  = 'pharmacy';
					$user->pharma_logistic_id  = $request->parentuser_id;
					$user->address  = $request->address;
					$user->block  = $request->block;
					$user->street  = $request->street;
					$user->pincode  = $request->pincode;
					$image_name = $user->profile_image;
					if($request->password != ''){
						$user->password = Hash::make($request->password);
					}
				break;

				case 'delivery_boy':
					$put = 'uploads/new_delivery_boy/';
					$storage_path = storage_path('app/public/uploads/new_delivery_boy/');
					$user = new_pharma_logistic_employee::where('id', $id)->first();
					$user->user_type  = $request->user_type;
					$user->parent_type  = $request->parent_type;
					$user->pharma_logistic_id  = $request->parentuser_id;
					$user->address  = $request->address;
					$user->block  = $request->block;
					$user->street  = $request->street;
					$user->pincode  = $request->pincode;
					$image_name = $user->profile_image;
					if($request->password != ''){
						$user->password = Hash::make($request->password);
					}
				break;

				case 'customer':
					$put = 'uploads/new_user/';
					$storage_path = storage_path('app/public/uploads/new_user/');
					$user = new_users::where('id', $id)->first();
					$image_name = $user->profile_image;
					if($request->password != ''){
						$user->password = Hash::make($request->password);
					}
				break;
			}

			if ($request->hasFile('profile_image')) {
				$filename = $storage_path . $image_name;
				if (File::exists($filename)) {
					File::delete($filename);
				}
				$image = $request->file('profile_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();
				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point
				Storage::disk('public')->put($put.$image_name, $img, 'public');
				$user->profile_image  = $image_name;
			}
			
			if ($request->hasFile('license_image')) {
				$filename = $storage_path . $license_image;
				if (File::exists($filename)) {
					File::delete($filename);
				}
				$image = $request->file('license_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();
				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point
				Storage::disk('public')->put($put.'license/'.$image_name, $img, 'public');
				$user->license_image  = $image_name;
			}

			if ($request->hasFile('pancard_image')) {
				$filename = $storage_path . $pancard_image;
				if (File::exists($filename)) {
					File::delete($filename);
				}
				$image = $request->file('pancard_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();
				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point
				Storage::disk('public')->put($put.'pancard/'.$image_name, $img, 'public');
				$user->pancard_image  = $image_name;
			}

			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			
			if($user->save()){
				return redirect(route('user.index'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}

	public function delete($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$user = new_users::where('id',$id)->first();
		$pharmacy = new_pharmacies::where('id',$id)->first();
		$seller_deliveryboy = new_pharma_logistic_employee::where('id',$id)->first();
		if($user){
			$user->is_active=0;
			$user->mobile_number='';
			$user->email='';
			$user->save();	
		}
		if($pharmacy){
			$pharmacy->is_active=0;
			$pharmacy->mobile_number='';
			$pharmacy->email='';
			$pharmacy->save();
			User::where(['user_id'=> $id, 'user_type'=> 'pharmacy'])->delete();
		}
		if($seller_deliveryboy){
			$seller_deliveryboy->is_active=0;
			$seller_deliveryboy->mobile_number='';
			$seller_deliveryboy->email='';
			$seller_deliveryboy->save();
		}
		return redirect(route('user.index'))->with('success_message', trans('Deleted Successfully'));
	}
	public function setActivate($id, $user_type){
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		switch ($user_type) {
			case 'pharmacy':
				$status_change = new_pharmacies::where('id',$id)->first();
				$status_change->is_active=1;
				$status_change->save();
				break;
			case 'seller':
				$status_change = new_pharma_logistic_employee::where('id',$id)->first();
				$status_change->is_active=1;
				$status_change->save();
				break;
			case 'delivery_boy':
				$status_change = new_pharma_logistic_employee::where('id',$id)->first();
				$status_change->is_active=1;
				$status_change->save();
				break;
			case 'customer':
				$status_change = new_users::where('id',$id)->first();
				$status_change->is_active=1;
				$status_change->save();	
				break;
		}
        return redirect(route('user.index'))->with('success_message', trans('Active Successfully'));
    }
    public function setInactivate($id, $user_type) {
    	if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
        switch ($user_type) {
			case 'pharmacy':
				$status_change = new_pharmacies::where('id',$id)->first();
				$status_change->is_active=0;
				$status_change->save();
				break;
			case 'seller':
				$status_change = new_pharma_logistic_employee::where('id',$id)->first();
				$status_change->is_active=0;
				$status_change->save();
				break;
			case 'delivery_boy':
				$status_change = new_pharma_logistic_employee::where('id',$id)->first();
				$status_change->is_active=0;
				$status_change->save();
				break;
			case 'customer':
				$status_change = new_users::where('id',$id)->first();
				$status_change->is_active=0;
				$status_change->save();	
				break;
		}
        return redirect(route('user.index'))->with('success_message', trans('InActive Successfully'));
    }
	public function delete_image(Request $request)
    {
		$id = auth()->user()->id;
		$user = User::find($id);
		
		if (!empty($user->profile_image)) {

            $filename = storage_path('app/public/uploads/users/' . $user->profile_image);
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		$user->profile_image = '';
        $user->save();

	}
	
	public function getstatelist(Request $request)
    {
		$states = new_states::where('country_id', $request->country)->get();
		return json_encode(['status' => 'success', 'states' => $states]);
	}

	public function getcitylist(Request $request)
    {
		$cities = new_cities::where('state_id', $request->state)->get();
		return json_encode(['status' => 'success', 'cities' => $cities]);
	}
	public function detail($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}
		$user = new_users::where('id',$id)->first();
		$data['user_detail'] = $user;
		$data['page_title'] = 'Detail User';
		$data['page_condition'] = 'page_user_customer_detail';
		$data['site_title'] = 'Detail User | ' . $this->data['site_title'];
		return view('users.detail', array_merge($this->data, $data));
	}
	public function getorderhistory(){
		$user_id = (isset($_REQUEST['user_id']) && $_REQUEST['user_id']!='')?$_REQUEST['user_id']:'';
		$per_page = (isset($_REQUEST['perpage']) && $_REQUEST['perpage']!='')?$_REQUEST['perpage']:10;
		$page=(isset($_POST['pageno']) && $_POST['pageno']!='')?$_POST['pageno']:1;
		$order_detail = new_orders::select('id','order_number','customer_id','pharmacy_id','logistic_user_id','deliveryboy_id','process_user_id','order_status','created_at');
		//$order_detail = $order_detail->where('order_status','!=','complete');
		$order_detail = $order_detail->where('customer_id',$user_id);
		$total_result = $order_detail->count();
		$total = $total_result;
		$total_page = ceil($total/$per_page);
		$order_detail = $order_detail->orderby('created_at', 'DESC')->paginate($per_page,'','',$page);
		$html = '';
		$pagination = '';
		$total_summary = '';
		if(count($order_detail)>0){
			foreach($order_detail as $order){
				$created_at = ($order->created_at!='')?date('d-M-Y  h:i a',strtotime($order->created_at)):'';

				$pharmacy_detail = new_pharmacies::select('name')->where('id','=',$order->pharmacy_id)->first();
				$pharmacy_detail_name = "";
				if($pharmacy_detail){
					$pharmacy_detail_name = $pharmacy_detail->name;
				}
				$customer_detail = new_users::select('name')->where('id',$order->customer_id)->first();
				$customer_detail_name = "";
				if($customer_detail){
					$customer_detail_name = $customer_detail->name;
				}
				$deliveryboy_details = new_pharma_logistic_employee::select('name')->where('id','=',$order->deliveryboy_id)->first();
				$deliveryboy_name = "";
				if($deliveryboy_details){
					$deliveryboy_name = $deliveryboy_details->name;
				}
				$logistics_details = new_logistics::select('name')->where('id','=',$order->logistic_user_id)->first();
				$logistic_name = "";
				if($logistics_details){
					$logistic_name = $logistics_details->name;
				}

				$html.='<tr><td><a href="'.url('/user/order_details/'.$order->id).'"</a><span>'.$order->order_number.'</span>';
				if($order->is_external_delivery){
					$html.=' <i class="ti-truck" style="color: orange;"></i> ';
				}
				$html.='</td>
				<td>'.$customer_detail_name.'</td>
				<td>'.$pharmacy_detail_name.'</td>
				<td>'.$deliveryboy_name.'</td>
				<td>'.$logistic_name.'</td>
				<td>'.$order->created_at.'</td>';
				
				if($order->order_status == 'payment_pending'){
					$html.= '<td><span class="label label-warning"> Payment pending</span></td>';
				}else if($order->order_status == 'reject'){
					$html.= '<td><span class="label label-danger"> Rejected</span></td>';
				}else if($order->order_status == 'new'){
					$html.= '<td><span class="label label-info"> New</span></td>';
				}else if($order->order_status == 'assign'){
					$html.= '<td><span class="label label-info"> Assign</span></td>';
				}else if($order->order_status == 'incomplete'){
					$html.= '<td><span class="label label-info"> Incomplete</span></td>';
				}else if($order->order_status == 'pickup'){
					$html.= '<td><span class="label label-success"> Pickup</span></td>';
				}else if($order->order_status == 'accept'){
					$html.= '<td><span class="label label-success"> Accept</span></td>';
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
						<a class="page-link" onclick="getorderfilter('.($page-1).')" href="javascript:;" tabindex="-1"><i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getorderfilter('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
	public function order_details($id)
    {
		$user_id = Auth::user()->user_id;

		if(new_order_history::find($id)){
			$order = new_order_history::select('new_order_history.*', 'prescription.name as prescription_name', 'prescription.image as prescription_image')->leftJoin('prescription', 'prescription.id', '=', 'new_order_history.prescription_id')->where('new_order_history.id', $id)->first();
			$order_detail = new_order_history::select('new_order_history.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname')
			->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_order_history.deliveryboy_id')
			->leftJoin('new_pharmacies as ua', 'ua.id', '=', 'new_order_history.pharmacy_id')
			->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_order_history.delivery_charges_id')
			->leftJoin('address_new', 'address_new.id', '=', 'new_order_history.address_id')
			->where('new_order_history.id',$id)->first();
		} else {
			$order = new_orders::select('new_orders.*', 'prescription.name as prescription_name', 'prescription.image as prescription_image')->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')->where('new_orders.id', $id)->first();
			$order_detail = new_orders::select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress','new_pharma_logistic_employee.name as deliveryboyname')
			->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
			->leftJoin('new_pharmacies as ua', 'ua.id', '=', 'new_orders.pharmacy_id')
			->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
			->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
			->where('new_orders.id',$id)->first();
		}
		
		$customer = new_users::where('id',$order->customer_id)->first();
		$address = '';
		if(get_name('address','address',$order->address_id)!=''){
			$address.= get_name('address','address',$order->address_id).', ';
		}
		if(get_name('address','address2',$order->address_id)!=''){
			$address.= get_name('address','address2',$order->address_id).', ';
		}
		if(get_name('address','city',$order->address_id)!=''){
			$address.= get_name('address','city',$order->address_id).', ';
		}
		if(get_name('address','state',$order->address_id)!=''){
			$address.= get_name('address','state',$order->address_id).', ';
		}
		if(get_name('address','country',$order->address_id)!=''){
			$address.= get_name('address','country',$order->address_id).', ';
		}
		if(get_name('address','pincode',$order->address_id)!=''){
			$address.= get_name('address','pincode',$order->address_id).', ';
		}
		$address = rtrim($address,', ');
		$delivered_images_file_array = array();
		$deliver_data = new_order_images::where(['order_id'=>$order->id,'image_type'=>'deliver'])->get();
        foreach ($deliver_data as $deliver) {
            if (!empty($deliver->image_name)) {
			    $filename = storage_path('app/public/uploads/deliver/' .  $deliver->image_name);
                if (File::exists($filename)) {
                	$delivered_images_file_array[] = asset('storage/app/public/uploads/deliver/' .  $deliver->image_name);
                }
            }
        }
        $pickup_images_file_array = array();
        $pickup_data = new_order_images::where(['order_id'=>$order->id,'image_type'=>'pickup'])->get();
        foreach ($pickup_data as $pickup) {
            $pickup_image = '';
            if (!empty($pickup->image_name)) {
                $filename = storage_path('app/public/uploads/pickup/' .  $pickup->image_name);
                if (File::exists($filename)) {
                	$pickup_images_file_array[] = asset('storage/app/public/uploads/pickup/' .  $pickup->image_name);
                }
            }
        }
		$data = array();
		$data['order'] = $order;
		$data['order_detail'] = $order_detail;
		$data['customer'] = new_users::where('id', $order->customer_id)->first();
		$data['address'] = $address;
		$data['pickup_images_file_array'] = $pickup_images_file_array;
		$data['delivered_images_file_array'] = $delivered_images_file_array;
		$data['page_title'] = 'Order detail';
		$data['page_condition'] = 'page_order_detail';
		$data['site_title'] = 'Order detail | ' . $this->data['site_title'];
		$data['reject_reason'] = Rejectreason::where('type', 'pharmacy')->get();
        return view('users.order_details', $data);
	}
	public function sendprescriptionotpsms(){
		$order_id = $_POST['order_id'];
		$order_detail = new_orders::select('new_orders.*','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress','ua.mobile_number as pharmacy_mobile_number','new_pharma_logistic_employee.name as deliveryboyname')
			->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
			->leftJoin('new_pharmacies as ua', 'ua.id', '=', 'new_orders.pharmacy_id')
			->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
			->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
			->where('new_orders.id',$order_id)->first();
		if($order_detail){
			$verification_code = rand(1111,9999);
			$mobile_number = $order_detail->pharmacy_mobile_number;
			$message = "Otp for admin request to view prescription " . $verification_code;
			$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HJENTP&mobileNos='".$mobile_number."'&message=" . urlencode($message);
			$sms = file_get_contents($api);
			
			$response['status'] = 'success';
			$response['otp'] = $verification_code;
			$response['message'] = 'OTP successfully sent to pharmacy';
		}else{
			$response['status'] = 'error';
			$response['otp'] = '';
			$response['message'] = 'Order detail not found';
		}
		echo json_encode($response);
	}
	public function verifyprescriptionotp(){
		$sent_otp = $_POST['sent_otp'];
		$otp = $_POST['otp'];
		$order_id = $_POST['order_id'];
		
		$order_detail = new_orders::select('new_orders.*','prescription.image as prescription_image','new_delivery_charges.delivery_type as delivery_type','new_delivery_charges.delivery_price as delivery_price', 'address_new.address as address','ua.address as pharmacyaddress','ua.mobile_number as pharmacy_mobile_number','new_pharma_logistic_employee.name as deliveryboyname')
			->leftJoin('prescription', 'prescription.id', '=', 'new_orders.prescription_id')
			->leftJoin('new_pharma_logistic_employee', 'new_pharma_logistic_employee.id', '=', 'new_orders.deliveryboy_id')
			->leftJoin('new_pharmacies as ua', 'ua.id', '=', 'new_orders.pharmacy_id')
			->leftJoin('new_delivery_charges', 'new_delivery_charges.id', '=', 'new_orders.delivery_charges_id')
			->leftJoin('address_new', 'address_new.id', '=', 'new_orders.address_id')
			->where('new_orders.id',$order_id)->first();
		if($order_detail){
			if($sent_otp == $otp){
				$image_url = url('/').'/uploads/placeholder.png';
				if (!empty($order_detail->prescription_image)) {
					if (file_exists(storage_path('app/public/uploads/prescription/'.$order_detail->prescription_image))){
						$image_url = asset('storage/app/public/uploads/prescription/' . $order_detail->prescription_image);
					}
				}
	
				$response['status'] = 'success';
				$response['data'] = '<div class="gallery"><img src="'.$image_url.'" style="width:500px;"></div>';
				$response['message'] = '';
			}else{
				$response['status'] = 'error';
				$response['data'] = '';
				$response['message'] = 'Invalid otp';
			}
		}else{
			$response['status'] = 'error';
			$response['data'] = '';
			$response['message'] = 'Order detail not found';
		}
		echo json_encode($response);
	}
}
