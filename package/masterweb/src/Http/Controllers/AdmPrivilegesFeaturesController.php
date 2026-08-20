<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Role;
use \Smt\Masterweb\Models\PrivilegeMenu;
use \Smt\Masterweb\Models\PrivilegeMenuRole;
use \Smt\Masterweb\Models\AdminMenu;

use \Smt\Masterweb\Models\Privileges;


use Smt\Masterweb\Models\UserDevice;
use Smt\Masterweb\Models\User;



use Ramsey\Uuid\Uuid;

class AdmPrivilegesFeaturesController extends Controller
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

        $parent = AdminMenu::where('is_elits',1)->get();
        $data = PrivilegeMenu::all();
        return view('masterweb::module.admin.laboratorium.privilegesfeatures.list',compact('data','parent'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parent = AdminMenu::where('is_elits',1)->where('upmenu','!=','0')->get();
        return view('masterweb::module.admin.laboratorium.privilegesfeatures.add',compact('menuadm'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      
        $input = $request->post();
     
             

        $name = $input['name'];
        $link = $input['link'];
        $upmenu = $input['upmenu'];


        $privilegemenu = new PrivilegeMenu;
        
        $uuid4 = Uuid::uuid4();

        $privilegemenu->id_privilege_features=$uuid4;
        $privilegemenu->name_privilege_features = $name;
        $privilegemenu->sub_link = $link;
        $privilegemenu->menu_id  = $upmenu;

        if(isset($input['bydevice']))
        {
            $privilegemenu->bydevice=1;
        }else{
            $privilegemenu->bydevice=0;
        }  

        
        $privilegemenu->save();

        $privileges = Privileges::where('is_elits','!=',0)->get();

        foreach($privileges as $privilege)
        {
            $privilegemenurole = new PrivilegeMenuRole;
            $uuid4 = Uuid::uuid4();
            $privilegemenurole->id_privilege_menu_role = $uuid4;
            $privilegemenurole->id_privilege_menu = $privilegemenu->id_privilege_features;
            $privilegemenurole->privilege_id=$privilege->id;
            $privilegemenurole->value=1;
            $privilegemenurole->save();

        }

      

        


        return redirect('privileges-features')->withInput(['alert', 'success']);
    }

    public function data(Request $request)
    {
        $privilegemenu = PrivilegeMenu::find($request->id);

        return json_encode($privilegemenu);
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
        $parent = AdminMenu::where('is_elits',1)->where('upmenu','!=','0')->get();
        return view('masterweb::module.admin.laboratorium.privilegesfeatures.edit',compact('menuadm','privilege'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
       
        $input = $request->post();


        $name = $input['name'];
        $link = $input['link'];
        $upmenu = $input['upmenu'];
        $id = $input['id'];


        $privilegemenu = PrivilegeMenu::findOrFail($id);

        $privilegemenu->name_privilege_features = $name;
        $privilegemenu->sub_link = $link;
        $privilegemenu->menu_id  = $upmenu;
        if(isset($input['bydevice']))
        {
            $privilegemenu->bydevice=1;
        }else{
            $privilegemenu->bydevice=0;
        }  
        $privilegemenu->save();

        return redirect('privileges-features')->withInput(['alert', 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $privilege = PrivilegeMenu::findOrFail($id);
        $privilege->delete();


        $privilagemenuroles = PrivilegeMenuRole::where('id_privilege_menu','=',$id)->get();

        foreach($privilagemenuroles as $privilagemenurole)
        {
            $privilagemenurole->delete();

        }

        

        return redirect()->route('privileges-features.index', [$id])->with('status', 'Data berhasil dihapus');
    }
}
