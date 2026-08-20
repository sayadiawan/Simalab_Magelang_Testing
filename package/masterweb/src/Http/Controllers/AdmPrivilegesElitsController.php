<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Role;
use \Smt\Masterweb\Models\Privileges;
use \Smt\Masterweb\Models\AdminMenu;
use Ramsey\Uuid\Uuid;

use \Smt\Masterweb\Models\PrivilegeMenu;
use \Smt\Masterweb\Models\PrivilegeMenuRole;

class AdmPrivilegesElitsController extends Controller
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

        $data = Privileges::where('is_elits',1)->get();
        return view('masterweb::module.admin.laboratorium.privilege.list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $menuadm = AdminMenu::where('is_elits',1)->where('upmenu','0')->get();
        $privilegemenurole =PrivilegeMenu::orderBy('created_at', 'asc')->get();
        return view('masterweb::module.admin.laboratorium.privilege.add',compact('menuadm','privilegemenurole'));
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
        $privilege_save->is_elits = 1;
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
      

        $privilegemenu =PrivilegeMenu::orderBy('created_at', 'asc')->get();
        foreach($privilegemenu as $privilegemenu)
        {
            $uuid4 = Uuid::uuid4();
            $privilegemenurole = new PrivilegeMenuRole;
            $privilegemenurole->id_privilege_menu_role=$uuid4;
            $privilegemenurole->id_privilege_menu=$privilegemenu->id_privilege_features;
           
            $privilegemenurole->privilege_id=$privilege_save->id;
            $privilegemenurole->value= (isset($input[join('-',explode(' ', $name))][$privilegemenu->id_privilege_features])) ? "1" : "0";
            $privilegemenurole->save();

        }





        return redirect('privileges-elits')->withInput(['alert', 'success']);
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
        
        $menuadm = AdminMenu::where('is_elits',1)->where('upmenu','0')->get();
        $privilege = Privileges::find($id);
        
        if($privilege->level!='USR'){
            $privilegemenurole =PrivilegeMenuRole::where('privilege_id',$privilege->id)
            ->join('tb_privilege_menu', 'tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')
            ->orderBy('tb_privilege_menu.created_at', 'asc')
            ->get();
            return view('masterweb::module.admin.laboratorium.privilege.edit',compact('menuadm','privilege','privilegemenurole'));
        }else{
            $privilegemenurole =PrivilegeMenuRole::where('privilege_id','bef9ec45-3a33-4ee8-a659-c8e78560b0b9')
            ->where('value','1')
            ->join('tb_privilege_menu', 'tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')
            ->orderBy('tb_privilege_menu.created_at', 'asc')
            ->get();

            $privilegemenuroleuser =PrivilegeMenuRole::where('privilege_id','7d6bc1b7-5115-4724-820d-f04744f61828')
            ->join('tb_privilege_menu', 'tb_privilege_menu.id_privilege_features', '=', 'tb_privilege_menu_role.id_privilege_menu')
            ->orderBy('tb_privilege_menu.created_at', 'asc')
            ->get();
            return view('masterweb::module.admin.laboratorium.privilege.edit',compact('menuadm','privilege','privilegemenurole','privilegemenuroleuser'));
        }
        
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
        $menuadm = AdminMenu::where('is_elits',1)->get();

       
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

        $role_delete = Role::where('privilege_id',$id)  
        ->join('ms_menuadm', function ($join) {
            $join->on('ms_menuadm.id', '=', 'tb_role.menu_id')
          
            ->whereNull('ms_menuadm.deleted_at')
            ->whereNull('tb_role.deleted_at');
             
        })->where('is_elits',1);

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

        $privilegemenurole=PrivilegeMenuRole::where('privilege_id',$id)->delete();
        $privilegemenu=PrivilegeMenu::all();
        // dd($input);

        foreach($privilegemenu as $privilegemenu){
            $name = $privilegemenu->name_privilege_features;

            $uuid4 = Uuid::uuid4();
            $privilegemenuroleupdate = new PrivilegeMenuRole;
            $privilegemenuroleupdate->id_privilege_menu=$privilegemenu->id_privilege_features;
            $privilegemenuroleupdate->privilege_id =$id;
            $privilegemenuroleupdate->id_privilege_menu_role=$uuid4;
            $privilegemenuroleupdate->value= (isset($input[join('-',explode(' ', $name))][$privilegemenu->id_privilege_features])) ? "1" : "0";
            $privilegemenuroleupdate->save();
        }

        return redirect('privileges-elits')->withInput(['alert', 'success']);
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
        return redirect()->route('privileges-elits.index', [$id])->with('status', 'Data berhasil dihapus');
    }
}
