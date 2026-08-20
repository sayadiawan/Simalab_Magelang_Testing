<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\SampleType;
use \Smt\Masterweb\Models\Product;
use Smt\Masterweb\Models\StockOpname;

class StockOpnameController extends Controller
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
        if(request()->has('month') AND request()->has('year'))
        {
            $month = request()->month;
            $year  = request()->year;
        }else{
            $month = date('m');
            $year  = date('Y');
        }
        $user = Auth()->user();
        $stockopnames = StockOpname::where('month_stockopname',$month)->where('year_stockopname',$year)->orderBy('created_at')->get();
        
        return view('masterweb::module.admin.laboratorium.stock-opname.list',compact('user','stockopnames','month','year'));
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
        $products= Product::orderBy('code_product')->get();

        

        return view('masterweb::module.admin.laboratorium.stock-opname.add',compact('products','user'));
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
        
        // print_r($data);
         
      
        
         $stockopname = new StockOpname;
         //uuid
         $uuid4 = Uuid::uuid4();
 
         $stockopname->id_stockopname      = $uuid4->toString();
         $stockopname->month_stockopname    = $request->post('month_stockopname');
         $stockopname->year_stockopname    = $request->post('year_stockopname');
         $stockopname->code_stockopname    = $request->post('code_stockopname');
         $stockopname->product_stockopname = $request->post('product_stockopname');
         $stockopname->qty_stockopname = $request->post('qty_stockopname');
        $stockopname->save();
 
        return redirect()->route('stock-opname.index')->with(['status'=>'Berhasil ditambahkan!']);
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

        $data = StockOpname::find($id);
        $products= Product::orderBy('code_product')->get();
        return view('masterweb::module.admin.laboratorium.stock-opname.edit',compact('data','products','auth','id'));

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
        $stockopname = StockOpname::find($id);
        $stockopname->month_stockopname    = $request->post('month_stockopname');
        $stockopname->year_stockopname    = $request->post('year_stockopname');
        $stockopname->product_stockopname = $request->post('product_stockopname');
        $stockopname->qty_stockopname = $request->post('qty_stockopname');
        $stockopname->save();
      

        return redirect()->route('stock-opname.index')->with('status', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stockopname = StockOpname::findOrFail($id);
        $stockopname->delete();
        return redirect()->route('stock-opname.index')->with('status', 'Data berhasil dihapus');
    }
}
