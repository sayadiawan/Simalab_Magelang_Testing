<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Role;
use \Smt\Masterweb\Models\Privileges;
use \Smt\Masterweb\Models\AdminMenu;

class AdmPrivilegesController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //get auth user
        $data = Privileges::all();
        return view('masterweb::module.admin.privilege.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuadm = AdminMenu::all()->where('upmenu','0');
        return view('masterweb::module.admin.privilege.add',compact('menuadm'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $menuadm = AdminMenu::all();
        $input = $request->post();

        $name = $input['name'];
        $level = $input['level'];
        $description = $input['description'];

        //save privilege
        $privilege_save = new Privileges;
        $privilege_save->name = $name;
        $privilege_save->level = $level;
        $privilege_save->description = $description;
        $privilege_save->save();
        
        foreach($menuadm as $dmn)
        {
            $create = (isset($input['create'][$dmn->id])) ? "1" : "0";
            $read = (isset($input['read'][$dmn->id])) ? "1" : "0";
            $update = (isset($input['update'][$dmn->id])) ? "1" : "0";
            $delete = (isset($input['delete'][$dmn->id])) ? "1" : "0";

            // if($create == "0" OR $read == "0" OR $update == "0" OR $delete == "0")
            // {
                //menyimpan yang bukan default 1111
                $role_save = new Role;
                $role_save->privilege_id = $privilege_save->id;
                $role_save->menu_id = $dmn->id;
                $role_save->create = $create;
                $role_save->read = $read;
                $role_save->update = $update;
                $role_save->delete = $delete;
                $role_save->save();
            // }
        }

        return redirect('adm-privileges')->withInput(['alert', 'success']);
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
        $menuadm = AdminMenu::all()->where('upmenu','0');
        $privilege = Privileges::find($id);
        return view('masterweb::module.admin.privilege.edit',compact('menuadm','privilege'));
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
        $menuadm = AdminMenu::all();
        $input = $request->post();

        $name = $input['name'];
        $level = $input['level'];
        $description = $input['description'];

        //save privilege
        $privilege_save = Privileges::find($id);
        $privilege_save->name = $name;
        $privilege_save->level = $level;
        $privilege_save->description = $description;
        $privilege_save->save();

        $role_delete = Role::where('privilege_id',$id);
        $role_delete->delete();

        foreach($menuadm as $dmn)
        {
            $create = (isset($input['create'][$dmn->id])) ? "1" : "0";
            $read = (isset($input['read'][$dmn->id])) ? "1" : "0";
            $update = (isset($input['update'][$dmn->id])) ? "1" : "0";
            $delete = (isset($input['delete'][$dmn->id])) ? "1" : "0";

            // if($create == "0" OR $read == "0" OR $update == "0" OR $delete == "0")
            // {
                //menyimpan yang bukan default 1111
                $role_save = new Role;
                $role_save->privilege_id = $privilege_save->id;
                $role_save->menu_id = $dmn->id;
                $role_save->create = $create;
                $role_save->read = $read;
                $role_save->update = $update;
                $role_save->delete = $delete;
                $role_save->save();
            // }
        }

        return redirect('adm-privileges')->withInput(['alert', 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $privilege = Privileges::findOrFail($id);
        $privilege->delete();
        return redirect()->route('adm-privileges.index', [$id])->with('status', 'Data berhasil dihapus');
    }
}
