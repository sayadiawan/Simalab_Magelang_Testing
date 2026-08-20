
@php
if($form!=null){
    ${"tgl_uji_". $id}= Carbon\Carbon::createFromFormat('Y-m-d H:i:s',  $form->date_test)->format('d/m/Y');
    ${"crm_iot_". $id}=$form->crm_iot;
    ${"nilai_". $id}=$form->nilai;
    ${"simplo_K₂Cr₂O₇_". $id}=$form->simplo_K₂Cr₂O₇;
    ${"duplo_K₂Cr₂O₇_". $id}=$form->duplo_K₂Cr₂O₇;
    ${"simplo_akuades_". $id}=$form->simplo_akuades;
    ${"duplo_akuades_". $id}=$form->duplo_akuades;

    ${"simplo_FAS_". $id}=$form->simplo_FAS;
    ${"duplo_FAS_". $id}=$form->duplo_FAS;
    ${"simplo_MFAS_". $id}=$form->simplo_MFAS;
    ${"duplo_MFAS_". $id}=$form->duplo_MFAS;
    ${"hasil_MFAS_". $id}=$form->hasil_MFAS;

    ${"bias_MFAS_". $id}=$form->bias_MFAS;
    ${"vol_blanko_". $id}=$form->vol_blanko;

    ${"r_" . $id} =$form->r;
    ${"is_r_" . $id} =$form->is_r;
    ${"spike_" . $id} =$form->r;
    ${"rpd_" . $id} =$form->rpd;
    ${"hasil_" . $id} =$form->hasil;


    ${"is_fp_" . $id} =$form->is_fp;

    ${"fp_operator_" . $id} =$form->fp_operator;

    ${"fp_value_" . $id} =$form->fp_value;
    ${"hasil_akhir_" . $id} =$form->hasil_akhir;

}else{
    $now = Carbon\Carbon::now()->format('d/m/Y');
    ${"tgl_uji_". $id}= $now;
    ${"crm_iot_". $id}='';
    ${"nilai_". $id}='215';
    ${"simplo_K₂Cr₂O₇_". $id}='5';
    ${"duplo_K₂Cr₂O₇_". $id}='5';
    ${"simplo_akuades_". $id}='2.5';
    ${"duplo_akuades_". $id}='2.5';
    ${"simplo_FAS_". $id}='10.32';
    ${"duplo_FAS_". $id}='10.32';
    ${"simplo_MFAS_". $id}='0.04844961240310077';
    ${"duplo_MFAS_". $id}='0.04844961240310077';
    ${"hasil_MFAS_". $id}='0.04844961240310077';
    ${"bias_MFAS_". $id}='3.1007751937984636';
    ${"vol_blanko_". $id}='';
    ${"is_r_" . $id} =false;
    ${"r_" . $id} ='';
    ${"rpd_" . $id} ='';
    ${"hasil_" . $id} ='';
    ${"spike_" . $id} ='';

    ${"is_fp_" . $id} =false;
    
    ${"fp_operator_" . $id} ='x';
    ${"fp_value_" . $id} ='';
    ${"hasil_akhir_" . $id} ='';
      
}
@endphp
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
var vol_sample=[],
vol_FAS_sample=[],
kadar_COD=[],

simplo_K2Cr2O7=5,
duplo_K2Cr2O7=5,
simplo_akuades=2.5,
duplo_akuades=2.5,
simplo_FAS=10.32,
duplo_FAS=10.32,
simplo_MFAS=0.04844961240310077,
duplo_MFAS=0.04844961240310077,
hasil_MFAS=0.04844961240310077,
bias_MFAS=3.1007751937984636,
vol_blanko=0,
is_r =false,
r =0,
rpd =0,
hasil=0,
is_fp=false,
fp_operator='x',
fp_value =0,
hasil_akhir =0;
</script>
<style>
    .carousel-indicators .active {
        background-color: #0C4061;
    }
    .carousel-indicators li{
        background-color: #5eb1ec;
        width: 30px;
        height: 3px;
        text-indent: -999px;    
    }
    .carousel-indicators {
        position: relative;   
    }
</style>

<div class="container">



      

    <div id="carouselExampleIndicators-{{$id}}" class="carousel slide" data-ride="carousel">
        

        <!-- <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-module-methods.formB', ['id' => Request::segment(3),'id_samples' => Request::segment(4)])}}"  method="POST">
            @csrf -->


            <div class="form-group">
                <label for="tanggal_uji">Tanggal Uji</label>
                <div class="input-group date">
                    <input type="text" class="form-control tanggal_uji_{{$id}}" name="tanggal_uji_{{$id}}" id="tanggal_uji_{{$id}}" value="<?php echo ${'tgl_uji_'.$id};?>"  placeholder="Isikan Tanggal Uji" data-date-format="dd/mm/yyyy" required>
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                </div>
            </div>
    
    
          

            <div class="form-group">
                <label for="crm_iot_{{$id}}">CRM - ERA lot</label>
                <input type="text" class="form-control" id="crm_iot_{{$id}}" name="crm_iot_{{$id}}" value="<?php echo ${'crm_iot_'.$id};?>" placeholder="CRM - ERA lot" >
            </div>

            <div class="form-group">
                <label for="nilai_{{$id}}">Nilai</label>
                <input type="number" class="form-control" id="nilai_{{$id}}" name="nilai_{{$id}}" step="any" value="<?php echo ${'nilai_'.$id};?>" placeholder="Masukkan Nilai" >
            </div>

            <div class="form-group">
                <div class="card">
                   
                  
                    <div class="card-body">
                        <h3 class="card-title"><b>Standarisasi FAS 0,05 N</b></h3>
                        <div class="content">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                Simplo
                                            </div>
                                            <div class="card-body">
                                                <div class="content">
                                                    <div class="container-fluid">
                                                        
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <label for="simplo_K₂Cr₂O₇_{{$id}}">Vol. K₂Cr₂O₇ (ml)</label>
                                                                    <input type="number" class="form-control" id="simplo_K₂Cr₂O₇_{{$id}}" step="any" name="simplo_K₂Cr₂O₇_{{$id}}" value="<?php echo ${'simplo_K₂Cr₂O₇_'.$id};?>" placeholder="Simplo K₂Cr₂O₇" >
                                                                
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="simplo_akuades_{{$id}}">Vol. Akuades  (ml)</label>
                                                                    <input type="number" class="form-control" id="simplo_akuades_{{$id}}" step="any" name="simplo_akuades_{{$id}}" value="<?php echo ${'simplo_akuades_'.$id};?>" placeholder="Simplo Akuades" >
                                                                    
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="simplo_FAS_{{$id}}">Vol. FAS (ml)</label>
                                                                    <input type="number" class="form-control" id="simplo_FAS_{{$id}}" step="any" name="simplo_FAS_{{$id}}" value="<?php echo ${'simplo_FAS_'.$id};?>" placeholder="Simplo FAS" >
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="simplo_MFAS_{{$id}}">M FAS</label>
                                                                    <input type="number" class="form-control" id="simplo_MFAS_{{$id}}" step="any" name="simplo_MFAS_{{$id}}" value="<?php echo ${'simplo_MFAS_'.$id};?>" placeholder="Simplo MFAS" readonly>
                                                                </div>
                                                            </div>

                                                        

                                                        

                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                                </div>

                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                Duplo
                                            </div>
                                            <div class="card-body">
                                                <div class="content">
                                                    <div class="container-fluid">
                                                        
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <label for="duplo_K₂Cr₂O₇_{{$id}}">Vol. K₂Cr₂O₇ (ml)</label>
                                                                    <input type="number" class="form-control" id="duplo_K₂Cr₂O₇_{{$id}}" step="any" name="duplo_K₂Cr₂O₇_{{$id}}" value="<?php echo ${'duplo_K₂Cr₂O₇_'.$id};?>" placeholder="Duplo K₂Cr₂O₇ (ml)" >
                                                                
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="duplo_akuades_{{$id}}">Vol. Akuades (ml)</label>
                                                                    <input type="number" class="form-control" id="duplo_akuades_{{$id}}" step="any" name="duplo_akuades_{{$id}}" value="<?php echo ${'duplo_akuades_'.$id};?>" placeholder="Duplo Akuades" >
                                                                    
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="duplo_FAS_{{$id}}">Vol. FAS (ml)</label>
                                                                    <input type="number" class="form-control" id="duplo_FAS_{{$id}}" step="any" name="duplo_FAS_{{$id}}" value="<?php echo ${'duplo_FAS_'.$id};?>" placeholder="Duplo FAS" >
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label for="duplo_MFAS_{{$id}}">M FAS</label>
                                                                    <input type="number" class="form-control" id="duplo_MFAS_{{$id}}" step="any" name="duplo_MFAS_{{$id}}" value="<?php echo ${'duplo_MFAS_'.$id};?>" placeholder="Simplo MFAS" readonly>
                                                                </div>
                                                            </div>

                                                        

                                                        

                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                                </div>
                                
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                Hasil Standarisasi
                                            </div>
                                            <div class="card-body">
                                                <div class="content">
                                                    <div class="container-fluid">
                                                        
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <label for="hasil_MFAS_{{$id}}">Hasil (M)</label>
                                                                    <input type="number" class="form-control" id="hasil_MFAS_{{$id}}" step="any" name="hasil_MFAS_{{$id}}" value="<?php echo ${'hasil_MFAS_'.$id};?>" placeholder="Hasil MFAS" readonly >
                                                                
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="bias_MFAS_{{$id}}">Bias (%)</label>
                                                                    <input type="number" class="form-control" id="bias_MFAS_{{$id}}" step="any" name="bias_MFAS_{{$id}}" value="<?php echo ${'bias_MFAS_'.$id};?>" placeholder="Bias MFAS %" readonly>
                                                                    
                                                                </div>
                                                               
                                                               
                                                            </div>

                                                        

                                                        

                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                                </div>
                                

                            </div>
                        </div>
                            
                    </div>
                </div>
            </div>

            

            <div class="form-group">
                    <div class="card">
                    
                    
                    <div class="card-body">
                        <h3 class="card-title"><b>Pengujian COD</b></h3>


                        <div class="container-fluid">                                                        
                            <div class="row">
                                <div class="col-md-3">
                                
                                <label for="vol_blanko_{{$id}}">Vol. Blanko (ml) </label>
                                        
                                    
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" id="vol_blanko_{{$id}}" step="any" name="vol_blanko_{{$id}}" value="<?php echo ${'vol_blanko_'.$id};?>" placeholder="Volume Blanko" >                            
                                </div>
                            
                                <div class="col-md-4">
                                </div>
                            </div>
                        </div>

                        <br>

                        <div class="carousel-inner">

                            @for ($i = 0; $i < 3; $i++)
                                @php
                                if ($i == 0){
                                    $status="active";
                                }else{
                                    $status="";
                                }
                                @endphp


                                <div class="carousel-item {{$status}}">

                                    <div class="content">
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            Sample
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="content">
                                                                <div class="container-fluid">
                                                                    
                                                                        <div class="row">
                                                                            <div class="col-md-4">
                                                                                <label for="vol_sample_{{$i}}_{{$id}}">Vol. Sampel (ml)</label>
                                                                                <input type="number" class="form-control" id="vol_sample_{{$i}}_{{$id}}" step="any" name="vol_sample_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"vol_sample",$i);?>" placeholder="Vol. Sample" >
                                                                            
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <label for="vol_FAS_sample_{{$i}}_{{$id}}">Vol. FAS (ml)</label>
                                                                                <input type="number" class="form-control" id="vol_FAS_sample_{{$i}}_{{$id}}" step="any" name="vol_FAS_sample_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"vol_FAS_sample",$i);?>" placeholder="Vol. FAS Sample" >
                                                                                
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <label for="kadar_COD_{{$i}}_{{$id}}">Kadar COD (mg/L)</label>
                                                                                <input type="number" class="form-control" id="kadar_COD_{{$i}}_{{$id}}" step="any" name="kadar_COD_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"kadar_COD",$i);?>" placeholder="Kadar COD"  readonly>
                                                                            </div>

                                                                        </div>

                                                                    

                                                                    

                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            
                                            </div>

                                        
                                            
                                        
                                            

                                        </div>
                                    </div>
                                    
                                </div>
                            @endfor

                        </div>
                        <br>
                        <div class="content">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="container">
                                                <ol class="carousel-indicators">
                                                @for ($i = 0; $i < 3; $i++)
                                                    @php
                                                    if ($i == 0){
                                                        $status="active";
                                                    }else{
                                                        $status="";
                                                    }
                                                    @endphp
                                                    <li data-target="#carouselExampleIndicators-{{$id}}" data-slide-to="{{$i}}" class="{{$status}}"></li>
                                                    
                                                @endfor
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    
            
            
            </div>

         

            <div class="form-group">
                <div class="card">
                    <div class="card-header">
                        Hasil
                    </div>
                    <div class="card-body">
                        <div class="content">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="hasil_{{$id}}">Hasil (mg/L)</label>
                                        <input type="number" class="form-control" id="hasil_{{$id}}" step="any" name="hasil_{{$id}}"value="<?php echo ${'hasil_'.$id};?>" placeholder="Hasil (mg/L)" readonly>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="rpd_{{$id}}">% RPD</label>
                                        <input type="number" class="form-control" id="rpd_{{$id}}" step="any" name="rpd_{{$id}}" value="<?php echo ${'rpd_'.$id};?>" placeholder="% RPD" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="r_{{$id}}"><input type="checkbox" id="is_r" class="is_r" name="is_r" data-toggle="toggle" <?php echo (${'is_r_'.$id}==true)?"checked":"";?>> Menggunakan %R</label>
                                        <div id="r_container">
                                            <input type="number" class="form-control" id="spike_{{$id}}" step="any" name="spike_{{$id}}" value="<?php echo ${'spike_'.$id};?>" placeholder="Masukkan Spike">
                                            <br>
                                            <input type="number" class="form-control" id="r_{{$id}}" step="any" name="r_{{$id}}" value="<?php echo ${'r_'.$id};?>" placeholder="% R" readonly >
                                        </div>
                                        <script>
                                            if("<?php echo ${'is_r_'.$id};?>"!="1"){
                                                $('#r_container').hide();
                                            }else{
                                                $('#r_container').show();
                                            }
                                           
                                        </script>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <div class="form-row align-items-center col-md-12"  id="fields-relay-1">
                  <div class="col-md-12" id="condition">
                  
                        <input type="checkbox" id="is_fp" class="is_fp" name="is_fp" data-toggle="toggle"  <?php echo (${'is_fp_'.$id}==true)?"checked":"";?>> Menggunakan FP
                        
                  </div>
                </div>
            </div>
        


            <div class="form-group" id="fp_container">
                <div class="card">
                    <div class="card-header">
                        FP
                    </div>
                    <div class="card-body">
                        <div class="content">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="hasil2_{{$id}}">Hasil (mg/L)</label>
                                        <input type="number" class="form-control" id="hasil2_{{$id}}" step="any" name="hasil2_{{$id}}"value="<?php echo ${'hasil_'.$id};?>" placeholder="Hasil (mg/L)" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="fp_operator_{{$id}}">Operator FP</label>
                                        <select class="form-select form-control" id="fp_operator_{{$id}}" aria-label="Operator FP">
                                            <option>Pilih Operator FP</option>
                                            <option value="x" <?php echo (${'fp_operator_'.$id}=="x")?"selected":"";?>>x</option>
                                            <option value="/" <?php echo (${'fp_operator_'.$id}=="/")?"selected":"";?>>/</option>
                                            <option value="+" <?php echo (${'fp_operator_'.$id}=="+")?"selected":"";?>>+</option>
                                            <option value="-" <?php echo (${'fp_operator_'.$id}=="-")?"selected":"";?>>-</option>
                                        </select>
                                       
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="fp_value_{{$id}}">Nilai FP</label>
                                        <input type="number" class="form-control" id="fp_value_{{$id}}" step="any" name="fp_value_{{$id}}" value="<?php echo ${'fp_value_'.$id};?>" placeholder="FP Value" >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            
                if("<?php echo ${'is_fp_'.$id};?>"!="1"){
                  
                    $('#fp_container').hide();
                }else{
                    $('#fp_container').show();
                }
                                           
            </script>

            <div class="form-group">
                <label for="hasil_akhir_{{$id}}">Hasil Akhir</label>
                <input type="number" class="form-control" id="hasil_akhir_{{$id}}" name="hasil_akhir_{{$id}}" step="any" value="<?php echo ${'hasil_akhir_'.$id};?>" placeholder="Hasil Akhir" readonly>
            </div>




           



            <button type="submit" class="btn btn-primary mr-2" id="save_{{$id}}">Simpan</button>
        <!-- </form> -->
        

    </div>

    <script>

            $('.tanggal_uji_{{$id}}').datepicker({
                format: 'dd/mm/yyyy'
            });

        function getValue(id) {
            var valueText=$("#"+id).val()
        
            if(valueText==''){
                return 0;
            }else{
                return parseFloat(valueText);
            }
        }

        

        simplo_K2Cr2O7=getValue("simplo_K₂Cr₂O₇_{{$id}}")
        simplo_FAS=getValue("simplo_FAS_{{$id}}")
        simplo_MFAS=getValue("simplo_MFAS_{{$id}}")
        
        duplo_K2Cr2O7=getValue("duplo_K₂Cr₂O₇_{{$id}}")
        duplo_FAS=getValue("duplo_FAS_{{$id}}")
        duplo_MFAS=getValue("duplo_MFAS_{{$id}}")
        
        hasil_MFAS=getValue("hasil_MFAS_{{$id}}")
        bias_MFAS=getValue("bias_MFAS_{{$id}}")
        vol_blanko=getValue("vol_blanko_{{$id}}")
        
        r =getValue("r_{{$id}}")
        
        rpd =getValue("rpd_{{$id}}")
        hasil=getValue("hasil_{{$id}}")

        hasil_akhir =getValue("hasil_akhir_{{$id}}")

        function setHasilStandard() {
            if(simplo_MFAS!=0&&duplo_MFAS!=0){
                hasil_MFAS=(simplo_MFAS+duplo_MFAS)/2;
                bias_MFAS =((0.05-hasil_MFAS)/0.05)*100
                $("#hasil_MFAS_{{$id}}").val(hasil_MFAS)
                $("#bias_MFAS_{{$id}}").val(bias_MFAS)
                setKadarCODSampleAll()
               
            }
        }



        function setKadarCODSampleAll() {
            if(hasil_MFAS!=0&&vol_blanko!=0 ){
                
                for(var i=0;i<3;i++){
                    if(vol_sample[i]!=0 && vol_FAS_sample[i]!=0){
                        kadar_COD[i]=((vol_blanko-vol_FAS_sample[i])*hasil_MFAS*8000)/2.5
                        $("#kadar_COD_"+i+"_{{$id}}").val(kadar_COD[i])
                        
                    }
                  
                }
                setHasil()
               
            }
        }

        function setKadarCODSampleID(i) {
            if(hasil_MFAS!=0&&vol_blanko!=0 ){
                
                if(vol_sample[i]!=0 && vol_FAS_sample[i]!=0){
                    kadar_COD[i]=((vol_blanko-vol_FAS_sample[i])*hasil_MFAS*8000)/2.5
                    
                    $("#kadar_COD_"+i+"_{{$id}}").val(kadar_COD[i])
                    setHasil()
                    
                }

               
            }
        }

        function setHasil() {
            var totalhasil=0,filled=0,total_r=0;

           var temp=0;
            for(var i=0;i<3;i++){
                if(kadar_COD[i]!=0){
                    if(temp!=0){
                        if(total_r==0){
                            total_r=Math.abs(kadar_COD[i]-temp)
                        }else{
                            total_r=(total_r+(Math.abs(kadar_COD[i]-temp)))/2
                           
                        }
                    }
                    
                    
                    temp=kadar_COD[i];

                    totalhasil=totalhasil+kadar_COD[i]
                    filled++;
                }
            }
            hasil=totalhasil/filled
            $("#hasil_{{$id}}").val(hasil)
            $("#hasil2_{{$id}}").val(hasil)

            if(filled>1){
               
                rpd=(total_r)/hasil*100
                $("#rpd_{{$id}}").val(rpd)
            }

            if(is_r &&  spike!=0){
                r=( hasil/spike)*100
                $("#r_{{$id}}").val(r)
            }

            if(!is_fp){
                hasil_akhir=hasil
                $("#hasil_akhir_{{$id}}").val(hasil_akhir)
            }else{
                if(fp_value!=0){
                    fp_operator=$('#fp_operator_{{$id}}').find(":selected").val()
                    if(fp_operator=='x'){
                        hasil_akhir=hasil*fp_value
                    }else if(fp_operator=='/'){
                        hasil_akhir=hasil/fp_value
                    }else if(fp_operator=='+'){
                        hasil_akhir=hasil+fp_value
                    }else if(fp_operator=='-'){
                        hasil_akhir=hasil-fp_value
                    }else{
                        hasil_akhir=hasil*fp_value
                    }
                    
                     $("#hasil_akhir_{{$id}}").val(hasil_akhir)
                }
            }

        }


        $("#simplo_K₂Cr₂O₇_{{$id}}").on('input', function() {
            simplo_K2Cr2O7=parseFloat($("#simplo_K₂Cr₂O₇_{{$id}}").val())
            if(simplo_K2Cr2O7!=0 && simplo_FAS!=0){
                simplo_MFAS=(simplo_K2Cr2O7*0.1)/simplo_FAS
               $("#simplo_MFAS_{{$id}}").val(simplo_MFAS)

                setHasilStandard()
            }


           
        })

     
        $("#simplo_FAS_{{$id}}").on('input', function() {
            simplo_FAS=parseFloat($("#simplo_FAS_{{$id}}").val())
            if(simplo_K2Cr2O7!=0 && simplo_FAS!=0){
                simplo_MFAS=(simplo_K2Cr2O7*0.1)/simplo_FAS
               $("#simplo_MFAS_{{$id}}").val(simplo_MFAS)
               setHasilStandard()
            }
          
        })

        $("#duplo_K₂Cr₂O₇_{{$id}}").on('input', function() {
            duplo_K2Cr2O7=parseFloat($("#duplo_K₂Cr₂O₇_{{$id}}").val())
            if(duplo_K2Cr2O7!=0 && duplo_FAS!=0){
                duplo_MFAS=(duplo_K2Cr2O7*0.1)/duplo_FAS
               $("#duplo_MFAS_{{$id}}").val(duplo_MFAS)
               setHasilStandard()
            }
          
        })


        $("#duplo_FAS_{{$id}}").on('input', function() {
            duplo_FAS=parseFloat($("#duplo_FAS_{{$id}}").val())
            if(duplo_K2Cr2O7!=0 && duplo_FAS!=0){
                duplo_MFAS=(duplo_K2Cr2O7*0.1)/duplo_FAS
               $("#duplo_MFAS_{{$id}}").val(duplo_MFAS)
               setHasilStandard()
            }
        })

        $("#vol_blanko_{{$id}}").on('input', function() {
            vol_blanko=parseFloat($("#vol_blanko_{{$id}}").val())
            setKadarCODSampleAll()
          
        })

        $("#spike_{{$id}}").on('input', function() {
            spike=parseFloat($("#spike_{{$id}}").val())
            setHasil()
        })

        $("#fp_value_{{$id}}").on('input', function() {
            fp_value=parseFloat($("#fp_value_{{$id}}").val())
            setHasil()
        })

        $("#fp_operator_{{$id}}").on('change', function() {
            fp_operator=$('#fp_operator_{{$id}}').find(":selected").val()
            setHasil()
        })


    


        
    </script>
    


    @for ($i = 0; $i < 3; $i++)
    <script>
        $(document).ready(function() {
            vol_sample.push(getValue("vol_sample_{{$i}}_{{$id}}"))
            vol_FAS_sample.push(getValue("vol_FAS_sample_{{$i}}_{{$id}}"))
            kadar_COD.push(getValue("kadar_COD_{{$i}}_{{$id}}"))

            $("#vol_sample_{{$i}}_{{$id}}").on('input', function() {
                vol_sample[parseInt("{{$i}}")]=parseFloat($('#vol_sample_{{$i}}_{{$id}}').val());
                
                setKadarCODSampleID(parseInt("{{$i}}")) 
            })
            $("#vol_FAS_sample_{{$i}}_{{$id}}").on('input', function() {
                vol_FAS_sample[parseInt("{{$i}}")]=parseFloat($('#vol_FAS_sample_{{$i}}_{{$id}}').val());
                setKadarCODSampleID(parseInt("{{$i}}")) 
            })
        })
    </script>
    @endfor


</div>

<script>


    $('#is_fp').on('change', function() {
        if($('input[name=is_fp]:checked').val()){
            $('#fp_container').show();
            is_fp=true;
            fp_operator=$('#fp_operator_{{$id}}').find(":selected").val()
            fp_value =getValue("fp_value_{{$id}}")
        }else{
            $('#fp_container').hide();
            is_fp=false;
        } 
    });


    $('#is_r').on('change', function() {
        if($('input[name=is_r]:checked').val()){
            $('#r_container').show();
            is_r=true;
            spike =getValue("spike_{{$id}}")
        
        }else{
            $('#r_container').hide();
            is_r=false;
        } 
    });

    $("#save_{{$id}}").click(function(){
      
      var data={}

      //Kosong
      for(var i=0;i<3;i++){

          data["kadar_COD_"+i+"_{{$id}}"]= $("#kadar_COD_"+i+"_{{$id}}").val();
          data["vol_sample_"+i+"_{{$id}}"]= $("#vol_sample_"+i+"_{{$id}}").val();
          data["vol_FAS_sample_"+i+"_{{$id}}"]= $("#vol_FAS_sample_"+i+"_{{$id}}").val();
        
      }

     

      data["_token"]= "{{ csrf_token() }}";
      data["hasil_{{$id}}"]= $("#hasil_{{$id}}").val();
   
      data["tanggal_uji_{{$id}}"]= $("#tanggal_uji_{{$id}}").val();
      data["crm_iot_{{$id}}"]= $("#crm_iot_{{$id}}").val();
      data["nilai_{{$id}}"]= $("#nilai_{{$id}}").val();
      
      data["simplo_K₂Cr₂O₇_{{$id}}"]= $("#simplo_K₂Cr₂O₇_{{$id}}").val();
      data["simplo_FAS_{{$id}}"]= $("#simplo_FAS_{{$id}}").val();
      data["simplo_akuades_{{$id}}"]= $("#simplo_akuades_{{$id}}").val();
      data["simplo_MFAS_{{$id}}"]= $("#simplo_MFAS_{{$id}}").val();
      

      data["duplo_K₂Cr₂O₇_{{$id}}"]= $("#duplo_K₂Cr₂O₇_{{$id}}").val();
      data["duplo_akuades_{{$id}}"]= $("#duplo_akuades_{{$id}}").val();
      data["duplo_FAS_{{$id}}"]= $("#duplo_FAS_{{$id}}").val();
      data["duplo_MFAS_{{$id}}"]= $("#duplo_MFAS_{{$id}}").val();
      
      data["hasil_MFAS_{{$id}}"]= $("#hasil_MFAS_{{$id}}").val();
      data["bias_MFAS_{{$id}}"]= $("#bias_MFAS_{{$id}}").val();
     
     
      data["vol_blanko_{{$id}}"]= $("#vol_blanko_{{$id}}").val();


      data["hasil_{{$id}}"]= $("#hasil_{{$id}}").val();

      data["hasil_akhir_{{$id}}"]= hasil_akhir;



      data["rpd_{{$id}}"]= $("#rpd_{{$id}}").val();


      if(is_r){
        data["is_r_{{$id}}"]= true;
        data["r_{{$id}}"]= $("#r_{{$id}}").val();
        data["spike_{{$id}}"]= $("#spike_{{$id}}").val();
      }else{

        data["is_r_{{$id}}"]=false;
      }


      if(is_fp){
        data["is_fp_{{$id}}"]= true;
        data["fp_operator_{{$id}}"]= fp_operator;
        data["fp_value_{{$id}}"]= fp_value;
      }else{
        data["is_fp_{{$id}}"]= false;
      }


      
  
      $.ajax({
          /* the route pointing to the post function */
          url: "{{route('elits-module-methods.formC', ['id' => Request::segment(3),'id_samples' => Request::segment(4)])}}" ,
          type: 'POST',
          /* send the csrf-token and the input to the controller */
          data: data,
          dataType: 'JSON',
          /* remind that 'data' is the response of the AjaxController */
          success: function (data) { 

              Swal.fire({
                  title: 'Berhasil!',
                  text: data.success,
                  icon: 'success',
                  confirmButtonText: 'Ok'
              })
          }
      }); 
  });
     

    


</script>