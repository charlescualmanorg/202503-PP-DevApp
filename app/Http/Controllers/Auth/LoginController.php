<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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
    protected $redirectTo = RouteServiceProvider::NEW;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
        // Se añade 'active' => 1 para solo permitir logins a usuarios activos
        return array_merge($request->only($this->username(), 'password'), ['active' => 1]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = \App\User::where($this->username(), $request->{$this->username()})->first();
        if ($user && !$user->active) {
            $errors = [$this->username() => trans('auth.inactive')];
        } else {
            $errors = [$this->username() => trans('auth.failed')];
        }

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }
}
