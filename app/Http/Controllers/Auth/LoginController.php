<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

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

    // protected function authenticated(Request $request, $user)
    // {
    //     switch ($request->user_type) {
    //         case 'new_pharmacies':
    //             $request->session()->push('user_type', 'pharmacy');
    //             Session::put('user_type', 'pharmacy');
    //             break;
            
    //         default:
    //             $request->session()->push('user_type', 'admin');
    //             Session::put('user_type', 'admin');
    //             break;
    //     }
    // }

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
