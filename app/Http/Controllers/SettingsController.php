<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use App\Categories;
use App\User;
use App\Settings;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
class SettingsController extends Controller
{
	public function __construct()
    {
		parent::__construct();
        $this->middleware('auth');
    }
    public function index()
    {
		$setting_id = 1;
		$setting = Settings::find($setting_id);
		if(!$setting){
			$setting = new Settings();
			$setting->site_name = '';
			$setting->site_email = '';
			$setting->site_contact = '';
			$setting->site_address = '';
			$setting->site_logo = '';
			$setting->created_at = date('Y-m-d H:i:s');
			$setting->updated_at = date('Y-m-d H:i:s');
			$setting->save();
		}
		$data = array();
		$data['page_title'] = 'Settings';
		$data['page_condition'] = 'page_settings';
		$data['setting'] = $setting;
		$data['site_title'] = 'Settings | ' . $this->data['site_title'];
		return view('settings', array_merge($this->data, $data));
    }
	public function update(Request $request){
		$validate = $request->validate([
			'site_name' => 'required|max:255',
			'site_email' => 'required|max:255',
			'site_contact' => 'required|max:255',
			'site_address' => 'required',
			'site_logo' => 'image|max:1024',
		]);
		if($validate){
			$setting_id = 1;
			$setting = Settings::find($setting_id);
			
			if ($request->hasFile('site_logo')) {
				$file1 = $request->file('site_logo');
				$fileName = time().'.'.$file1->getClientOriginalExtension();  
				$destinationPath = base_path() . '/uploads/';
				$file1->move($destinationPath, $fileName);
				$setting->site_logo = $fileName;
				
			}else{
				$setting->site_logo = (isset($request->old_logo))?$request->old_logo:'';
			}
			
			$setting->site_name = $request->site_name;
			$setting->site_email = $request->site_email;
			$setting->site_contact = $request->site_contact;
			$setting->site_address = $request->site_address;
			$setting->updated_at = date('Y-m-d H:i:s');
			if($setting->save()){
				return redirect(route('settings'))->with('success_message', trans('Updated Successfully'));
			}
		}
	}
}
