<?php
namespace Smt\Masterweb\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdmBiodataController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
    }

    public function index()
    {
        $user = Auth()->user();
        return view('masterweb::module.admin.users.biodata',compact('user'));
    }

    public function update(Request $request, $id)
    {
        //
        $user = \Smt\Masterweb\Models\User::findOrFail($id);
        $request->validate([
            'name'      => 'required',
            'email'     => 'required',
            'username'  => 'required'
        ]);

        $user->name = $request->post('name');
        $user->email = $request->get('email');
        $user->username = $request->post('username');

        if($request->hasFile('photo')){
         
            if($user->photo && file_exists(storage_path('app/public/photo/' . $user->photo))){
                    
                unlink(storage_path('app/public/photo/' . $user->photo));
            }
            $file = $request->file('photo')->store('photo/', 'public');
            $imgName = basename($file);

        } else {
            $imgName = $user->photo;
        }
        $user->photo = $imgName;
        $user->save();
        return redirect('/biodata')->with('status', 'User succesfully updated');
    }
}
