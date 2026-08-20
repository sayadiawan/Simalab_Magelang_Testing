<?php


namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Smt\Masterweb\Models\Layout;
use Smt\Masterweb\Models\Module;
use Smt\Masterweb\Models\Menu;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $getMenu = Menu::where(['link'=>'beranda'])->whereOr(['link'=>'home'])->first();
        $page = Layout::where(['id_type'=>$getMenu->type])->first();

        return view('masterweb::public',compact('page'));
    }

    public function getPage($segment = null)
    {
        //get one slug
        $slug = explode('/',$segment)[0];
        $action = (@explode('/',$segment)[1] != NULL) ? explode('/',$segment)[1] : 'index';

        $getMenu = Menu::where(['link'=>$slug])->first();
        //check if null
        if($getMenu == NULL)
        {
            abort(404);
        }else{
            $page = Layout::where(['id_type'=>$getMenu->type,'action'=>$action])->first();
            if($page == NULL)
            {
                abort(404);
            }
            return view('masterweb::public',compact('page'));
        }
    }
}
