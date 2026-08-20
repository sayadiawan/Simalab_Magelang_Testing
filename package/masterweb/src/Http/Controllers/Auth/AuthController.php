<?php

namespace Smt\Masterweb\Http\Controllers\Auth;

use App\Rules\Captcha;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends Controller
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
  protected $redirectTo = 'home';

  /**
   * Create a new controller instance.
   *
   * @return void
   */

  public function showLoginForm()
  {
    return redirect('/login');
  }

  public function username()
  {
    return 'username';
  }
}