<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//   return view('welcome');
// });


// Route::get('/storage', function () {
//   Artisan::call('storage:link');
// });

// Ensure CAPTCHA route is globally available for all views that call it
Route::get('/captcha', [\Smt\Masterweb\Http\Controllers\CaptchaController::class, 'generate'])
	->name('captcha.generate')
	->middleware('throttle:60,1');
