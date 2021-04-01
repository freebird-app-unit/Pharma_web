<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Str;
use Carbon;
use File;
use Storage;
use Image;
use Illuminate\Validation\Rule;

use App\User;
use App\geo_fencings;
use App\new_countries;
use App\new_logistics;

class LogisticsController extends Controller
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
		$logistic_city = new_logistics::select('city')->groupby('city')->get();
		$data['logistic_city'] = $logistic_city;
		$data['page_title'] = 'Logistic';
		$data['page_condition'] = 'page_logistic';
		$data['site_title'] = 'Logistic | ' . $this->data['site_title'];
        return view('logistics.index', $data);
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

		$total_new_logistiics = new_logistics::select('id', 'name', 'email', 'owner_name', 'profile_image', 'mobile_number', 'address', 'pincode', 'created_at','is_active','total_deposit','current_deposit');

		if($searchtxt!=''){
			$total_new_logistiics = $total_new_logistiics->where(function ($query) use($searchtxt) {
                $query->where('name', 'like', '%'.$searchtxt.'%')
				->orWhere('email', 'like', '%'.$searchtxt.'%')
				->orWhere('mobile_number', 'like', '%'.$searchtxt.'%');
			});
		}
		if($search_city!=''){
			$total_new_logistiics= $total_new_logistiics->where('city', $search_city);
		}
		$total_res= $total_new_logistiics->get();
		$total = count($total_res);
		$total_page = ceil($total/$per_page);

		DB::connection()->enableQueryLog();
		$user_detail = $total_new_logistiics->paginate($per_page,'','',$page);
		$queries = DB::getQueryLog();
		//get list
		if(count($user_detail)>0){
			foreach($user_detail as $user){
				$image_url = '';
				if($user->profile_image!=''){
					$destinationPath = base_path() . '/storage/app/public/uploads/new_logistic/'.$user->profile_image;
					if(file_exists($destinationPath)){
						$image_url = url('/').'/storage/app/public/uploads/new_logistic/'.$user->profile_image;
					}else{
						$image_url = url('/').'/uploads/placeholder.png';
					}
				}else{
					$image_url = url('/').'/uploads/placeholder.png';
				}
				$html.='<tr>
					<td><img src="'.$image_url.'" width="50"/></td>
					<td>'.$user->name.'</td>
					<td>'.$user->owner_name.'</td>
					<td>'.$user->email.'</td>
					<td>'.$user->mobile_number.'</td>
					<td>'.$user->address.'</td>
					<td>'.$user->total_deposit.'</td>
					<td>'.$user->current_deposit.'</td>';
					$html.='<td><a class="btn btn-success waves-effect waves-light" href="'.url('/logistic/detail/'.$user->id).'" title="Detail"><i class="fa fa-eye"></i></a><a class="btn btn-info waves-effect waves-light" href="'.url('/logistic/edit/'.$user->id).'" title="Edit user"><i class="fa fa-pencil"></i></a><a data-toggle="modal" href="#delete_modal" data-id="'.$user->id.'" class="btn btn-danger waves-effect waves-light deleteUser" href="javascript:;" title="Delete user"><i class="fa fa-trash"></i></a>';
					if($user->is_active == 1){
                        $html.='<a href="'.url('/logistic/'.$user->id.'/inactive/').'" onClick="return confirm(\'Are you sure you want to inactive this?\');" rel="tooltip" title="InActive" class="btn btn-default btn-xs"><i class="fa fa-circle text-success"></i></a>';  
                    }else{ 
                    	$html.='<a href="'.url('/logistic/'.$user->id.'/active/').'" onClick="return confirm(\'Are you sure you want to active this?\');" rel="tooltip" title="Active" class="btn btn-default btn-xs"><i class="fa fa-circle text-danger"></i></a>';
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
						<a class="page-link" onclick="getlogisticlist('.($page-1).')" href="javascript:;" tabindex="-1"> <i class="fa fa-angle-left"></i></a>
					</li>
					<li class="page-item '.$next.'">
						<a class="page-link" onclick="getlogisticlist('.($page+1).')" href="javascript:;"><i class="fa fa-angle-right"></i></a>
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
		$data['page_title'] = 'Create Logistic';
		$data['countries'] = new_countries::all();
		$data['page_condition'] = 'page_logistic_create';
		$data['site_title'] = 'Create Logistic | ' . $this->data['site_title'];
		return view('logistics.create', array_merge($this->data, $data));
    }
    
    public function store(Request $request){
		$validate = $request->validate([
			'name' => 'required',
			'owner_name' => 'required',
			'email' => 'required|email|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,email|max:255',
			'mobile_number' => 'required|digits:10|unique:new_users|unique:new_pharma_logistic_employee|unique:new_pharmacies|unique:new_logistics,mobile_number',
			'profile_image' => 'image|max:1024',
			'password' => 'required|min:4|max:255',
			'address'  => 'required',
			'country'  => 'required',
			'state'  => 'required',
			'city'  => 'required',
			'street'  => 'required',
			'block'  => 'required',
			'confirm_password' => 'required|min:4|max:255|same:password',
			'lat' => 'required',
			'lon' => 'required',
			'start_time' => 'required',
			'close_time' => 'required',
			'pincode' => 'required'
		]);

		if($validate){
			$user = new new_logistics();
			
			if ($request->hasFile('profile_image')) {
				$image = $request->file('profile_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();

				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point

				Storage::disk('public')->put('uploads/new_logistic/'.$image_name, $img, 'public');
				$user->profile_image = $image_name;
			}

			$user->name = $request->name;
			$user->owner_name = $request->owner_name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$hashed_random_password = Hash::make($request->password);
			$user->password = $hashed_random_password;
			$user->address = $request->address;
			$user->country = $request->country;
			$user->state = $request->state;
			$user->city = $request->city;
			$user->street = $request->street;
			$user->block = $request->block;
			$user->lat = $request->lat;
			$user->lon = $request->lon;
			$user->start_time = $request->start_time;
			$user->close_time = $request->close_time;
			$user->pincode = $request->pincode;
			$user->created_at = date('Y-m-d H:i:s');
	
			if($user->save()){
				$auth_user = new User();
				$auth_user->user_id = $user->id;
				$auth_user->user_type = 'logistic';
				$auth_user->name = $request->name;
				$auth_user->email = $request->email;
				$auth_user->mobile_number = $request->mobile_number;
				$auth_user->password = $user->password;
				$auth_user->created_at = $user->created_at;
				$auth_user->save();

				if((isset($request->area))){
					$geofencings = json_decode($request->area);
					if(count($geofencings) > 0){
						foreach ($geofencings as $key => $value) { 
							$geo_fencings = new geo_fencings();
							$geo_fencings->user_id = $user->id;
							$geo_fencings->type = $value->type;

							if($value->type == 'circle'){
								$geo_fencings->radius = $value->radius;
								$geo_fencings->coordinates = '('.$value->center.')';
							} else if ($value->type == 'polygon'){
								$coords = '';
								foreach ($value->coordinates as $value){
									$coords .=  '('.$value->lat.', '.$value->lng.'), ';
								}
								$geo_fencings->coordinates = $coords;
							} else if ($value->type == 'rectangle'){
								$coords = '';
								foreach ($value->coordinates as $value){
									$coords .=  '('.$value->lat.', '.$value->lng.'), ';
								}
								$geo_fencings->coordinates = $coords;
							}
							$geo_fencings->created_at = Carbon::now();
							$geo_fencings->save();
						}
					}
				}
				return redirect(route('logistic.index'))->with('success_message', trans('Added Successfully'));
			}
		}
    }
    
    public function edit($id)
    {
		$user_id = Auth::user()->id;
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}

		$user_detail = new_logistics::where('id',$id)->first();
		
		if(!$user_detail){
			return abort(404);
		}

		$user_detail->geo_fencings = geo_fencings::where('user_id',$id)->get();
		$data = array();
		$data['page_title'] = 'Edit Logistic';
		$data['countries'] = new_countries::all();
		$data['page_condition'] = 'page_logistic_edit';
		$data['user_detail'] = $user_detail;
		$data['site_title'] = 'Edit Logistic | ' . $this->data['site_title'];
		return view('logistics.create', array_merge($this->data, $data));
	}

	public function update(Request $request, $id){
		$validate = $request->validate([
			'name' => 'required|max:255',
			'email' =>  ['required',Rule::unique('new_pharma_logistic_employee','email')->ignore($id),Rule::unique('new_users','email')->ignore($id),Rule::unique('new_pharmacies','email')->ignore($id),Rule::unique('new_logistics','email')->ignore($id)],
			//'email' => 'required|email|unique:new_pharmacies,email|unique:new_pharma_logistic_employee,email|unique:new_logistics,email|max:255|unique:new_users,email,'.$id,
			'address'  => 'required',
			'owner_name' => 'required|max:255',
			'mobile_number' =>  ['required',Rule::unique('new_pharma_logistic_employee','mobile_number')->ignore($id),Rule::unique('new_users','mobile_number')->ignore($id),Rule::unique('new_pharmacies','mobile_number')->ignore($id),Rule::unique('new_logistics','mobile_number')->ignore($id)],
			//'mobile_number' => 'required',
			'profile_image' => 'image|max:1024',
			'address'  => 'required',
			'country'  => 'required',
			'street'  => 'required',
			'block'  => 'required',
			'lat' => 'required',
			'lon' => 'required',
			'start_time' => 'required',
			'close_time' => 'required',
			'pincode' => 'required'
		]);

		if($validate){
			$user = new_logistics::find($id);
			
			if ($request->hasFile('profile_image')) {
				$image_name = $user->profile_image;
				$filename = storage_path('app/public/uploads/new_logistic/') . $image_name;
				if (File::exists($filename)) {
					File::delete($filename);
				}
				$image = $request->file('profile_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();
				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point
				Storage::disk('public')->put('uploads/new_logistic/'.$image_name, $img, 'public');
				$user->profile_image  = $image_name;
			}

			$user->name = $request->name;
			$user->owner_name = $request->owner_name;
			$user->email = $request->email;
			$user->mobile_number  = $request->mobile_number;
			$user->address = $request->address;
			$user->country = $request->country;
			$user->city = isset($request->city)?($request->city):$user->city;
			$user->state = isset($request->state)?($request->state):$user->state;
			$user->street = $request->street;
			$user->block = $request->block;
			$user->lat = $request->lat;
			$user->lon = $request->lon;
			$user->start_time = ($request->start_time);
			$user->close_time = ($request->close_time);
			$user->pincode = $request->pincode;

			if($user->save()){

				$auth_user = User::where(['user_id'=> $id, 'user_type'=> 'logistic'])->first();
				if(isset($auth_user)){
					$auth_user->name = $request->name;
					$auth_user->email = $request->email;
					$auth_user->mobile_number = $request->mobile_number;
					$auth_user->password = $user->password;
					$auth_user->save();
				}

				
				// if((isset($request->area))){
				// 	$geofencings = json_decode($request->area);
				// 	if(count($geofencings) > 0){
				// 		foreach ($geofencings as $key => $value) { 
				// 			$geo_fencings = new geo_fencings();
				// 			$geo_fencings->user_id = $user->id;
				// 			$geo_fencings->type = $value->type;

				// 			if($value->type == 'circle'){
				// 				$geo_fencings->radius = $value->radius;
				// 				$geo_fencings->coordinates = '('.$value->center.')';
				// 			} else if ($value->type == 'polygon'){
				// 				$coords = '';
				// 				foreach ($value->coordinates as $value){
				// 					$coords .=  '('.$value->lat.', '.$value->lng.'), ';
				// 				}
				// 				$geo_fencings->coordinates = $coords;
				// 			} else if ($value->type == 'rectangle'){
				// 				$coords = '';
				// 				foreach ($value->coordinates as $value){
				// 					$coords .=  '('.$value->lat.', '.$value->lng.'), ';
				// 				}
				// 				$geo_fencings->coordinates = $coords;
				// 			}
				// 			$geo_fencings->created_at = Carbon::now();
				// 			$geo_fencings->save();
				// 		}
				// 	}
				// }
				return redirect(route('logistic.index'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}

	public function detail($id)
    {
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}

		$user_detail = new_logistics::where('id',$id)->first();

		$data['user_detail'] = $user_detail;
		$data['page_title'] = 'Detail logistic boy';
		$data['page_condition'] = 'page_logistic_detail';
		$data['site_title'] = 'Detail logistic boy | ' . $this->data['site_title'];
		return view('logistics.detail', array_merge($this->data, $data));
	}

	public function delete($id)
    {
		$user_id = Auth::user()->id;
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}

		$user = new_logistics::where('id', $id)->first();
		if(!$user){
			return abort(404);
		}

		geo_fencings::where('user_id', $id)->update(array('is_active' => 0));
		User::where(['user_id'=> $id, 'user_type'=> 'logistic'])->delete();

		$user->is_active=0;
		$user->mobile_number='';
		$user->email='';
		$user->save();
		return redirect(route('logistic.index'))->with('success_message', trans('Deleted Successfully'));
	}
	public function setActivate($id){
		$user_id = Auth::user()->id;
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}

		$user = new_logistics::where('id', $id)->first();
		if(!$user){
			return abort(404);
		}
		$status_change = new_logistics::where('id',$id)->first();
		$status_change->is_active=1;
		$status_change->save();
        return redirect(route('logistic.index'))->with('success_message', trans('Active Successfully'));
    }
    public function setInactivate($id) {
    	$user_id = Auth::user()->id;
		if(Auth::user()->user_type!='admin'){
			return redirect(route('home'));
		}

		$user = new_logistics::where('id', $id)->first();
		if(!$user){
			return abort(404);
		}
		$status_change = new_logistics::where('id',$id)->first();
		$status_change->is_active=0;
		$status_change->save();
        return redirect(route('logistic.index'))->with('success_message', trans('InActive Successfully'));
    }
}
