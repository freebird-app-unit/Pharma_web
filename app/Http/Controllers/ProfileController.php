<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Categories;
use App\User;
use App\Clinic;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
use Storage;
use Image;
use File;

use App\new_pharmacies;
use App\new_users;
use	App\new_pharma_logistic_employee;
use	App\new_logistics;

class ProfileController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		$user = auth()->user();
		$user = User::find($user->id);
		$data = array();
		$data['page_title'] = 'My profile';
		$data['page_condition'] = 'page_myprofile';
		if($user->user_type == 'pharmacy'){
			$pharmacy = new_pharmacies::find($user->user_id); 
			$user->discount = $pharmacy->discount; 
		}
		$data['user_detail'] = $user;
		$data['site_title'] = 'My profile | ' . $this->data['site_title'];
		return view('profile', array_merge($this->data, $data));
    }
	public function update(Request $request){
		$user = auth()->user();
		$validate = $request->validate([
			'name' => 'required|max:255',
		]);

		switch ($user->user_type) {
			case 'pharmacy':
				$validation_arr['mobile_number'] = 'required|unique:new_users,mobile_number|unique:new_pharma_logistic_employee,mobile_number|unique:new_logistics,mobile_number|max:255|unique:new_pharmacies,mobile_number,'.$user->user_id;
				break;

			case 'logistic':
				$validation_arr['mobile_number'] = 'required|unique:new_pharmacies,mobile_number|unique:new_pharma_logistic_employee,mobile_number|unique:new_users,mobile_number|max:255|unique:new_logistics,mobile_number,'.$user->user_id;
				break;
		}


		if($validate){
			
			$image_name = $request->hidden_image;

			if ($request->hasFile('profile_image')) {
				
				$filename = storage_path('app/public/uploads/users/' . $image_name);
				
				if (File::exists($filename)) {
					File::delete($filename);
				}

				$image      = $request->file('profile_image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();

				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point

				Storage::disk('public')->put('uploads/users/'.$image_name, $img, 'public');
			}
		
			$user = User::find($user->id);
			$user->name = $request->name;
			if($user->user_type == 'pharmacy'){
				$pharmacy = new_pharmacies::find($user->user_id); 
				$pharmacy->discount = $request->discount; 
				$pharmacy->mobile_number = $request->mobile_number; 
				$pharmacy->save();
			} else if($user->user_type == 'logistic') {
				$user = new_logistics::find($user->user_id);
				$user->mobile_number = $request->mobile_number; 
				$user->save();
			}

			// $user->profile_image = $image_name;
			$user->mobile_number = $request->mobile_number;
			$user->updated_at = date('Y-m-d H:i:s');
			if($user->save()){
				return redirect(route('profile'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}
	public function changeemail()
    {
		$user = auth()->user();
		$user = User::find($user->id);
		$data = array();
		$data['page_title'] = 'Change Email';
		$data['page_condition'] = 'page_myprofile';
		$data['user_detail'] = $user;
		$data['site_title'] = 'Change Email | ' . $this->data['site_title'];
		return view('changeemail', array_merge($this->data, $data));
    }
	public function updateemail(Request $request){
		$user = auth()->user();
		$validate = $request->validate([
			'email' => 'required',
			'password' => 'required',
		]);
		$user_data = User::find($user->id);
		if(!Hash::check($request->password, $user_data->password)){
			$request->session()->flash('error', 'Current password does not match');
			return redirect()->route('changeemail');
		}
		if($validate){
			$user = User::find($user->id);
			$user->email = $request->email;
			$user->updated_at = date('Y-m-d H:i:s');
			if($user->save()){
				$clinic = DB::table('clinic')->where('user_id','=',$user->id)->first();
				if($clinic){
					$clinic_ = Clinic::find($clinic->id);
					$clinic_->clinic_email = $request->email;
					$clinic_->save();
				}
				return redirect(route('changeemail'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}
	public function changepassword()
    {
		$user = auth()->user();
		$user = User::find($user->id);
		$data = array();
		$data['page_title'] = 'Change password';
		$data['page_condition'] = 'page_profile';
		$data['user_detail'] = $user;
		$data['site_title'] = 'Change password | ' . $this->data['site_title'];
		return view('changepassword', array_merge($this->data, $data));
	}
	
	public function updatepassword(Request $request){
		$user = auth()->user();

		$validate = $request->validate([
			'current_password' => 'required',
			'new_password' => 'required',
			'confirm_new_password' => 'required|same:new_password',
		]);

		switch ($user->user_type) {
			case 'pharmacy':
				$user = new_pharmacies::find($user->id);
				break;

			case 'logistic':
				$user = new_logistics::find($user->id);
				break;
			
			default:
				$user = User::find($user->id);
				break;
		}
		
		
		if(!Hash::check($request->current_password, $user->password)){
			$request->session()->flash('error', 'Current password does not match');
			return redirect()->route('changepassword');
		}

		if($validate){
			// $user = User::find($user->id);
			$hashed_random_password = Hash::make($request->new_password);
			$user->password = $hashed_random_password;
			$user->updated_at = date('Y-m-d H:i:s');
			if($user->save()){
				return redirect(route('changepassword'))->with('success_message', trans('Password Successfully changed'));
			}
		}
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
}
