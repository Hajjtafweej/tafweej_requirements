<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use \Illuminate\Http\Request;
use Auth;
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
    protected $redirectTo = '/account/profile';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

     /**
      * Handle a login request to the application.
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
      */
     public function login(Request $request)
     {
         // $request->validate([
         //     'sa_id' => 'required|max:10',
         //     'password' => 'required'
         // ]);
         //
         // // If the class is using the ThrottlesLogins trait, we can automatically throttle
         // // the login attempts for this application. We'll key this by the username and
         // // the IP address of the client making these requests into this application.
         // if ($this->hasTooManyLoginAttempts($request)) {
         //     $this->fireLockoutEvent($request);
         //     return $this->sendLockoutResponse($request);
         // }
         //
         // if (Auth::attempt($request->only('sa_id', 'password'))) {
         //      return redirect('/');
         // }else {
         //     return redirect()->back()->withInput($request->only('sa_id', 'remember'))->withErrors(['sa_id' => trans('auth.failed')]);
         // }
     }

}
