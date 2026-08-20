<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Contact;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\Client;
use Smt\Masterweb\Models\Device;
use Smt\Masterweb\Models\DeviceField;

class ExportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        //get auth user
        // $data = Contact::where('id','e3e5b586-5846-11ea-a992-646e69cb63c6')->first();
        $user = Auth()->user();
        $users = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get();
        $data = Device::all();
 
        return view('masterweb::module.admin.firebase.report.list',compact('data','users','users'));
        
    }

  
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth()->user();
        $client = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get();
        return view('masterweb::module.admin.firebase.report.add',compact('user','client'));

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
        //insert to report
        $report = new Report();
        $report->user_report = $request->name_report;
        $report->name_report = $request->name_report;
        $report->save();
        foreach ($request->name as $key => $value) {
            $reportdetail = new Reportdetail();
            $reportdetail->report_repdetail = $report->id_report;
            $reportdetail->name_repdetail = $request->name[$key];
            $reportdetail->rumus_repdetail = $request->report[$key];
            $reportdetail->save();
        }
        return redirect('report-management')->withInput(['alert', 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function report($id,$mount,$year)
    {

        $user = Auth()->user();
        $device = Device::where('kode_device',$id)->first();
       
        $client = User::where('id',$device->client_id)->first();
        $clientID= $client->root_firebase;

    

        $data = DeviceField::where('device_id',$id)->orderBy('fields_id_firebase', 'ASC')->get();
        return view('masterweb::module.admin.firebase.report.export',compact('user','id','clientID','data','mount','year','device'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth()->user();
        $client = User::where('level','01f62b38-fce5-43bf-9088-9d4a33a496da')->get();
        $data = Report::find($id);
        return view('masterweb::module.admin.firebase.report.edit',compact('user','client','data'));
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
        $validatedData = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'email' => 'required',
            'phone' => 'required'
        ]);
        
        $data = Contact::find($id);
        $data->nama = $request->post('nama');
        $data->alamat = $request->post('alamat');
        $data->email = $request->post('email');
        $data->phone = $request->post('phone');
        $data->save();
        return redirect()->route('admcontact.index')->with('status', 'Data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();
        $repdetail = Reportdetail::where('report_repdetail',$id);
        $repdetail->delete();
        return redirect()->route('report-management.index')->with('status', 'Data berhasil dihapus');
    }

    public function getClient(Request $request)
    {
        $client = User::where('client_id',$request->client)->get();
        echo $client;
    }
}
