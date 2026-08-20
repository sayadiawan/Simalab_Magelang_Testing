<?php
namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Type;
use \Smt\Masterweb\Models\Menu;

class AdmMenuPublicController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}
    public function index()
    {
        $type = Type::all()->where('active','1');
        $parent = Menu::all()->where('upmenu',null);
        $data = Menu::all()->sortBy('order')->where('upmenu','0');
        return view('masterweb::module.admin.menu_public.list',compact('data','parent','type'));
    }

    public function sort(Request $request)
    {
        $main = $request->main;
        
        $order = 1;
        foreach ($main as $value) {
            $mainId = $value[0];

            $main = Menu::findOrFail($mainId);
            $main->order = $order;
            $main->save();

            $subId = (isset($value[1])) ? $value[1] : NULL;
            if($subId != NULL)
            {
                $ordersub = 1;
                foreach ($subId as $sub_id) {
                    $main = Menu::findOrFail($sub_id);
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
        $admMenu = new Menu;
        
        //check last order by parent
        $menu = Menu::all()->where('upmenu',$request->get('upmenu'))->sortByDesc('order')->first();
        
        $lasted = (isset($menu->order)) ? $menu->order : '0';
        
        if($request->post('typeNew'))
        {
            $typeNew = new Type;
            $typeNew->name = $request->post('typeNew');
            $typeNew->action = 'index';
            $typeNew->active = '1';
            $typeNew->save();
            $type = $typeNew->id;
        }else{
            $type = $request->post('type');
        }
        
        $admMenu->upmenu = $request->post('upmenu');
        $admMenu->name = $request->post('name');
        $admMenu->type = $type;
        $admMenu->link = $request->post('link');
        $admMenu->order = $lasted+1;
        $admMenu->publish = 1;
        $admMenu->save();

        return redirect(url('menu'))->with('status', 'Data berhasil disimpan!');
    }

    public function data(Request $request)
    {
        $menu = Menu::find($request->id);

        return json_encode($menu);
    }

    public function update(Request $request)
    {
        $admMenu = Menu::find($request->post('id'));

        if($request->post('typeNew'))
        {
            $typeNew = new Type;
            $typeNew->name = $request->post('typeNew');
            $typeNew->action = 'index';
            $typeNew->active = '1';
            $typeNew->save();
            $type = $typeNew->id;
        }else{
            $type = $request->post('type');
        }

        $admMenu->upmenu = $request->post('upmenu');
        $admMenu->name = $request->post('name');
        $admMenu->type = $type;
        $admMenu->link = $request->post('link');
        $admMenu->publish = 1;
        $admMenu->save();
        
        return redirect(url('menu'))->with('status', 'Data berhasil disimpan!');
    }

    public function change(Request $request)
    {
        $admMenu = Menu::find($request->get('id'));

        $admMenu->upmenu = 0;
        $admMenu->publish = 1;
        $admMenu->save();

        return redirect(url('menu'))->with('status', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        $user = Menu::findOrFail($id);
        $user->delete();
        return redirect(url('menu'))->with('status', 'Data berhasil disimpan!');
    }
}
