<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//   return $request->user();
// });

// --- TMS30i: tanpa auth, tanpa CSRF ---
Route::post('tms/result', 'TmsController@store');
Route::get('tms/result', 'TmsController@index');
// Alias lama (kompatibilitas)
Route::post('biolis/result', 'TmsController@store');
Route::get('biolis/result', 'TmsController@index');

// --- Order TMS untuk komputer alat (Basic Auth) ---
Route::middleware('tms.auth')->group(function () {
    Route::get('tms/orders', 'TmsController@pendingOrders');
    Route::get('tms/orders/{id_order_tms}', 'TmsController@showOrder');
    Route::post('tms/orders/execute', 'TmsController@executeOrder');
    Route::post('tms/orders/{id_order_tms}/execute', 'TmsController@executeOrder');
});
