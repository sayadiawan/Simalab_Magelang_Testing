<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Stock;
use \Smt\Masterweb\Models\Product;

class LaboratoriumInventoryManagement extends Controller
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
        //$users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da');
        //get auth user
        $user = Auth()->user();

        
        $inventories = Stock::all();
        return view('masterweb::module.admin.laboratorium.inventory.list',compact('user','inventories'));
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

        $products= Product::all();

        
        return view('masterweb::module.admin.laboratorium.inventory.add',compact('user','products'));
        //get all menu public
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data =$request->all();
        
      
        
         $inventory = new Stock;
         //uuid
         $uuid4 = Uuid::uuid4();
 
         $inventory->id_stock = $uuid4->toString();
         $inventory->product_stock = $request->post('productAttributes');
         if($request->post('type_stock')=="tambah"){
            $inventory->type_stock = 1;
         }else{
            $inventory->type_stock = 0;
         }
         $inventory->qty_stock = $request->post('qty_stock');
         $inventory->save();
 
         return redirect()->route('elits-inventories.index')->with(['status'=>'Inventory succesfully inserted']);
   
         //return redirect()->route('user-client-management.index',[$request->get('client_id')])->with(['status'=>'User succesfully inserted','user'=>$user]);
  
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
        $auth = Auth()->user();

        $inventory = Stock::where('id_inventory',$id)->first();



        return view('masterweb::module.admin.laboratorium.inventory.edit',compact('auth','inventory','id'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {

        
        // print_r($data);
         
      
        
         $data =$request->all();
         $inventory = Stock::find($id);
         $inventory->save();
 
         return redirect()->route('elits-invertories.index')->with(['status'=>'Inventory succesfully updated']);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inventory = Stock::findOrFail($id);
        $inventory->delete();
        return redirect()->route('elits-invertories.index')->with('status', 'Data berhasil dihapus');
    }
}
