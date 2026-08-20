<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use \Smt\Masterweb\Models\Feedback;
use \Smt\Masterweb\Models\Offer;
use Smt\Masterweb\Models\User;
use Ramsey\Uuid\Uuid;

class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
     public function contact(Request $request)
     {
         # code...
         Feedback::create([
             'name' => $request->name,
             'email' => $request->email,
             'phone' => $request->Telefon,
             'message' => $request->message
         ]);
         return redirect('kontak')->with('status', 'Terimakasih sudah mengirim pesan kepada kami');
     }

     public function offer(Request $request)
     {
         # code...
        //  dd($request);
         $request->validate([
            'nama' => 'required',
            'email' => 'required',
            'nama_proyek' => 'required',
            'deadline' => 'required',
            'info_umum' => 'required'
        ]);

        $data = new Offer;

        $data->nama             = $request->post('nama');
        $data->instansi         = $request->post('instansi');
        $data->email            = $request->post('email');
        $data->nama_proyek      = $request->post('nama_proyek');
        $data->detail_proyek    = $request->post('detail_proyek');
        $data->layanan          = implode(',',$request->post('layanan'));
        $data->deadline         = $request->post('deadline');
        $data->lampiran_url     = $request->post('lampiran_url');
        $data->info_umum        = $request->post('info_umum');
        if($request->file('lampiran_berkas')){
            $file = $request->file('lampiran_berkas');
            $imgName = $file->getClientOriginalName();
            $destinationPath = public_path('/assets/public/images/');
            $file->move($destinationPath, $imgName);

            $data->lampiran_berkas = $imgName;
        }
        $data->save();
        
        return redirect('penawaran')->with('status', 'Terimakasih telah membuat penawaran, Kami akan segera menghubungi anda');
     }

     public function register(Request $request)
    {
        $user = new User;
        //uuid
        $uuid4 = Uuid::uuid4();

        $user->id = $uuid4->toString();
        $user->name = $request->post('name');
        $user->root_firebase = $request->post('root_firebase');
        $user->username = $request->post('username');
        $user->email = $request->get('email');
        $user->phone = $request->post('phone');
        $user->instansi = $request->post('instansi');
        $user->level = '01f62b38-fce5-43bf-9088-9d4a33a496da';
        $user->password = \Hash::make('diawan');
        $user->save();

        return redirect()->route('register')->with(['status'=>'User succesfully inserted','user'=>$user]);
  
    }
    
}
