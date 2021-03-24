<?php

namespace App\Http\Controllers\logistic\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;

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
    protected $redirectTo = RouteServiceProvider::LOGISTIC_HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:new_logistics')->except('logout');
    }

    public function attemptLogin(Request $request)
    {
        // Returned validated fields also contain the csrf token,
        // therefore, we pick only email and password.
    
        $guard_result = Auth::guard('new_logistics')->attempt(['email' => $request->email, 'password' => $request->password]);
        $user = User::where(['email' => $request->email, 'password' => $request->password]);
        $user->user_type = 'logistic';
        \Auth::login($user);
        return  $guard_result;

    }

    // public function login(Request $request)
    // {
    //     // Returned validated fields also contain the csrf token,
    //     // therefore, we pick only email and password.
    
    //     $this->validate($request, [
    //         'email' => 'required',
    //         'password' => 'required'
    //     ]);

    //     $user = User::whereRaw('password = AES_ENCRYPT("' . $request->input('password') . '", "' . $xyz . '")')
    //     ->where('username', $username)
    //     ->where('isActive', 1)
    //     ->first();

    //     Auth::login($user);
    //     return redirect()->route('logistic_home');
    // }

    public function showLoginForm()
    {
        return view('logistic.auth.login');
    }

    protected function guard()
    {
        return Auth::guard('new_logistics');
    }

}
