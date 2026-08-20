<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;


use \Smt\Masterweb\Models\Privileges;

use \Smt\Masterweb\Models\Role;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\AdminMenu;

class AdmMenuElitsController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
    }
    
    public function index()
    {
        $parent = AdminMenu::where('upmenu','0')->where('is_elits',1)->get();
        $data = AdminMenu::all()->sortBy('order')->where('publish','1')->where('upmenu','0')->where('is_elits',1);
        return view('masterweb::module.admin.laboratorium.menu.list',compact('data','parent'));
    }

    public function sort(Request $request)
    {
        $main = $request->main;
        
        $order = 14;
        foreach ($main as $value) {
            $mainId = $value[0];

            $main = AdminMenu::findOrFail($mainId);
            $main->order = $order;
            $main->save();

            $subId = (isset($value[1])) ? $value[1] : NULL;
            if($subId != NULL)
            {
                $ordersub = 1;
                foreach ($subId as $sub_id) {
                    $main = AdminMenu::findOrFail($sub_id);
                    $main->upmenu = $mainId;
                    $main->order = $ordersub;
                    $main->save();
                    $ordersub++;
                }
            }
            $order++;
        }
    }

    public function store(Request $request)
    {
        $admMenu = new AdminMenu;
        
        //check last order by parent
        $menu = AdminMenu::all()->where('upmenu',$request->post('upmenu'))->sortByDesc('order')->first();
        
        $lasted = (isset($menu->order)) ? $menu->order : '0';

        $admMenu->upmenu = $request->post('upmenu');
        $admMenu->name = $request->post('name');
        $admMenu->is_elits =1;
        $admMenu->link = $request->post('link');
        $admMenu->icon = $request->post('icon');
        $admMenu->type = $request->post('type');
        $admMenu->order = $lasted+1;
        $admMenu->publish = 1;
        $admMenu->save();

        $allprivillegs=Privileges::all();

        foreach($allprivillegs as $preville){
            $newrole = new Role;
            $newrole->privilege_id = $preville->id;
            $newrole->menu_id = $admMenu->id;
            $newrole->create = "1";
            $newrole->read = "1";
            $newrole->update = "1";
            $newrole->delete = "1";
            $newrole->save();
        }

        return redirect(url('menu-elits'))->with('status', 'Data berhasil disimpan!');
    }

    public function data(Request $request)
    {
        $menu = AdminMenu::find($request->id);

        return json_encode($menu);
    }

    public function update(Request $request)
    {
        $admMenu = AdminMenu::find($request->id);

        $admMenu->upmenu = $request->post('upmenu');
        $admMenu->name = $request->post('name');
        $admMenu->link = $request->post('link');
        $admMenu->icon = $request->post('icon');
        $admMenu->type = $request->post('type_link');
        $admMenu->publish = 1;
        $admMenu->save();

        return redirect(url('menu-elits'))->with('status', 'Data berhasil disimpan!');
    }

    public function change(Request $request)
    {
        $admMenu = AdminMenu::find($request->get('id'));

        $admMenu->upmenu = 0;
        $admMenu->publish = 1;
        $admMenu->save();

        return redirect(url('menu-elits'))->with('status', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        $user = AdminMenu::findOrFail($id);
        $user->delete();
        return redirect(url('menu-elits'))->with('status', 'Data berhasil disimpan!');
    }

    public function show()
    {
        # code...
    }
}
