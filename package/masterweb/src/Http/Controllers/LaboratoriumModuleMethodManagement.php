<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;


use \Smt\Masterweb\Models\Module\FormA;

use \Smt\Masterweb\Models\Module\FormB;
use \Smt\Masterweb\Models\Module\FormC;
use \Smt\Masterweb\Models\Module\FormD;

use \Smt\Masterweb\Models\Result\FormAResult;
use \Smt\Masterweb\Models\Result\FormBResult;
use \Smt\Masterweb\Models\Result\FormCResult;




use Carbon\Carbon;


class LaboratoriumModuleMethodManagement extends Controller{

    public function __construct()
	{
		$this->middleware('auth');
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function formA(Request $request,$id,$id_samples){
        $data =$request->all();

       
        
      
        $user = Auth()->user();

        $formA= FormA::where('id_samples', $id_samples)->where('id_method', $id)->first();
        if($formA==null){
            $formA = new FormA;
            $uuid4 = Uuid::uuid4();
            
            $formA->id=$uuid4;
            $formA->id_samples = $id_samples;
            $formA->id_method=$id;
         
            $formA->rpd = $request->post('rpd_'.$id);
            $formA->hasil = $request->post('hasil_'.$id);
            $formA->save();
            

        }else{
            $formA->id_samples = $id_samples;
            $formA->id_method=$id;
            $formA->rpd = $request->post('rpd_'.$id);
            $formA->hasil = $request->post('hasil_'.$id);
            $formA->save();

            $formAResult = FormAResult::where('id_FormA', $formA->id)->delete();
        }
        
       

        for($i=0;$i<3;$i++){
            $formAResult =new FormAResult;
            $uuid4 = Uuid::uuid4();
            $formAResult->id=$uuid4;
            $formAResult->id_FormA=$formA->id;
            $formAResult->date_test =  Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$i.'_'.$id))->format('Y-m-d H:i:s');
            $formAResult->crm_iot= $request->post('crm_iot_'.$i.'_'.$id);
            $formAResult->konsentrasi_1 = $request->post('konsentrasi_1_'.$i.'_'.$id);
            $formAResult->konsentrasi_2 = $request->post('konsentrasi_2_'.$i.'_'.$id);
            $formAResult->order = $i;
            $formAResult->save();

            
        }

        
        
   
        return response()->json(
            [
                'success'=>'Form Berhasil Tersimpan',
                'data'=>$data,
            ]);

        // return redirect()->route('elits-samples.analys',["id"=>$id_samples,"id_method"=>$id])->with('status', 'Data berhasil dihapus');
    }


    public function formB(Request $request,$id,$id_samples){
        $data =$request->all();
        
        // print_r($data);
         
      
        $user = Auth()->user();

        $formB= FormB::where('id_samples', $id_samples)->where('id_method', $id)->first();
        if($formB==null){
            $formB = new FormB;
            $uuid4 = Uuid::uuid4();
            
            $formB->id=$uuid4;
            $formB->id_samples = $id_samples;
            $formB->id_method=$id;
            $formB->crm_iot = $request->post('crm_iot_'.$id);
            $formB->standarisation = $request->post('standarisation_'.$id);
           
            $formB->hasil = $request->post('hasil_'.$id);

            $formB->save();
        }else{
            $formB->crm_iot = $request->post('crm_iot_'.$id);
            $formB->standarisation = $request->post('standarisation_'.$id);
           
            $formB->hasil = $request->post('hasil_'.$id);
            $formB->save();
            $formBResult = FormBResult::where('id_FormB', $formB->id)->delete();
        }

        for($i=0;$i<3;$i++){
            $formBResult =new FormBResult;
            $uuid4 = Uuid::uuid4();
            $formBResult->id=$uuid4;
            $formBResult->id_FormB=$formB->id;
            $formBResult->date_test = Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$i.'_'.$id))->format('Y-m-d H:i:s');
            
            $formBResult->blanko_titration1 = $request->post('blanko_titration1_'.$i.'_'.$id);
            $formBResult->blanko_titration2 = $request->post('blanko_titration2_'.$i.'_'.$id);
            $formBResult->blanko_DO_0 = $request->post('blanko_DO_0_'.$i.'_'.$id);
            $formBResult->blanko_DO_5 = $request->post('blanko_DO_5_'.$i.'_'.$id);
            $formBResult->sample_titration1 = $request->post('sample_titration1_'.$i.'_'.$id);
            $formBResult->sample_titration2 = $request->post('sample_titration2_'.$i.'_'.$id);
            $formBResult->sample_DO_0 = $request->post('sample_DO_0_'.$i.'_'.$id);
            $formBResult->sample_DO_5 = $request->post('sample_DO_5_'.$i.'_'.$id);
            $formBResult->volume_bakteri = $request->post('volume_bakteri_'.$i.'_'.$id);
            $formBResult->volume_sample = $request->post('volume_sample_'.$i.'_'.$id);
            $formBResult->volume_botol = $request->post('volume_botol_'.$i.'_'.$id);
            $formBResult->order = $i;
            $formBResult->save();
        } 
        return response()->json(
            [
                'success'=>'Form Berhasil Tersimpan',
                'data'=>$data,
            ]);
        //return redirect()->route('elits-samples.analys',["id"=>$id_samples,"id_method"=>$id])->with('status', 'Data berhasil dihapus');
    }

    public function formC(Request $request,$id,$id_samples){
        $data =$request->all();
        
        // print_r($data);
         
      
        $user = Auth()->user();

        $formC= FormC::where('id_samples', $id_samples)->where('id_method', $id)->first();
        if($formC==null){
            $formC = new FormC;
            $uuid4 = Uuid::uuid4();

          
  
            $formC->id=$uuid4;
            $formC->id_samples = $id_samples;
            $formC->id_method=$id;
            $formC->crm_iot = $request->post('crm_iot_'.$id);
            $formC->date_test = Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$id))->format('Y-m-d H:i:s');
            $formC->nilai = $request->post('nilai_'.$id);

            $formC->simplo_K₂Cr₂O₇ = $request->post('simplo_K₂Cr₂O₇_'.$id);
            $formC->simplo_FAS = $request->post('simplo_FAS_'.$id);
            $formC->simplo_akuades = $request->post('simplo_akuades_'.$id);
            $formC->simplo_MFAS = $request->post('simplo_MFAS_'.$id);
            
            $formC->duplo_K₂Cr₂O₇ = $request->post('duplo_K₂Cr₂O₇_'.$id);
            $formC->duplo_akuades = $request->post('duplo_akuades_'.$id);
            $formC->duplo_FAS = $request->post('duplo_FAS_'.$id);
            $formC->duplo_MFAS = $request->post('duplo_MFAS_'.$id);

          
            $formC->hasil_MFAS = $request->post('hasil_MFAS_'.$id);
            $formC->bias_MFAS = $request->post('bias_MFAS_'.$id);
            
            
            $formC->vol_blanko = $request->post('vol_blanko_'.$id);
            $formC->rpd = $request->post('rpd_'.$id);

            if($request->post('is_r_'.$id)=="true"){
                $formC->is_r = 1;
            }else{
                $formC->is_r = 0;
            }
           
            if($formC->is_r){
                $formC->r = $request->post('r_'.$id);
                $formC->spike = $request->post('spike_'.$id);
            }

            if($request->post('is_fp_'.$id)=="true"){
                $formC->is_fp = 1;
            }else{
                $formC->is_fp = 0;
            }

            if($formC->is_fp){
                $formC->fp_operator = $request->post('fp_operator_'.$id);
                $formC->fp_value = $request->post('fp_value_'.$id);
            }

            $formC->hasil = $request->post('hasil_'.$id);


            $formC->hasil_akhir = $request->post('hasil_akhir_'.$id);

            $formC->save();
        }else{
            $formC->crm_iot = $request->post('crm_iot_'.$id);
            $formC->date_test = Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$id))->format('Y-m-d H:i:s');
            $formC->nilai = $request->post('nilai_'.$id);
            $formC->simplo_K₂Cr₂O₇ = $request->post('simplo_K₂Cr₂O₇_'.$id);
            $formC->simplo_FAS = $request->post('simplo_FAS_'.$id);
            $formC->simplo_akuades = $request->post('simplo_akuades_'.$id);
            $formC->simplo_MFAS = $request->post('simplo_MFAS_'.$id);
            
            $formC->duplo_K₂Cr₂O₇ = $request->post('duplo_K₂Cr₂O₇_'.$id);
            $formC->duplo_akuades = $request->post('duplo_akuades_'.$id);
            $formC->duplo_FAS = $request->post('duplo_FAS_'.$id);
            $formC->duplo_MFAS = $request->post('duplo_MFAS_'.$id);
            
            
            $formC->hasil_MFAS = $request->post('hasil_MFAS_'.$id);
            $formC->bias_MFAS = $request->post('bias_MFAS_'.$id);
            
            
            $formC->vol_blanko = $request->post('vol_blanko_'.$id);
            $formC->rpd = $request->post('rpd_'.$id);


            if($request->post('is_r_'.$id)=="true"){
                
                $formC->is_r = 1;
            }else{
                $formC->is_r = 0;
            }
           
            if($formC->is_r){
                $formC->r = $request->post('r_'.$id);
                $formC->spike = $request->post('spike_'.$id);
            }

            
            if($request->post('is_fp_'.$id)=="true"){
                $formC->is_fp = 1;
            }else{
                $formC->is_fp = 0;
            }

            if($formC->is_fp){
                $formC->fp_operator = $request->post('fp_operator_'.$id);
                $formC->fp_value = $request->post('fp_value_'.$id);
            }

            $formC->hasil = $request->post('hasil_'.$id);


            $formC->hasil_akhir = $request->post('hasil_akhir_'.$id);

            $formC->save();
            $formCResult = FormCResult::where('id_FormC', $formC->id)->delete();
        }

        for($i=0;$i<3;$i++){
            $formCResult =new FormCResult;
            $uuid4 = Uuid::uuid4();
            $formCResult->id=$uuid4;
            $formCResult->id_FormC=$formC->id;
            
            $formCResult->kadar_COD = $request->post('kadar_COD_'.$i.'_'.$id);
            $formCResult->vol_sample = $request->post('vol_sample_'.$i.'_'.$id);
            $formCResult->vol_FAS_sample = $request->post('vol_FAS_sample_'.$i.'_'.$id);
            $formCResult->order = $i;
            $formCResult->save();
        } 
        return response()->json(
            [
                'success'=>'Form Berhasil Tersimpan',
                'data'=>$data,
            ]);
        //return redirect()->route('elits-samples.analys',["id"=>$id_samples,"id_method"=>$id])->with('status', 'Data berhasil dihapus');
    }

    public function formD(Request $request,$id,$id_samples){
        $data =$request->all();
        
        // print_r($data);
         
      
        $user = Auth()->user();

        $formD= FormD::where('id_samples', $id_samples)->where('id_method', $id)->first();
        if($formD==null){
            $formD = new FormD;
            $uuid4 = Uuid::uuid4();

          

            $formD->id_samples = $id_samples;
            $formD->id_method=$id;
            $formD->date_test =  Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$id))->format('Y-m-d H:i:s');
            $formD->molaritas_CaCO₃_standarisation = $request->post('molaritas_CaCO₃_standarisation_'.$id);
            $formD->volume_CaCO₃_standarisation = $request->post('volume_CaCO₃_standarisation_'.$id);
            $formD->volume_EDTA_standarisation = $request->post('volume_EDTA_standarisation_'.$id);
            $formD->molaritas_EDTA_standarisation = $request->post('molaritas_EDTA_standarisation_'.$id);
            $formD->volume_sampel = $request->post('volume_sampel_'.$id);
            $formD->volume_EDTA_sampel = $request->post('volume_EDTA_sampel_'.$id);
            $formD->hasil = $request->post('hasil_'.$id);
            $formD->recovery = $request->post('recovery_'.$id);
            $formD->rpd = $request->post('rpd_'.$id);

            $formD->save();
        }else{
            $formD->date_test =  Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$id))->format('Y-m-d H:i:s');
            $formD->molaritas_CaCO₃_standarisation = $request->post('molaritas_CaCO₃_standarisation_'.$id);
            $formD->volume_CaCO₃_standarisation = $request->post('volume_CaCO₃_standarisation_'.$id);
            $formD->volume_EDTA_standarisation = $request->post('volume_EDTA_standarisation_'.$id);
            $formD->molaritas_EDTA_standarisation = $request->post('molaritas_EDTA_standarisation_'.$id);
            $formD->volume_sampel = $request->post('volume_sampel_'.$id);
            $formD->volume_EDTA_sampel = $request->post('volume_EDTA_sampel_'.$id);
            $formD->hasil = $request->post('hasil_'.$id);
            $formD->recovery = $request->post('recovery_'.$id);
            $formD->rpd = $request->post('rpd_'.$id);
            $formD->save();
        }
        
   
        return redirect()->route('elits-samples.analys',["id"=>$id_samples,"id_method"=>$id])->with('status', 'Data berhasil dihapus');
    }

    public function formE(Request $request,$id,$id_samples){
        $data =$request->all();
        
        // print_r($data);
         
      
        $user = Auth()->user();

        $formD= FormD::where('id_samples', $id_samples)->where('id_method', $id)->first();
        if($formD==null){
            $formD = new FormD;
            $uuid4 = Uuid::uuid4();

          

            $formD->id_samples = $id_samples;
            $formD->id_method=$id;
            $formD->date_test =  Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$id))->format('Y-m-d H:i:s');
            $formD->molaritas_CaCO₃_standarisation = $request->post('molaritas_CaCO₃_standarisation_'.$id);
            $formD->volume_CaCO₃_standarisation = $request->post('volume_CaCO₃_standarisation_'.$id);
            $formD->volume_EDTA_standarisation = $request->post('volume_EDTA_standarisation_'.$id);
            $formD->molaritas_EDTA_standarisation = $request->post('molaritas_EDTA_standarisation_'.$id);
            $formD->volume_sampel = $request->post('volume_sampel_'.$id);
            $formD->volume_EDTA_sampel = $request->post('volume_EDTA_sampel_'.$id);
            $formD->hasil = $request->post('hasil_'.$id);
            $formD->recovery = $request->post('recovery_'.$id);
            $formD->rpd = $request->post('rpd_'.$id);

            $formD->save();
        }else{
            $formD->date_test =  Carbon::createFromFormat('d/m/Y',  $request->post('tanggal_uji_'.$id))->format('Y-m-d H:i:s');
            $formD->molaritas_CaCO₃_standarisation = $request->post('molaritas_CaCO₃_standarisation_'.$id);
            $formD->volume_CaCO₃_standarisation = $request->post('volume_CaCO₃_standarisation_'.$id);
            $formD->volume_EDTA_standarisation = $request->post('volume_EDTA_standarisation_'.$id);
            $formD->molaritas_EDTA_standarisation = $request->post('molaritas_EDTA_standarisation_'.$id);
            $formD->volume_sampel = $request->post('volume_sampel_'.$id);
            $formD->volume_EDTA_sampel = $request->post('volume_EDTA_sampel_'.$id);
            $formD->hasil = $request->post('hasil_'.$id);
            $formD->recovery = $request->post('recovery_'.$id);
            $formD->rpd = $request->post('rpd_'.$id);
            $formD->save();
        }
        
   
        return redirect()->route('elits-samples.analys',["id"=>$id_samples,"id_method"=>$id])->with('status', 'Data berhasil dihapus');
    }


}
