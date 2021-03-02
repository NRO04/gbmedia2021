<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Studios\StudioLastLogin;
use App\Models\Tenancy\Tenant;
use App\Providers\RouteServiceProvider;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
//        session(['studio_id' => tenant('studio_id')]);
//        session(['studio_name' => tenant('id')]);

    }

    function authenticated(Request $request, $user)
    {
        $now = Carbon::now()->toDateTimeString();
        $studio = Tenant::where('id', tenant('id'))->first();

        if($user->is_admin != 1) {
            $studio->last_login_user_id = $user->id;
            $studio->last_login_user_name = "$user->first_name $user->last_name";
            $studio->last_login_ip = $request->getClientIp();
            $studio->last_login_datetime = $now;
            $studio->last_login_user_agent = $request->header('User-Agent');
            $studio->save();
        }

        session(['studio_id' => tenant('id')]);
        session(['studio_name' => tenant('studio_name')]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('login');
    }

    protected function credentials(\Illuminate\Http\Request $request)
    {
        return ['email' => $request->email, 'password' => $request->password, 'status' => 1];
    }
}
