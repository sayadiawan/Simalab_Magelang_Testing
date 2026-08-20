<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Product;
use \Smt\Masterweb\Models\Unit;

class LaboratoriumProductManagement extends Controller
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
        $products = Product::all();

        return view('masterweb::module.admin.laboratorium.product.list',compact('user','products'));

                
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
        $units = Unit::all();

        return view('masterweb::module.admin.laboratorium.product.add',compact('user','units'));
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
        $product = new Product;
        //uuid
        $uuid4 = Uuid::uuid4();

        $product->id_product = $uuid4->toString();
        $product->code_product = $request->post('code_product');
        $product->name_product = $request->post('name_product');
        $product->unit_product = $request->post('unit_product');
        $product->type_product = $request->post('type_product');
        $product->desc_product = $request->post('desc_product');
        $product->save();

        return redirect()->route('elits-products.index')->with(['status'=>'Major succesfully inserted']);
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

        $units = Unit::all();

        $product = Product::where('id_product',$id)->first();



        return view('masterweb::module.admin.laboratorium.product.edit',compact('product','auth','id','units'));

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

        $data =$request->all();
        $product = Product::find($id);
        $product->code_product = $request->post('code_product');
        $product->name_product = $request->post('name_product');
        $product->unit_product = $request->post('unit_product');
        $product->type_product = $request->post('type_product');
        $product->desc_product = $request->post('desc_product');
        $product->save();
        return redirect()->route('elits-products.index')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $products = Product::findOrFail($id);
        $products->delete();
        return redirect()->route('elits-products.index')->with('status', 'Data berhasil dihapus');
    }
}
