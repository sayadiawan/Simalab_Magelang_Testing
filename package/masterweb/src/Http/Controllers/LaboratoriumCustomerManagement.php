<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Industry;
use \Smt\Masterweb\Models\Customer;
use \Smt\Masterweb\Models\PermohonanUji;

class LaboratoriumCustomerManagement extends Controller
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
    $customers = Customer::orderBy('created_at', 'desc')->get();

    return view('masterweb::module.admin.laboratorium.customer.list', compact('user', 'customers'));
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
    $categories = Industry::all();


    return view('masterweb::module.admin.laboratorium.customer.add', compact('user', 'categories'));
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
    $data = $request->all();

    // print_r($data);



    $customer = new Customer;
    //uuid
    $uuid4 = Uuid::uuid4();

    $customer->id_customer = $uuid4->toString();
    $customer->name_customer = $request->post('name_customer');
    $customer->address_customer = $request->post('address_customer');
    $customer->email_customer = $request->post('email_customer');
    // $customer->category_customer = $request->post('category_customer');
    $customer->cp_customer = $request->post('cp_customer');
    $kecamatan = $request->post('kecamatan');
    if ($kecamatan != '0') {
      $customer->kecamatan_customer = $request->post('kecamatan');
    } else {
      $customer->kecamatan_customer = $request->post('kecamatan_other');
    }


    $customer->save();




    return redirect()->route('elits-customers.index')->with(['status' => 'Customer succesfully inserted']);

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

    $customer = Customer::where('id_customer', $id)->first();

    $categories = Industry::all();
    $jumlah_nota = PermohonanUji::where('customer_id', $id)->whereNull('deleted_at')->count();

    return view('masterweb::module.admin.laboratorium.customer.edit', compact('customer', 'auth', 'categories', 'id', 'jumlah_nota'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update($id, Request $request)
  {
    $customer = Customer::find($id);
    if (!$customer) {
      return redirect()->route('elits-customers.index')->with(['status' => 'Customer tidak ditemukan']);
    }

    $oldName = $customer->name_customer;
    $newName = $request->post('name_customer');
    $nameChanged = trim((string) $oldName) !== trim((string) $newName);

    $customer->name_customer = $newName;
    $customer->address_customer = $request->post('address_customer');
    $customer->email_customer = $request->post('email_customer');
    // $customer->category_customer = $request->post('category_customer');
    $customer->cp_customer = $request->post('cp_customer');
    $kecamatan = $request->post('kecamatan');
    if ($kecamatan != '0') {
      $customer->kecamatan_customer = $request->post('kecamatan');
    } else {
      $customer->kecamatan_customer = $request->post('kecamatan_other');
    }
    $customer->save();

    $status = 'Customer succesfully updated';

    // Sync nama di nota jika dicentang (dan nama memang berubah)
    if ($nameChanged && $request->filled('update_nota_nama')) {
      $updated = PermohonanUji::where('customer_id', $id)
        ->whereNull('deleted_at')
        ->where(function ($q) use ($oldName) {
          $q->whereNull('nota_diterima_dari')
            ->orWhere('nota_diterima_dari', '')
            ->orWhere('nota_diterima_dari', $oldName);
        })
        ->update(['nota_diterima_dari' => $newName]);

      $status .= '. Nama pada ' . $updated . ' nota terkait juga diperbarui.';
    }

    return redirect()->route('elits-customers.index')->with(['status' => $status]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $customer = Customer::findOrFail($id);
    $customer->delete();

    $permohonan_uji = PermohonanUji::where('customer_id', $id)->delete();

    return redirect()->route('elits-customers.index')->with('status', 'Data berhasil dihapus');
  }
}
