<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\User;
use \Smt\Masterweb\Models\Container;



use Carbon\Carbon;


class LaboratoriumContainerManagement extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth()->user();
        $level = $user-> getlevel->level;
     

        
        // if($level =="elits-dev"|| $level=="admin"){
            $containers = Container::orderBy('created_at')->get();
       

      
            return view('masterweb::module.admin.laboratorium.container.list',compact('containers'));
        // }else{
        //     return abort(404);
        // }
       
       
        
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
     

        
        // if($level =="elits-dev"|| $level=="admin"){
            return view('masterweb::module.admin.laboratorium.container.add');
        // }else{
        //     return abort(404);
        // }
       
     
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      
            $container = new Container;
            //uuid
            $uuid4 = Uuid::uuid4();

            $container->id_container = $uuid4->toString();
            $container->name_container = $request->post('name_container');
          
            $container->save();

            return redirect()->route('elits-containers.index')->with(['status'=>'Container succesfully inserted']);
            
      

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
     
        $container = Container::findOrFail($id);
        
       

        return view('masterweb::module.admin.laboratorium.container.edit',compact('container','id'));
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
        $container = Container::findOrFail($id);
        $container->name_container = $request->post('name_container');
        $container->save();
        return redirect()->route('elits-containers.index', [$id])->with('status', 'Container succesfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $container = Container::findOrFail($id);
        $container->delete();
        return redirect()->route('elits-containers.index', [$id])->with('status', 'Container succesfully updated');
    }

    


}

