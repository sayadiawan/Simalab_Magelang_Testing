<?php

namespace Smt\Masterweb\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Hash;


use App\Http\Controllers\Controller;

class AdmPasswordController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}
    //
    public function edit()
    {
        $user = Auth()->user();
        return view('masterweb::module.admin.users.edit_password', compact('user'));
    }

    public function update(Request $request)
    {
        $request->user()->update([
        'password' => Hash::make($request->get('password'))
    ]);

    return redirect()->route('user.adm-password.edit')->with('status', 'Password berhasil diperbarui.');
    }
}
