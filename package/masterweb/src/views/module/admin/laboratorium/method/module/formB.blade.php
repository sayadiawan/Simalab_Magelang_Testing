
@php
if($form!=null){
    ${"crm_iot_". $id}=$form->crm_iot;
    ${"standarisation_". $id}=$form->standarisation;
 
    ${"hasil_" . $id} =$form->hasil;
}else{
 
    ${"crm_iot_". $id}='';
    ${"standarisation_". $id}='';
   
    ${"hasil_" . $id} ='';
      
}
@endphp
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
var sample_titration1=[],
sample_titration2=[],
sample_DO_0=[],
sample_DO_5=[],
volume_bakteri=[],
volume_sample=[],
volume_botol=[],
blanko_titration1=[],
blanko_titration2=[],
blanko_DO_0=[],
blanko_DO_5=[],
standarisation=0,
hasil=0
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
                <label for="crm_iot_{{$id}}">CRM - ERA lot</label>
                <input type="text" class="form-control" id="crm_iot_{{$id}}" name="crm_iot_{{$id}}" value="<?php echo ${'crm_iot_'.$id};?>" placeholder="CRM - ERA lot" >
            </div>

            <div class="form-group">
                <label for="standarisation_{{$id}}">Standarisation</label>
                <input type="number" class="form-control" id="standarisation_{{$id}}" name="standarisation_{{$id}}" step="any" value="<?php echo ${'standarisation_'.$id};?>" placeholder="Masukkan Standaritation" >
            </div>


           

           

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
                    <div class="form-group">
                        <label for="tanggal_{{$i}}_uji">Tanggal Uji</label>
                        <div class="input-group date">
                            <input type="text" class="form-control tanggal_uji_{{$i}}" name="tanggal_uji_{{$i}}_{{$id}}" id="tanggal_uji_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"date_test",$i);?>"  placeholder="Isikan Tanggal Uji {{$i}}" data-date-format="dd/mm/yyyy" required>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>  


                    <div class="form-group">
                        <div class="card">
                            <div class="card-header">
                                Volume
                            </div>
                            <div class="card-body">
                            <div class="content">
                                <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="volume_bakteri_{{$i}}_{{$id}}">Bakteri</label>
                                        <input type="number" class="form-control" id="volume_bakteri_{{$i}}_{{$id}}" step="any" name="volume_bakteri_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"volume_bakteri",$i);?>" placeholder="Bakteri" >
                                    </div>
                                    <div class="col-md-4">
                                        <label for="volume_sample_{{$i}}_{{$id}}">Sample</label>
                                        <input type="number" class="form-control" id="volume_sample_{{$i}}_{{$id}}" step="any" name="volume_sample_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"volume_sample",$i);?>" placeholder="Sample" >
                                    </div>
                                    <div class="col-md-4">
                                        <label for="volume_botol_{{$i}}_{{$id}}">Botol</label>
                                        <input type="number" class="form-control" id="volume_botol_{{$i}}_{{$id}}" step="any" name="volume_botol_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"volume_botol",$i);?>" placeholder="Botol" >
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
                                BLANKO
                            </div>
                            <div class="card-body">
                            <div class="content">
                                <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="blanko_titration1_{{$i}}_{{$id}}">Titrasi I</label>
                                        <input type="number" class="form-control" id="blanko_titration1_{{$i}}_{{$id}}" step="any" name="blanko_titration1_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"blanko_titration1",$i);?>" placeholder="Titrasi I" >
                                    </div>
                                    <div class="col-md-3">
                                        <label for="blanko_titration2_{{$i}}_{{$id}}">Titrasi II</label>
                                        <input type="number" class="form-control" id="blanko_titration2_{{$i}}_{{$id}}" step="any" name="blanko_titration2_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"blanko_titration2",$i);?>" placeholder="Titrasi II" >
                                    </div>
                                    <div class="col-md-3">
                                        <label for="blanko_DO_0_{{$i}}_{{$id}}">DO 0</label>
                                        <input type="number" class="form-control" id="blanko_DO_0_{{$i}}_{{$id}}" step="any" name="blanko_DO_0_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"blanko_DO_0",$i);?>" placeholder="DO 0" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="blanko_DO_5_{{$i}}_{{$id}}">DO 5</label>
                                        <input type="number" class="form-control" id="blanko_DO_5_{{$i}}_{{$id}}" step="any" name="blanko_DO_5_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"blanko_DO_5",$i);?>" placeholder="DO 5" readonly>
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
                                Sample
                            </div>
                            <div class="card-body">
                            <div class="content">
                                <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="sample_titration1_{{$i}}_{{$id}}">Titrasi I</label>
                                        <input type="number" class="form-control" id="sample_titration1_{{$i}}_{{$id}}" step="any" name="sample_titration1_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"sample_titration1",$i);?>" placeholder="Titrasi I" >
                                    </div>
                                    <div class="col-md-3">
                                        <label for="sample_titration2_{{$i}}_{{$id}}">Titrasi II</label>
                                        <input type="number" class="form-control" id="sample_titration2_{{$i}}_{{$id}}" step="any" name="sample_titration2_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"sample_titration2",$i);?>" placeholder="Titrasi II" >
                                    </div>
                                    <div class="col-md-3">
                                        <label for="sample_DO_0_{{$i}}_{{$id}}">DO 0</label>
                                        <input type="number" class="form-control" id="sample_DO_0_{{$i}}_{{$id}}" step="any"  name="sample_DO_0_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"sample_DO_0",$i);?>" placeholder="DO 0" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="sample_DO_5_{{$i}}_{{$id}}">DO 5</label>
                                        <input type="number" class="form-control" id="sample_DO_5_{{$i}}_{{$id}}" name="sample_DO_5_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"sample_DO_5",$i);?>" placeholder="DO 5" readonly>
                                    </div>
                                </div>
                            </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            @endfor

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



            <div class="form-group">
                <label for="hasil">Hasil</label>
                <input type="text" class="form-control" id="hasil_{{$id}}" name="hasil_{{$id}}" step="any"  value="<?php echo ${'hasil_'.$id};?>" placeholder="Hasil" readonly>
            </div>



            <button type="submit" class="btn btn-primary mr-2" id="save_{{$id}}">Simpan</button>
        <!-- </form> -->
        

    </div>

    <script>
     

        function getValue(id) {
            var valueText=$("#"+id).val()
        
            if(valueText==''){
                return 0;
            }else{
                return parseFloat(valueText);
            }
        }
        
        standarisation=getValue("standarisation_{{$id}}")
      
        rpd=getValue("rpd_{{$id}}")
        hasil=getValue("hasil_{{$id}}")

        $("#standarisation_{{$id}}").on('input', function() {
            standarisation=parseFloat($("#standarisation_{{$id}}").val());


            for(var i=0;i<3;i++){
                if(blanko_titration1[i]!=0&&volume_botol[i]!=0){
                    blanko_DO_0[i]=(blanko_titration1[i]*standarisation*8000*(volume_botol[i]/298))/50
                    $("#blanko_DO_0_{{$i}}_{{$id}}").val(blanko_DO_0[i]) 
                }
                if(blanko_titration2[i]!=0&&volume_botol[i]!=0){
                    blanko_DO_5[i]=(blanko_titration2[i]*standarisation*8000*(volume_botol[i]/298))/50
                    $("#blanko_DO_5_{{$i}}_{{$id}}").val(blanko_DO_5[i])
                }
                if(blanko_titration2[i]!=0&&volume_botol[i]!=0){
                    sample_DO_0[i]=(sample_titration1[i]*standarisation*8000*(volume_botol[i]/298))/50
                    $("#sample_DO_0_{{$i}}_{{$id}}").val(sample_DO_0[i])
                }
                if(blanko_titration2[i]!=0&&volume_botol[i]!=0){
                    sample_DO_5[i]=(sample_titration2[i]*standarisation*8000*(volume_botol[i]/298))/50
                    $("#sample_DO_5_{{$i}}_{{$id}}").val(sample_DO_5[i])
                }
               
            }

           
            var total_hasil=0,total_rpd=0,filled=0;
            for(var i=0;i<3;i++){
                if(sample_DO_0[i]!=0 && sample_DO_5[i]!=0 && blanko_DO_0[i]!=0 && blanko_DO_5[i]!=0  && volume_bakteri[i]!=0 && volume_sample[i]!=0 && volume_botol[i]!=0){
                    total_hasil=total_hasil+((sample_DO_0[i]-sample_DO_5[i])-((blanko_DO_0[i]-blanko_DO_5[i])/volume_bakteri[i])*volume_bakteri[i])/(volume_sample[i]/volume_botol[i])
                    filled++;
                }
            }
            hasil=total_hasil/filled;

          
            $("#hasil_{{$id}}").val(hasil)

        });


        function setHasil(id) {
             var total_hasil=0,total_rpd=0,filled=0;
            for(var i=0;i<3;i++){

               
                  if(sample_DO_0[i]!=0 && sample_DO_5[i]!=0 && blanko_DO_0[i]!=0 && blanko_DO_5[i]!=0 && volume_bakteri[i]!=0 && volume_bakteri[i]!=0 && volume_sample[i]!=0 && volume_botol[i]!=0){
                    total_hasil=total_hasil+((sample_DO_0[i]-sample_DO_5[i])-((blanko_DO_0[i]-blanko_DO_5[i])/volume_bakteri[i])*volume_bakteri[i])/(volume_sample[i]/volume_botol[i])
                    filled++
                  }
                 
            }

            hasil=total_hasil/filled;      
            $("#hasil_"+id).val(hasil)
        }


     
    </script>

    @for ($i = 0; $i < 3; $i++)
    <script>
        $(document).ready(function() {
            $('.tanggal_uji_{{$i}}_{{$id}}').datepicker({
                format: 'dd/mm/yyyy'
            });


            blanko_titration1.push(getValue("blanko_titration1_{{$i}}_{{$id}}"))
            blanko_titration2.push(getValue("blanko_titration2_{{$i}}_{{$id}}"))
            blanko_DO_0.push(getValue("blanko_DO_0_{{$i}}_{{$id}}"))
            blanko_DO_5.push(getValue("blanko_DO_5_{{$i}}_{{$id}}"))
            sample_titration1.push(getValue("sample_titration1_{{$i}}_{{$id}}"))
            sample_titration2.push(getValue("sample_titration2_{{$i}}_{{$id}}"))
            sample_DO_0.push(getValue("sample_DO_0_{{$i}}_{{$id}}"))
            sample_DO_5.push(getValue("sample_DO_5_{{$i}}_{{$id}}"))
            volume_bakteri.push(getValue("volume_bakteri_{{$i}}_{{$id}}"))
            volume_sample.push(getValue("volume_sample_{{$i}}_{{$id}}"))
            volume_botol.push(getValue("volume_botol_{{$i}}_{{$id}}"))



            $("#blanko_titration1_{{$i}}_{{$id}}").on('input', function() {
                blanko_titration1[parseInt("{{$i}}")]=parseFloat($("#blanko_titration1_{{$i}}_{{$id}}").val());


                if(blanko_titration1[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){

                    blanko_DO_0[parseInt("{{$i}}")]=(blanko_titration1[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#blanko_DO_0_{{$i}}_{{$id}}").val(blanko_DO_0[parseInt("{{$i}}")]) 
                }



             
                setHasil("{{$id}}")
              
            });

            $("#blanko_titration2_{{$i}}_{{$id}}").on('input', function() {
                blanko_titration2[parseInt("{{$i}}")]=parseFloat($("#blanko_titration2_{{$i}}_{{$id}}").val());
                
                if(blanko_titration2[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){
                    blanko_DO_5[parseInt("{{$i}}")]=(blanko_titration2[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#blanko_DO_5_{{$i}}_{{$id}}").val(blanko_DO_5[parseInt("{{$i}}")]) 
                }

                setHasil("{{$id}}")
            });

            $("#sample_titration1_{{$i}}_{{$id}}").on('input', function() {
                sample_titration1[parseInt("{{$i}}")]=parseFloat($("#sample_titration1_{{$i}}_{{$id}}").val());
               
                if(sample_titration1[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){
                    sample_DO_0[parseInt("{{$i}}")]=(sample_titration1[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#sample_DO_0_{{$i}}_{{$id}}").val(sample_DO_0[parseInt("{{$i}}")]) 
                }
                
             
                setHasil("{{$id}}")
            });

            $("#sample_titration2_{{$i}}_{{$id}}").on('input', function() {
                sample_titration2[parseInt("{{$i}}")]=parseFloat($("#sample_titration2_{{$i}}_{{$id}}").val());
                if(sample_titration2[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){
                    sample_DO_5[parseInt("{{$i}}")]=(sample_titration2[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#sample_DO_5_{{$i}}_{{$id}}").val(sample_DO_5[parseInt("{{$i}}")]) 
                }
                
               
                setHasil("{{$id}}")
            });

            $("#volume_botol_{{$i}}_{{$id}}").on('input', function() {

                volume_botol[parseInt("{{$i}}")]=parseFloat($("#volume_botol_{{$i}}_{{$id}}").val());

                if(blanko_titration1[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){
                    blanko_DO_0[parseInt("{{$i}}")]=(blanko_titration1[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#blanko_DO_0_{{$i}}_{{$id}}").val(blanko_DO_0[parseInt("{{$i}}")]) 
                }
                if(blanko_titration2[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){
                    blanko_DO_5[parseInt("{{$i}}")]=(blanko_titration2[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#blanko_DO_5_{{$i}}_{{$id}}").val(blanko_DO_5[parseInt("{{$i}}")]) 
                }
                if(sample_titration1[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){
                    sample_DO_0[parseInt("{{$i}}")]=(sample_titration1[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#sample_DO_0_{{$i}}_{{$id}}").val(sample_DO_0[parseInt("{{$i}}")]) 
                }
                if(sample_titration2[parseInt("{{$i}}")]!=0 && standarisation!=0 && volume_botol[parseInt("{{$i}}")]!=0){
                    sample_DO_5[parseInt("{{$i}}")]=(sample_titration2[parseInt("{{$i}}")]*standarisation*8000*(volume_botol[parseInt("{{$i}}")]/298))/50
                    $("#sample_DO_5_{{$i}}_{{$id}}").v
                    al(sample_DO_5[parseInt("{{$i}}")]) 
                }
                
               
                setHasil("{{$id}}")
            });

            $("#volume_bakteri_{{$i}}_{{$id}}").on('input', function() {
                volume_bakteri[parseInt("{{$i}}")]=parseFloat($("#volume_bakteri_{{$i}}_{{$id}}").val());
                console.log(volume_bakteri[0])
             
                setHasil("{{$id}}")
            });

            $("#volume_sample_{{$i}}_{{$id}}").on('input', function() {
                volume_sample[parseInt("{{$i}}")]=parseFloat($("#volume_sample_{{$i}}_{{$id}}").val());
            
              
                setHasil("{{$id}}")
            });


        });
    </script>
    @endfor


</div>

<script>
 $(document).ready(function() {
    
    $("#save_{{$id}}").click(function(){
      
        var data={}

        //Kosong
        for(var i=0;i<3;i++){

            data["blanko_titration1_"+i+"_{{$id}}"]= $("#blanko_titration1_"+i+"_{{$id}}").val();
            data["blanko_titration2_"+i+"_{{$id}}"]= $("#blanko_titration2_"+i+"_{{$id}}").val();
            data["blanko_DO_0_"+i+"_{{$id}}"]= $("#blanko_DO_0_"+i+"_{{$id}}").val();
            data["blanko_DO_5_"+i+"_{{$id}}"]= $("#blanko_DO_5_"+i+"_{{$id}}").val();


            data["sample_titration1_"+i+"_{{$id}}"]= $("#sample_titration1_"+i+"_{{$id}}").val();
            data["sample_titration2_"+i+"_{{$id}}"]= $("#sample_titration2_"+i+"_{{$id}}").val();
            data["sample_DO_0_"+i+"_{{$id}}"]= $("#sample_DO_0_"+i+"_{{$id}}").val();
            data["sample_DO_5_"+i+"_{{$id}}"]= $("#sample_DO_5_"+i+"_{{$id}}").val();
     


            data["volume_botol_"+i+"_{{$id}}"]= $("#volume_botol_"+i+"_{{$id}}").val();
            data["volume_bakteri_"+i+"_{{$id}}"]= $("#volume_bakteri_"+i+"_{{$id}}").val();
            data["volume_sample_"+i+"_{{$id}}"]= $("#volume_sample_"+i+"_{{$id}}").val();
         
            data["tanggal_uji_"+i+"_{{$id}}"]= $("#tanggal_uji_"+i+"_{{$id}}").val();
         
        }

       

        data["_token"]= "{{ csrf_token() }}";
        data["hasil_{{$id}}"]= $("#hasil_{{$id}}").val();
        data["crm_iot_{{$id}}"]= $("#crm_iot_{{$id}}").val();
        data["standarisation_{{$id}}"]= $("#standarisation_{{$id}}").val();

        
    
        $.ajax({
            /* the route pointing to the post function */
            url: "{{route('elits-module-methods.formB', ['id' => Request::segment(3),'id_samples' => Request::segment(4)])}}" ,
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
       
   

});
</script>