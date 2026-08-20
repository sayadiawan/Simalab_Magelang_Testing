<?php

namespace Smt\Masterweb\Http\Controllers;

use Illuminate\Http\Request;
use Smt\Masterweb\Models\User;
use Ramsey\Uuid\Uuid;
use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\Role;

use Smt\Masterweb\Exports\UsersExport;

use Maatwebsite\Excel\Facades\Excel;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get auth user
        $user = Auth()->user();
        $users = User::all();
        return view('masterweb::module.admin.users.list',compact('user','users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //get auth user
        $user = Auth()->user();
        $privileges = \Smt\Masterweb\Models\Privileges::all();
        $laboratories = \Smt\Masterweb\Models\Laboratories::all();
        return view('masterweb::module.admin.users.add',compact('user','privileges','laboratories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      
            $user = new User;
            //uuid
            $uuid4 = Uuid::uuid4();

            $user->id   = $uuid4->toString();
            $user->name = $request->post('name');
            if( $request->get('level')=="01f62b38-fce5-43bf-9088-9d4a33a496da"){
                $user->root_firebase = $request->post('root_firebase');
            }
            $user->username = $request->post('username');
            $user->email    = $request->get('email');
            $user->level    = $request->get('level');
            $user->password = \Hash::make('elits');
            $user->laboratory_users    = $request->get('laboratory_users');
        


            if($request->hasFile('photo')){
                // $filenameWithExt = $request->file('photo')->getClientOriginalName();
                // $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                // $extension = $request->file('photo')->getClientOriginalExtension();
                // $filenameSimpan = $filename.'_'.time().'.'.$extension;
                // $path = $request->file('photo')->storeAs('public/uploads', $filenameSimpan);

                $file = $request->file('photo')->store('photo/', 'public');
                $user->photo = basename($file);

            }
            $user->save();

            if($user->level=="01f62b38-fce5-43bf-9088-9d4a33a496da"){
                return redirect()->route('adm-users.index')->with(['status'=>'User succesfully inserted','user'=>$user]);
            }else{
                return redirect()->route('adm-users.index')->with(['status'=>'User succesfully inserted']);
            }
      

                //return redirect()->route('module.admin.users.index')->with('status', 'User succesfully inserted');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //get auth user

        $user = Auth()->user();
        $users = User::findOrFail($id);
        $privileges = \Smt\Masterweb\Models\Privileges::all();

        return view('masterweb::module.admin.users.edit',compact('user','users','privileges','id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = \Smt\Masterweb\Models\User::findOrFail($id);
        $user->name     = $request->post('name');
        $user->username = $request->post('username');
        // $user->email = $request->get('email');
        $user->level            = $request->get('level');
        $user->laboratory_users = $request->get('laboratory_users');

        if($request->file('photo')){
            if($user->photo && file_exists(storage_path('assets/admin/images/photo/' . $user->photo))){
                \Storage::delete('assets/admin/images/photo/'.$user->photo);
            }
            $file = $request->file('photo')->store('assets/admin/images/photo/', 'public');
            $user->photo = $request->file('photo')->getClientOriginalName();
        }
        $user->save();
        return redirect()->route('adm-users.index', [$id])->with('status', 'User succesfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = \Smt\Masterweb\Models\User::findOrFail($id);
        $user->delete();
        return redirect()->route('adm-users.index', [$id])->with('status', 'User succesfully updated');
    }

    public function reset_password($id)
    {
        $user = \Smt\Masterweb\Models\User::findOrFail($id);
        $user->password = \Hash::make('diawan');
        $user->save();
        return redirect()->route('adm-users.index', [$id])->with('status', 'User succesfully reset password');
    }

    public function previlage($activity) 
    {
        $user = Auth()->user();
        $level=$user->level;

        $role = Role::where(['privilege_id'=>"a93b0f54-ad04-43cc-a34e-9d414be4c129"])
        ->join('ms_menuadm', function ($join) {
            $join->on('tb_role.menu_id', '=', 'ms_menuadm.id')
            ->where('ms_menuadm.link', '=',  Request::segment(1));
        })
        ->where('ms_menuadm.deleted_at', '=', null)
        ->first();

        if($role[$activity]){
            return true;
        }else{
            return false;
        }


        return view('masterweb::module.admin.laboratorium.coba',compact('role'));
        // return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function export() 
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
