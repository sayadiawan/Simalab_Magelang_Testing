<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\SampleOfficer;



use Carbon\Carbon;


class LaboratoriumSampleOfficerManagement extends Controller
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
        $level = $user-> getlevel->level;
     

        if( $level =="LAB"){
            $users = User::where('ms_users.level', '=', '0e6da765-0f3a-4471-9e1d-6af257e60a70')
            ->orWhere('ms_users.level', '=', '328082d9-4be4-4da8-907e-3c414d093371')
            ->orWhere('ms_users.level', '=', '3382abf2-8518-42f9-91e1-096f25da8ae8')
            ->join('ms_privilege', function($join)
            {
              $join->on('ms_privilege.id', '=', 'ms_users.level')
              ->where('ms_privilege.is_elits','=','1')
              ->whereNull('ms_privilege.deleted_at')
              ->whereNull('ms_users.deleted_at');
            })
            ->select('ms_privilege.name as privilege','ms_users.*')
            ->get();
        }else if($level =="elits-dev"|| $level=="admin"){
            $users = User::join('ms_privilege', function($join)
            {
              $join->on('ms_privilege.id', '=', 'ms_users.level')
              ->where('ms_privilege.is_elits','=','1')
              ->whereNull('ms_privilege.deleted_at')
              ->whereNull('ms_users.deleted_at');
            })
            ->select('ms_privilege.name as privilege','ms_users.*')
            ->get();
        }else{
            $users = User::join('ms_privilege', function($join)
            {
              $join->on('ms_privilege.id', '=', 'ms_users.level')
              ->where('ms_privilege.is_elits','=','1')
              ->whereNull('ms_privilege.deleted_at')
              ->whereNull('ms_users.deleted_at');
            })
            ->select('ms_privilege.name as privilege','ms_users.*')
            ->get();
        }
       

      
        return view('masterweb::module.admin.laboratorium.sample-type.list',compact('user','users'));
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
        $level = $user-> getlevel->level;
     

        if( $level =="LAB"){
            $user = Auth()->user();
            $privileges = \Smt\Masterweb\Models\Privileges::where('id', '=', '0e6da765-0f3a-4471-9e1d-6af257e60a70')
            ->orWhere('id', '=', '328082d9-4be4-4da8-907e-3c414d093371')
            ->orWhere('id', '=', '3382abf2-8518-42f9-91e1-096f25da8ae8')->get();
        }else if($level =="elits-dev"|| $level=="admin"){
            $user = Auth()->user();
            $privileges = \Smt\Masterweb\Models\Privileges::where('is_elits', '=', '1')->get();
        }else{
            return abort(404);
        }
       
        return view('masterweb::module.admin.laboratorium.sample-type.add',compact('user','privileges'));
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

            $user->id = $uuid4->toString();
            $user->name = $request->post('name');
            if( $request->get('level')=="01f62b38-fce5-43bf-9088-9d4a33a496da"){
                $user->root_firebase = $request->post('root_firebase');
            }
            $user->username = $request->post('username');
            $user->email = $request->get('email');
            $user->level = $request->get('level');
            $user->password = \Hash::make('elits');
        


            if($request->hasFile('photo')){
                $file = $request->file('photo')->store('photo/', 'public');
                $user->photo = basename($file);

            }
            $user->save();

            return redirect()->route('elits-users.index')->with(['status'=>'User succesfully inserted']);
            
      

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
        $level = $user-> getlevel->level;
     
        $users = User::findOrFail($id);
        
        if( $level =="LAB"){
            $user = Auth()->user();
            $privileges = \Smt\Masterweb\Models\Privileges::where('id', '=', '0e6da765-0f3a-4471-9e1d-6af257e60a70')
            ->orWhere('id', '=', '328082d9-4be4-4da8-907e-3c414d093371')
            ->orWhere('id', '=', '3382abf2-8518-42f9-91e1-096f25da8ae8')->get();
        }else if($level =="elits-dev"|| $level=="admin"){
            $user = Auth()->user();
            $privileges = \Smt\Masterweb\Models\Privileges::where('is_elits', '=', '1')->get();
        }else{
            return abort(404);
        }

        return view('masterweb::module.admin.laboratorium.sample-type.edit',compact('user','users','privileges','id'));
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
        $user->name = $request->post('name');
        $user->username = $request->post('username');
        // $user->email = $request->get('email');
        $user->level = $request->get('level');

        if($request->file('photo')){
          
            if($user->photo && file_exists(storage_path('app/public/photo/' . $user->photo))){
                
                unlink(storage_path('app/public/photo/' . $user->photo));
            }
            $file = $request->file('photo')->store('photo/', 'public');
            $user->photo = basename($file);

        }
        $user->save();
        return redirect()->route('elits-sample-officer.index', [$id])->with('status', 'User succesfully updated');
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
        return redirect()->route('elits-sample-officer.index', [$id])->with('status', 'User succesfully updated');
    }

    public function reset_password($id)
    {

        $user = \Smt\Masterweb\Models\User::findOrFail($id);
        $user->password = \Hash::make('elits');
        $user->save();
        return redirect()->route('elits-sample-officer.index', [$id])->with('status', 'User succesfully reset password');
    }


}

