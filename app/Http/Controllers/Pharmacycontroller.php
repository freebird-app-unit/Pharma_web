<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\users_doc;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Str;
use App\new_pharma_logistic_employee;
use App\new_users;
use App\new_pharmacies;
use App\new_countries;
use App\new_states;
use App\new_cities;
use App\new_order_history;
use Storage;
use Image;
use File;
use Illuminate\Validation\Rule;

class Pharmacycontroller extends Controller
{
    public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function getCountryList()
    {
         $countries = DB::table("new_countries")->pluck("name","id");
         return view('pharmacy.index',compact('countries'));
    }

    public function getStateList(Request $request)
    {
          $states = DB::table("new_states")
          ->where("country_id",$request->country_id)
          ->pluck("name","id");
          return response()->json($states);
    }

    public function getCityList(Request $request)
    {
        $cities = DB::table("new_cities")
        ->where("state_id",$request->state_id)
        ->pluck("name","id");
        return response()->json($cities);
     }
    public function index()
    {	
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$pharmacy_city = new_pharmacies::select('city')->groupby('city')->get();
		$data['pharmacy_city'] = $pharmacy_city;
		$data['page_title'] = 'Pharmacy';
		$data['page_condition'] = 'page_pharmacy';
		$data['site_title'] = 'Pharmacy | ' . $this->data['site_title'];
        return view('pharmacy.index', $data);
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
		$total_new_pharmacies = new_pharmacies::select('new_pharmacies.*')->where('is_delete','1');

		if($searchtxt!=''){
			$total_new_pharmacies= $total_new_pharmacies->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
						->orWhere('email', 'like', '%'.$searchtxt.'%')
						->orWhere('mobile_number', 'like', '%'.$searchtxt.'%')
						->orWhere('address', 'like', '%'.$searchtxt.'%');
            });
		}
		if($search_city!=''){
			$total_new_pharmacies= $total_new_pharmacies->where('city', $search_city);
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
					$destinationPath = base_path() . '/storage/app/public/uploads/new_pharmacy/'.$user->profile_image;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/storage/app/public/uploads/new_pharmacy/'.$user->profile_image;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				$order_detail = new_order_history::select('new_order_history.id')
					->where('new_order_history.pharmacy_id',$user->id)
					->where('new_order_history.order_status','complete');
				$total_complete_order = $order_detail->count();
				$total_seller = new_pharma_logistic_employee::select('new_pharma_logistic_employee.*')->where('user_type','seller')->where('pharma_logistic_id',$user->id)->count();
				$selected_package = 0;
				$html.='<tr>
					<td><img src="'.$image_url.'" width="50"/></td>
					<td>'.$user->name.'</td>
					<td>'.$user->mobile_number.'</td>
					<td>'.$user->address.'</td>
					<td>'.$total_complete_order.'</td>
					<td>'.$total_seller.'</td>
					<td>'.$selected_package.'</td>
					<td>'.$user->referral_code.'</td>
					<td>'.$user->remining_standard_paid_deliveries.'</td>';
					$html.='<td><a class="btn btn-success waves-effect waves-light" href="'.url('/pharmacy/detail/'.$user->id).'" title="Detail"><i class="fa fa-eye"></i></a><a class="btn btn-info waves-effect waves-light" href="'.url('/pharmacy/edit/'.$user->id).'" title="Edit user"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteUser" href="javascript:;" title="Delete user"><i class="fa fa-trash"></i></a>';
					if($user->is_active == 1){
                        $html.='<a href="'.url('/pharmacy/'.$user->id.'/inactive/').'" onClick="return confirm(\'Are you sure you want to inactive this?\');" rel="tooltip" title="InActive" class="btn btn-default btn-xs"><i class="fa fa-circle text-success"></i></a>';  
                    }else{ 
                    	$html.='<a href="'.url('/pharmacy/'.$user->id.'/active/').'" onClick="return confirm(\'Are you sure you want to active this?\');" rel="tooltip" title="Active" class="btn btn-default btn-xs"><i class="fa fa-circle text-danger"></i></a>';
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
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$data = array();
		$data['page_title'] = 'Create pharmacy';
		$data['page_condition'] = 'page_user_pharmacy';
		$data['countries'] = new_countries::all();

		$data['site_title'] = 'Create pharmacy | ' . $this->data['site_title'];
		return view('pharmacy.create', array_merge($this->data, $data));
	}
	
	public function detail($id)
    {
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/

		$user_detail = new_pharmacies::where('id',$id)->first();
		$data['user_detail'] = $user_detail;
		$data['page_title'] = 'Detail pharmacy';
		$data['page_condition'] = 'page_user_pharmacy_detail';
		$data['site_title'] = 'Detail pharmacy | ' . $this->data['site_title'];
		return view('pharmacy.detail', array_merge($this->data, $data));
	}

	public function store(Request $request){
		$validate = $request->validate([
			'name' => 'required',
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email|unique:new_pharmacies,email|max:255',
			'mobile_number' => 'required|digits:10|unique:new_pharmacies,mobile_number',
			'address' => 'required',
			'profile_image' => 'image|max:1024',
			'adharcard_image' => 'image|max:1024',
			'pancard_image' => 'image|max:1024',
			'druglicense_image' => 'image|max:1024',
			'lat' => 'required', 
			'lon' => 'required', 
			'start_time' => 'required', 
			'close_time' => 'required', 
			'radius' => 'required', 
			'pincode' => 'required', 
			'discount' => 'required', 
			'password' => 'required|min:4|max:255',
			'confirm_password' => 'required|min:4|max:255|same:password',
		]);

		if($validate){
			$user = new new_pharmacies();
			
			$users_doc = new users_doc();

			if ($request->hasFile('profile_image')) {
				$file2 = $request->file('profile_image');
				$fileName2 = time().'profile.'.$file2->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy';
				$file2->move($destinationPath, $fileName2);
				$user->profile_image = $fileName2;
			}else{
				$user->profile_image = (isset($request->profile_image))?$request->profile_image:'';
			}

			if ($request->hasFile('adharcard_image')) {
				$file3 = $request->file('adharcard_image');
				$fileName3 = time().'adharcard.'.$file3->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy/adharcard';
				$file3->move($destinationPath, $fileName3);
				$user->adharcard_image = $fileName3;
			}else{
				$user->adharcard_image = (isset($request->adharcard_image))?$request->adharcard_image:'';
			}

			if ($request->hasFile('pancard_image')) {
				$file4 = $request->file('pancard_image');
				$fileName4 = time().'pancard.'.$file4->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy/pancard';
				$file4->move($destinationPath, $fileName4);
				$user->pancard_image = $fileName4;	
			}else{
				$user->pancard_image = (isset($request->pancard_image))?$request->pancard_image:'';
			}

			if ($request->hasFile('druglicense_image')) {
				$file5 = $request->file('druglicense_image');
				$fileName5 = time().'druglicense.'.$file5->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy/druglicense';
				$file5->move($destinationPath, $fileName5);
				$user->druglicense_image = $fileName5;	
			}else{
				$user->druglicense_image = (isset($request->druglicense_image))?$request->druglicense_image:'';
			}
			$user->address = $request->address;
			if(!empty($request->street)){
				$user->street = $request->street;
			}
			if(!empty($request->block)){
				$user->block = $request->block;
			}
			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->first_name  = $request->first_name;
			$user->last_name  = $request->last_name;
			$hashed_random_password = Hash::make($request->password);
			$user->password = $hashed_random_password;
			$user->radius  = $request->radius;
			$user->lat = $request->lat;
			$user->lon = $request->lon;
			$user->start_time = $request->start_time;
			$user->close_time = $request->close_time;
			$user->pincode = $request->pincode;
			$user->discount = $request->discount;
			$c_data = new_countries::where('id',$request->country)->first();
			$user->country = $c_data->name;
			$s_data = new_states::where('id',$request->state)->first();
			$user->state = $s_data->name;
			$ci_data = new_cities::where('id',$request->city)->first();
			$user->city = $ci_data->name;
			$user->created_at = date('Y-m-d H:i:s');
			$user->save();

			$str1 = $user->first_name;
			$str2 = $user->last_name;
			$no=rand(1000,9999);
			$user->referral_code=ucfirst($str1[0]).ucfirst($str2[0]).$no.$user->id;
			$user->save();
			
			$auth_user = new User();
			$auth_user->user_id = $user->id;
			$auth_user->user_type = 'pharmacy';
			$auth_user->name = $request->name;
			$auth_user->email = $request->email;
			$auth_user->mobile_number = $request->mobile_number;
			$auth_user->password = $user->password;
			$auth_user->created_at = $user->created_at;
			$auth_user->save();
			return redirect(route('pharmacy.index'))->with('success_message', trans('Added Successfully'));
		}
	}

	public function edit($id)
    {

		$user_id = Auth::user()->id;
		
		/*if(Auth::user()->user_type!='pharmacy' && Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/

		$user_detail = new_pharmacies::where('id',$id)->first();

		if(!$user_detail){
			return abort(404);
		}
		if(isset($doc_detail)){
			$user_detail->license = $doc_detail->license;
			$user_detail->pancard = $doc_detail->pancard;
		}

		$data = array();
		$data['page_title'] = 'Edit pharmacy';
		$data['page_condition'] = 'page_client_pharmacy';
		$data['user_detail'] = $user_detail;
		$data['countries'] = new_countries::all();
		$data['site_title'] = 'Edit pharmacy | ' . $this->data['site_title'];
		return view('pharmacy.create', array_merge($this->data, $data));
	}
	public function update(Request $request, $id){
		$validate = $request->validate([
					'name' => 'required',
					'first_name' => 'required',
					'last_name' => 'required',
					'email' =>  ['required',Rule::unique('new_pharmacies','email')->ignore($id)],
					'mobile_number' =>  ['required',Rule::unique('new_pharmacies','mobile_number')->ignore($id)],
					'address' => 'required',
					'profile_image' => 'image|max:1024',
					'adharcard_image' => 'image|max:1024',
					'pancard_image' => 'image|max:1024',
					'druglicense_image' => 'image|max:1024',
					'lat' => 'required', 
					'lon' => 'required', 
					'start_time' => 'required', 
					'close_time' => 'required', 
					'radius' => 'required', 
					'pincode' => 'required', 
					'discount' => 'required', 
					'password' => 'required|min:4|max:255',
				]);
		if($validate){
			$user = new_pharmacies::find($id);
			if ($request->hasFile('profile_image')) {
				$file2 = $request->file('profile_image');
				$fileName2 = time().'profile.'.$file2->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy';
				$file2->move($destinationPath, $fileName2);
				$user->profile_image = $fileName2;
			}else{
				$user->profile_image = (isset($request->profile_image))?$request->profile_image:$user->profile_image;
			}

			if ($request->hasFile('adharcard_image')) {
				$file3 = $request->file('adharcard_image');
				$fileName3 = time().'adharcard.'.$file3->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy/adharcard';
				$file3->move($destinationPath, $fileName3);
				$user->adharcard_image = $fileName3;
			}else{
				$user->adharcard_image = (isset($request->adharcard_image))?$request->adharcard_image:$user->adharcard_image;
			}

			if ($request->hasFile('pancard_image')) {
				$file4 = $request->file('pancard_image');
				$fileName4 = time().'pancard.'.$file4->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy/pancard';
				$file4->move($destinationPath, $fileName4);
				$user->pancard_image = $fileName4;	
			}else{
				$user->pancard_image = (isset($request->pancard_image))?$request->pancard_image:$user->pancard_image;
			}

			if ($request->hasFile('druglicense_image')) {
				$file5 = $request->file('druglicense_image');
				$fileName5 = time().'druglicense.'.$file5->getClientOriginalExtension();  
				$destinationPath = 'storage/app/public/uploads/new_pharmacy/druglicense';
				$file5->move($destinationPath, $fileName5);
				$user->druglicense_image = $fileName5;	
			}else{
				$user->druglicense_image = (isset($request->druglicense_image))?$request->druglicense_image:$user->druglicense_image;
			}
			$user->address = $request->address;
			$user->street = $request->street;
			$user->block = $request->block;
			$user->name = $request->name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->first_name  = $request->first_name;
			$user->last_name  = $request->last_name;
			$user->radius  = $request->radius;
			$user->lat = $request->lat;
			$user->lon = $request->lon;
			$user->start_time = $request->start_time;
			$user->close_time = $request->close_time;
			$user->pincode = $request->pincode;
			$user->discount = $request->discount;
			if($user->password !== $request->password){
				$user->password = Hash::make($request->password);
			}
			$c_data = new_countries::where('id',$request->country)->first();
			$user->country = $c_data->name;
			if(!empty($request->state)){
				$s_data = new_states::where('id',$request->state)->first();
				$user->state = $s_data->name;
			}
			if(!empty($request->city)){
				$ci_data = new_cities::where('id',$request->city)->first();
				$user->city = $ci_data->name;
			}
			$user->created_at = date('Y-m-d H:i:s');
			$user->save();

			$auth_user = User::where(['user_id'=> $id, 'user_type'=> 'pharmacy'])->first();
			if(isset($auth_user)){
				$auth_user->name = $request->name;
				$auth_user->email = $request->email;
				$auth_user->mobile_number = $request->mobile_number;
				$auth_user->password = $user->password;
				$auth_user->save();
			}

			return redirect(route('pharmacy.index'))->with('success_message', trans('Updated Successfully'));
		}
	}
	public function delete($id)
    {
    	$user_id = Auth::user()->id;
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$user_detail = new_pharmacies::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}
		$pharmacy = new_pharmacies::find($id);
		$pharmacy->is_delete='0';
		$pharmacy->is_active=0;
		$pharmacy->mobile_number='';
		$pharmacy->email='';
		$pharmacy->save();
		
		$user = User::where('user_id',$id)->first();
		$user->mobile_number='';
		$user->email='';
		$user->save();

		return redirect(route('pharmacy.index'))->with('success_message', trans('Deleted Successfully'));
	}
	public function setActivate($id){
		$user_id = Auth::user()->id;
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$user_detail = new_pharmacies::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}
		$status_change = new_pharmacies::where('id',$id)->first();
		$status_change->is_active=1;
		$status_change->save();
        return redirect(route('pharmacy.index'))->with('success_message', trans('Active Successfully'));
    }
    public function setInactivate($id) {
    	$user_id = Auth::user()->id;
		/*if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}*/
		$user_detail = new_pharmacies::where('id',$id)->first();
		if(!$user_detail){
			return abort(404);
		}
		$status_change = new_pharmacies::where('id',$id)->first();
		$status_change->is_active=0;
		$status_change->save();
        return redirect(route('pharmacy.index'))->with('success_message', trans('InActive Successfully'));
    }
    public function delete_image_adhar(Request $request)
    {
		$id = $request->edit_id;
		$user = new_pharmacies::find($id);
		if (!empty($user->adharcard_image)) {

            $filename = 'storage/app/public/uploads/new_pharmacy/adharcard' . $user->adharcard_image;
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		$user->adharcard_image = '';
        $user->save();

	}
	public function delete_image_drug(Request $request)
    {
		$id = $request->edit_id;
		$user = new_pharmacies::find($id);
		if (!empty($user->druglicense_image)) {

            $filename = 'storage/app/public/uploads/new_pharmacy/druglicense' . $user->druglicense_image;
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		$user->druglicense_image = '';
        $user->save();

	}
	public function delete_image_pan(Request $request)
    {
		$id = $request->edit_id;
		$user = new_pharmacies::find($id);
		if (!empty($user->pancard_image)) {

            $filename = 'storage/app/public/uploads/new_pharmacy/pancard' . $user->pancard_image;
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		$user->pancard_image = '';
        $user->save();

	}
	public function delete_image_profile(Request $request)
    {
		$id = $request->edit_id;
		$user = new_pharmacies::find($id);
		if (!empty($user->profile_image)) {

            $filename = 'storage/app/public/uploads/new_pharmacy' . $user->profile_image;
                
            if (File::exists($filename)) {
                File::delete($filename);
            }
        }
		
		$user->profile_image = '';
        $user->save();

	}
}
