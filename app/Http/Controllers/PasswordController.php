<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Hash;
use Mail;

class PasswordController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		//parent::__construct();
		$this->data['site_title'] = (get_settings('site_name')!='')?get_settings('site_name'):'Pharma';
		$this->data['site_email'] = (get_settings('site_email')!='')?get_settings('site_email'):'';
		$this->data['site_contact'] = (get_settings('site_contact')!='')?get_settings('site_contact'):'';
		$this->data['site_address'] = (get_settings('site_address')!='')?get_settings('site_address'):'';
		$this->data['site_logo'] = (get_settings('site_logo')!='')?get_settings('site_logo'):'';
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function resetpassword()
    {
		
		$data['page_condition'] = 'page_resetpassword';
		$data['site_title'] = 'Dashboard | Pharma';
        return view('auth.passwords.email',array_merge($data));
    }
	public function sendotp(Request $request){
		$validatedData = $request->validate([
			'email_mobile' => 'required',
		]);
		if($validatedData){
			$users = DB::table('admin_panel_creds')->where('email', $request->email_mobile)->orWhere('mobile_number', $request->email_mobile)->first();
			if($users){
				$otp = rand(111111,999999);
				$user = User::find($users->id);
				$user->otp = $otp;
				$user->save();
				
				$data['to'] = $user->email;
				$data['otp'] = $otp;
				$data['name'] = $user->name;
				Mail::send('email.sendotp', $data, function($message) use ($data){
					$message->from($this->data['site_email'],$this->data['site_title']);
					$message->subject("Password reset otp");
					$message->to($data['to']);
				});

				return redirect('/otpverification/'.base64_encode($user->id))->with('data',$request->email_mobile);
			}else{
				return redirect()->back()->with('error','email or mobile not found');
			}
		}
	}
	public function otpverification($slug){
		$id = base64_decode($slug);
		$email_mobile = \Session::get('data');
		$data['id'] = $id;
		return view('auth.passwords.otpverify',array_merge($data));
	}
	public function otpverify(Request $request){
		$validatedData = $request->validate([
			'otp' => 'required',
		]);
		if($validatedData){
			$users = DB::table('admin_panel_creds')->where('id', $request->id)->Where('otp', $request->otp)->first();
			if($users){
				return redirect('/passwordreset/'.base64_encode($users->id));
			}else{
				return redirect()->back()->with('error','Invalid otp');
			}
		}
	}
	public function passwordreset($slug){
		$id = base64_decode($slug);
		$email_mobile = \Session::get('data');
		$data['id'] = $id;
		return view('auth.passwords.passwordreset',array_merge($data));
	}
	public function savepassword(Request $request){
		$validatedData = $request->validate([
			'password' => 'required|min:4|max:255',
			'confirm_password' => 'required|min:4|max:255|same:password',
		]);
		if($validatedData){
			$users = User::find($request->id);
			$users->password = Hash::make($request->password);
			$users->save();
			return redirect('/login')->with('success','Your password successfully reset!');
		}
	}
}
