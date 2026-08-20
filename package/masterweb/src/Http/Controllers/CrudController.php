<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CrudController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $datas;
    public $columns;
    public $fileds;
    public $displays;
    public $tables;
    public $set_relation;
    public $upload;
   

    public function get_data($data)
    {
        // if (!empty($data['upload'])) {
        //     $this->upload = $data['upload'];
        // }else{
        //     $this->upload = null;
        // }
       
        // $this->set_relation = $data['join'];
        // $this->datas = DB::table($data['table'])->whereNull('deleted_at')->get();//get tabel
        // $this->columns = Schema::getColumnListing($data['table']); // get colom
        // $this->fileds = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($data['table']); // get type colom
        // if (!empty($data['display'])) {
        //     $this->displays = $data['display'];
        // }else{
        //     $this->displays = null;
        // }
        $set_columns =Schema::getColumnListing($data['table']); // get colom
        $set_datas =  DB::table($data['table'])->whereNull('deleted_at')->get();//get tabel
        $set_fileds =Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($data['table']); // get type colom
      
        $table = $data['table'];
        $relation = $data['join'];
       

        if (!empty($data['display'])) {
            $ds = $data['display'];
        }else{
            $ds = null;
        }
        $set_displays = $ds;

        if (!empty($data['upload'])) {
            $uploads = $data['upload'];
        }else{
            $uploads = null;
        }
        $set_upload = $uploads;

        return view('masterweb::module.admin.crud',compact('set_columns','set_datas','set_fileds','set_displays','table','relation','set_upload'));
        
    }

    ////dilarang
    public function store(Request $request)
    {
        
        // set session
        $table = $request->session()->get('table');
    

        $columns = Schema::getColumnListing($table); // get colom
        $datas = DB::table($table)->get();//get tabel
        $fileds = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($table); // get type colom
        
        $data = array();
        $uuid = Uuid::uuid4();
        $file_name = "";
        $imgName = "";
        
        if($request->file()){
            foreach ($request->file() as $key => $value) {
                $file = $request->file($key);
                
                // Security: Validate file
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $maxSize = 5120; // 5MB in KB
                
                if (!$file->isValid()) {
                    return response()->json(['error' => 'File tidak valid'], 400);
                }
                
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    return response()->json(['error' => 'Tipe file tidak diizinkan'], 400);
                }
                
                if ($file->getSize() > ($maxSize * 1024)) {
                    return response()->json(['error' => 'Ukuran file melebihi batas maksimum (5MB)'], 400);
                }
                
                // Sanitize filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                $imgName = time().'_'.$safeName.'.'.$extension;
                
                $destinationPath = public_path('/assets/admin/file/');
                
                // Ensure directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                $file->move($destinationPath, $imgName);
                $file_name = $key;
            }
        }else{
            $file_name = null;
            $imgName = null;
        }

        foreach ($request->all() as $key => $value) {
            if (in_array($key,array($file_name,'_token','id','deleted_at','created_at','updated_at'))){
                continue;
            }
    
            $data['id'] = $uuid;
            $data['created_at'] = date('Y-m-d H:i:s');
           
           if (!empty($file_name)) {
               $data[$file_name] = $imgName;
              
           }

            $requestName = $request->post($key);
            
            $data[$key] = $requestName;
            
        }

        $simpan = DB::table($table)->insert($data);
        

        if ($simpan) {
            $msg = true;

            // destroy session
            $request->session()->forget('table');
        } else {
            $msg = false;
        }

        return response()->json($msg);
    }

    public function update(Request $request,$id)
    {
         // set session
         $table = $request->session()->get('table');

         $columns = Schema::getColumnListing($table); // get colom
         $datas = DB::table($table)->get();//get tabel
         $fileds = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($table); // get type colom
         
         $data = array();
         $file_name = "";
         $imgName = "";
         
         if($request->file()){
             foreach ($request->file() as $key => $value) {
                 $file = $request->file($key);
                 
                 // Security: Validate file
                 $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                 $maxSize = 5120; // 5MB in KB
                 
                 if (!$file->isValid()) {
                     return response()->json(['error' => 'File tidak valid'], 400);
                 }
                 
                 if (!in_array($file->getMimeType(), $allowedMimes)) {
                     return response()->json(['error' => 'Tipe file tidak diizinkan'], 400);
                 }
                 
                 if ($file->getSize() > ($maxSize * 1024)) {
                     return response()->json(['error' => 'Ukuran file melebihi batas maksimum (5MB)'], 400);
                 }
                 
                 // Sanitize filename
                 $originalName = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension();
                 $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                 $imgName = time().'_'.$safeName.'.'.$extension;
                 
                 $destinationPath = public_path('/assets/admin/file/');
                 
                 // Ensure directory exists
                 if (!file_exists($destinationPath)) {
                     mkdir($destinationPath, 0755, true);
                 }
                 
                 $file->move($destinationPath, $imgName);
                 $file_name = $key;
             }
         }else{
             $file_name = null;
             $imgName = null;
         }
 
         foreach ($request->all() as $key => $value) {
             if (in_array($key,array($file_name,'_token','id','deleted_at','created_at','updated_at'))){
                 continue;
             }
 
             $data['updated_at'] = date('Y-m-d H:i:s');
            
            if (!empty($file_name)) {
                $data[$file_name] = $imgName;
               
            }
 
             $requestName = $request->post($key);
             
             $data[$key] = $requestName;
             
         }
         $simpan = DB::table($table)->where('id',$id)->update($data);
         
 
         if ($simpan) {
             $msg = true;
 
             //destroy session
             $request->session()->forget('table');
         } else {
             $msg = false;
         }
 
         return response()->json($msg);
    }

    public function destroy($id,$table)
    {
        $data = array(
            'deleted_at' => date('Y-m-d H:i:s')
        );
        $simpan = DB::table($table)->where('id',$id)->update($data);

        if ($simpan) {
            $msg = true;
        } else {
            $msg = false;
        }

        return response()->json($msg);
    }
}
