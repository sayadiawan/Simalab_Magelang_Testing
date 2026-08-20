
@php
if($form!=null){
   
    ${"rpd_". $id}=$form->rpd;
    ${"hasil_". $id}=$form->hasil;
}else{
  
    ${"rpd_". $id}='';
    ${"hasil_". $id}='';
}

@endphp


<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
var konsentrasi_1=[],konsentrasi_2=[],hasil=0,rpd=0
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
        
        <!-- <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-module-methods.formA', ['id' => Request::segment(3),'id_samples' => Request::segment(4)])}}"  method="POST">
                    @csrf -->
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
                        <label for="tanggal_uji_{{$i}}_{{$id}}">Tanggal Uji</label>
                        <div class="input-group date">
                            <input type="text" class="form-control tanggal_uji_{{$i}}_{{$id}}" name="tanggal_uji_{{$i}}_{{$id}}" id="tanggal_uji_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"date_test",$i);?>"  placeholder="Isikan Tanggal Uji {{$i+1}}" data-date-format="dd/mm/yyyy" required>
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="crm_iot_{{$i}}_{{$id}}">CRM - ERA lot</label>
                        <input type="text" class="form-control" id="crm_iot_{{$i}}_{{$id}}" name="crm_iot_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"crm_iot",$i);?>" placeholder="CRM - ERA lot" >
                    </div>

                

                    <div class="form-group">
                        <label for="konsentrasi_1_{{$i}}">Konsentrasi I {{$method->params_method}}</label>
                        <input type="number" class="form-control" id="konsentrasi_1_{{$i}}_{{$id}}"  step="any" name="konsentrasi_1_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"konsentrasi_1",$i);?>" placeholder="Masukkan Konsentrasi I {{$method->params_method}}" >
                    </div>

                    <div class="form-group">
                        <label for="konsentrasi_2_{{$i}}">Konsentrasi II {{$method->params_method}}</label>
                        <input type="number" class="form-control" id="konsentrasi_2_{{$i}}_{{$id}}"  step="any" name="konsentrasi_2_{{$i}}_{{$id}}" value="<?php echo cekValue($form_result,"konsentrasi_2",$i);?>" placeholder="Masukkan Konsentrasi II {{$method->params_method}}" >
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
        </div>
      
        <div class="form-group">
            <label for="rpd">% RPD</label>
            <input type="text" class="form-control" id="rpd_{{$id}}" name="rpd_{{$id}}"  step="any" placeholder="RPD" value="<?php echo ${'rpd_'.$id};?>" readonly>
        </div>

        <div class="form-group">
            <label for="hasil">Hasil</label>
            <input type="text" class="form-control" id="hasil_{{$id}}" name="hasil_{{$id}}"  step="any" placeholder="Hasil" value="<?php echo ${'hasil_'.$id};?>" readonly>
        </div>

        
        

    <!-- </form> -->

    <button type="submit" class="btn btn-primary mr-2" id="save_{{$id}}" >Simpan</button>

 
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
        hasil=getValue("hasil_{{$id}}"),rpd=getValue("rpd_{{$id}}")
     
    </script>

    @for ($i = 0; $i < 3; $i++)
    <script>
        $(document).ready(function() {

             // Hasil        : avg(k1,k2)
            // RPD        : K1-K2/hasil*100

            $('.tanggal_uji_{{$i}}_{{$id}}').datepicker({
                format: 'dd/mm/yyyy'
            });

            konsentrasi_1.push(getValue("konsentrasi_1_{{$i}}_{{$id}}")),
            konsentrasi_2.push(getValue("konsentrasi_2_{{$i}}_{{$id}}"));


            $("#konsentrasi_1_{{$i}}_{{$id}}").on('input', function() {
                konsentrasi_1[parseInt("{{$i}}")]=parseFloat($("#konsentrasi_1_{{$i}}_{{$id}}").val());
                var i=0
                var temp=0;
                var total_hasil=0,total_rpd=0,filled=0;
                konsentrasi_1.forEach(value=>{
                    if(value!=0){

                        if(temp!=0){
                            if(total_rpd==0){
                                total_rpd=Math.abs((konsentrasi_1[i]+konsentrasi_2[i])/2-temp)
                            }else{
                                total_rpd=(total_rpd+(Math.abs((konsentrasi_1[i]+konsentrasi_2[i])/2-temp)))/2
                            
                            }
                        }
                        
                        
                        temp=(konsentrasi_1[i]+konsentrasi_2[i])/2;

                      


                        total_hasil=total_hasil+((konsentrasi_1[i]+konsentrasi_2[i])/2)
                        filled++;
                    }
                    i++;
                })

                
                hasil=total_hasil/filled;

                $("#hasil_{{$id}}").val(hasil)
            

                rpd=(total_rpd)/hasil*100
                $("#rpd_{{$id}}").val(rpd)
            });

            $("#konsentrasi_2_{{$i}}_{{$id}}").on('input', function() {
                konsentrasi_2[parseInt("{{$i}}")]=parseFloat($("#konsentrasi_2_{{$i}}_{{$id}}").val());
                var i=0
                var temp;
                var total_hasil=0,total_rpd=0,filled=0;
                konsentrasi_2.forEach(value=>{
                    if(value!=0){
                        if(total_rpd==0){
                            total_rpd=Math.abs((konsentrasi_1[i]+konsentrasi_2[i])/2-temp)
                        }else{
                            total_rpd=(total_rpd+(Math.abs((konsentrasi_1[i]+konsentrasi_2[i])/2-temp)))/2
                        
                        }
                        
                        
                        temp=(konsentrasi_1[i]+konsentrasi_2[i])/2;

                      


                        total_hasil=total_hasil+((konsentrasi_1[i]+konsentrasi_2[i])/2)
                        filled++;
                    }
                    i++;
                })

                
                hasil=total_hasil/filled;
                $("#hasil_{{$id}}").val(hasil)
                rpd=(total_rpd)/hasil*100
                $("#rpd_{{$id}}").val(rpd)
            });
        });
    </script>
    @endfor


<script>
 $(document).ready(function() {
    
    $("#save_{{$id}}").click(function(){
      
        var data={}

        //Kosong
        for(var i=0;i<3;i++){

            data["konsentrasi_1_"+i+"_{{$id}}"]= $("#konsentrasi_1_"+i+"_{{$id}}").val();
            data["konsentrasi_2_"+i+"_{{$id}}"]= $("#konsentrasi_2_"+i+"_{{$id}}").val();
            data["tanggal_uji_"+i+"_{{$id}}"]= $("#tanggal_uji_"+i+"_{{$id}}").val();
            data["crm_iot_"+i+"_{{$id}}"]= $("#crm_iot_"+i+"_{{$id}}").val();            
         
        }

       

        data["_token"]= "{{ csrf_token() }}";
        data["rpd_{{$id}}"]= $("#rpd_{{$id}}").val();
        data["hasil_{{$id}}"]= $("#hasil_{{$id}}").val();

        
    
        $.ajax({
            /* the route pointing to the post function */
            url: "{{route('elits-module-methods.formA', ['id' => Request::segment(3),'id_samples' => Request::segment(4)])}}" ,
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
