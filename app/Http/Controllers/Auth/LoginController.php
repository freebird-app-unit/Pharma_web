<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\User;
use DB;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
	public function username()
	{
		return 'mobile_number';
	}
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // public function authenticate(Request $request)
    // {
    //     // Returned validated fields also contain the csrf token,
    //     // therefore, we pick only email and password.
    //     $attributes = $requestFields->only(['email', 'password']);
    //     if (Auth::attempt($attributes)) {
    //         return redirect()->route('dashboard');
    //     }
    // }

    // public function attemptLogin(Request $request)
    // {
    //     // Returned validated fields also contain the csrf token,
    //     // therefore, we pick only email and password.
    //     switch ($request->user_type) {
    //         case 'new_pharmacies':
    //             $guard = 'new_pharmacies';
    //             break;
            
    //         default:
    //             $guard = 'web';
    //             break;
    //     }
    
    //     $guard_result = Auth::guard($guard)->attempt(['email' => $request->email, 'password' => $request->password], $request->filled('remember'));

    //     if($guard_result){
    //         switch ($request->user_type) {
    //             case 'new_pharmacies':
    //                 Auth::user()->user_type = 'pharmacy';
    //                 break;
                
    //             default:
    //                 Auth::user()->user_type = 'admin';
    //                 break;
    //         }
    //     }

    //     return  $guard_result;

    // }

     protected function authenticated(Request $request, $user)
     {
		 if(isset($request->otp)){
			 if($request->otp == $request->mobile_otp){
				 redirect('/dashboard');
			 }else{
				Auth::logout();
				$data = array();
				$data['mobile_number'] = $request->mobile_number;
				$data['password'] = $request->password;
				$data['mobile_otp'] = $request->mobile_otp;
				$data['mobile_otp_msg'] = 'Invalid otp';
				return view('auth.otp', $data);
			 }
		 }else{
			$mobile_otp = rand(111111,999999);
			$message = "OTP to verify your account is " . $mobile_otp ." Team My Health Chart";
			$api = "http://message.smartwave.co.in/rest/services/sendSMS/sendGroupSms?AUTH_KEY=6d1bdc8e4530149c49564516e213f7&routeId=8&senderId=HLTCHT&mobileNos='".$request->mobile_number."'&message=" . urlencode($message);
			$sms = file_get_contents($api);
            $users = DB::table('admin_panel_creds')->where('mobile_number',$request->mobile_number)->first();
			$user = User::find($users->id);
            $user->otp = $mobile_otp;
            $user->save();
			Auth::logout();
			$data = array();
			$data['mobile_number'] = $request->mobile_number;
			$data['password'] = $request->password;
			$data['mobile_otp'] = $mobile_otp;
			$data['mobile_otp_msg'] = '';
			return view('auth.otp', $data);
		 }
     }

    // protected function authUserPass(CreateLoginRequest $request)
    // {
    //     dd('$request->user_type');
    //     switch ($request->user_type) {
    //         case 'new_pharmacies':
    //             // $request->session()->push('user_type', 'pharmacy');
    //             Session::put('user_type', 'pharmacy');
    //             break;
            
    //         default:
    //             // $request->session()->push('user_type', 'admin');
    //             Session::put('user_type', 'admin');
    //             break;
    //     }
    // }
}
