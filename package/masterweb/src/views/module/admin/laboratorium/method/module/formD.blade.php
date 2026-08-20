
@php
if($result!=null){
    ${"tgl_uji_". $id}= Carbon\Carbon::createFromFormat('Y-m-d H:i:s',  $result->date_test)->format('d/m/Y');
    ${"molaritas_CaCO₃_standarisation_". $id}=$result->molaritas_CaCO₃_standarisation;
    ${"volume_CaCO₃_standarisation_". $id}=$result->volume_CaCO₃_standarisation;
    ${"volume_EDTA_standarisation_". $id}=$result->volume_EDTA_standarisation;
    ${"molaritas_EDTA_standarisation_". $id}=$result->molaritas_EDTA_standarisation;
    ${"volume_sampel_". $id}=$result->volume_sampel;
    ${"volume_EDTA_sampel_". $id}=$result->volume_EDTA_sampel;
    ${"hasil_". $id}=$result->hasil;
    ${"recovery_". $id}=$result->recovery;
    ${"rpd_". $id}=$result->rpd;

}else{
    $now = Carbon\Carbon::now()->format('d/m/Y');
    ${"tgl_uji_". $id}= $now;
    ${"molaritas_CaCO₃_standarisation_". $id}='0.01';
    ${"volume_CaCO₃_standarisation_". $id}='10';
    ${"volume_EDTA_standarisation_". $id}='11';
    ${"molaritas_EDTA_standarisation_". $id}='0.009090909090909092';
    ${"volume_sampel_". $id}='';
    ${"volume_EDTA_sampel_". $id}='';
    ${"hasil_". $id}='';
    ${"recovery_". $id}='';
    ${"rpd_". $id}='';
}
@endphp


<form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-module-methods.formD', ['id' => Request::segment(3),'id_samples' => Request::segment(4)])}}"  method="POST">
    @csrf

    <div class="form-group">
        <label for="tanggal_uji">Tanggal Uji</label>
        <div class="input-group date">
            <input type="text" class="form-control tanggal_uji" name="tanggal_uji_{{$id}}" id="tanggal_uji_{{$id}}" value="<?php echo ${'tgl_uji_'.$id};?>"  placeholder="Isikan Tanggal Uji" data-date-format="dd/mm/yyyy" required>
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
                Standarisasi
            </div>
            <div class="card-body">
            <div class="content">
                <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <label for="molaritas_CaCO₃_standarisation_{{$id}}">Molaritas CaCO₃ (M)</label>
                        <input type="number" class="form-control" id="molaritas_CaCO₃_standarisation_{{$id}}" step="any" name="molaritas_CaCO₃_standarisation_{{$id}}" value="<?php echo ${'molaritas_CaCO₃_standarisation_'.$id};?>" placeholder="Molaritas CaCO₃ (M)" >
                    </div>
                    <div class="col-md-3">
                        <label for="volume_CaCO₃_standarisation_{{$id}}">Volume CaCO₃ (mL)</label>
                        <input type="number" class="form-control" id="volume_CaCO₃_standarisation_{{$id}}" step="any" name="volume_CaCO₃_standarisation_{{$id}}" value="<?php echo ${'volume_CaCO₃_standarisation_'.$id};?>" placeholder="Volume CaCO₃ (mL)" >
                    </div>
                    <div class="col-md-3">
                        <label for="volume_EDTA_standarisation_{{$id}}">Volume EDTA (mL)</label>
                        <input type="number" class="form-control" id="volume_EDTA_standarisation_{{$id}}" step="any" name="volume_EDTA_standarisation_{{$id}}" value="<?php echo ${'volume_EDTA_standarisation_'.$id};?>" placeholder="Volume EDTA (mL)" >
                    </div>
                    <div class="col-md-3">
                        <label for="molaritas_EDTA_standarisation_{{$id}}">Molaritas  EDTA (M)</label>
                        <input type="number" class="form-control" id="molaritas_EDTA_standarisation_{{$id}}" step="any" name="molaritas_EDTA_standarisation_{{$id}}" value="<?php echo ${'molaritas_EDTA_standarisation_'.$id};?>" placeholder="Molaritas  EDTA (M)" readonly>
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
                Pengujian
            </div>
            <div class="card-body">
            <div class="content">
                <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <label for="volume_sampel_{{$id}}">Volume Sampel (mL)</label>
                        <input type="number" class="form-control" id="volume_sampel_{{$id}}" step="any" name="volume_sampel_{{$id}}" value="<?php echo ${'volume_sampel_'.$id};?>" placeholder="Volume Sampel (mL)" >
                    </div>
                    <div class="col-md-6">
                        <label for="volume_EDTA_sampel_{{$id}}">Volume EDTA Sampel (mL)</label>
                        <input type="number" class="form-control" id="volume_EDTA_sampel_{{$id}}" step="any" name="volume_EDTA_sampel_{{$id}}" value="<?php echo ${'volume_EDTA_sampel_'.$id};?>" placeholder="Volume EDTA Sampel (mL)" >
                    </div>
                  
                </div>
             </div>
            </div>
            </div>
        </div>
    </div>

 


    <div class="form-group">
        <label for="hasil_{{$id}}">Hasil</label>
        <input type="text" class="form-control" id="hasil_{{$id}}" name="hasil_{{$id}}" step="any"  value="<?php echo ${'hasil_'.$id};?>" placeholder="Hasil" readonly>
    </div>


    <div class="form-group">
        <label for="recovery_{{$id}}">% Recovery</label>
        <input type="text" class="form-control" id="recovery_{{$id}}" name="recovery_{{$id}}" step="any"  value="<?php echo ${'recovery_'.$id};?>" placeholder="% Recovery">
    </div>

    <div class="form-group">
        <label for="rpd_{{$id}}">% RPD</label>
        <input type="text" class="form-control" id="rpd_{{$id}}" name="rpd_{{$id}}" step="any"  value="<?php echo ${'rpd_'.$id};?>" placeholder="% RPD">
    </div>



    <button type="submit" class="btn btn-primary mr-2" >Simpan</button>
</form>


<script>

 $(document).ready(function() {
    

    $('.tanggal_uji').datepicker({
        format: 'dd/mm/yyyy'
    });


    // Hasil        : avg(k1,k2)
    // RPD        : K1-K2/hasil*100

    var molaritas_CaCO_standarisation=getValue("molaritas_CaCO₃_standarisation_{{$id}}"),
    volume_CaCO_standarisation=getValue("volume_CaCO₃_standarisation_{{$id}}"),
    volume_EDTA_standarisation=getValue("volume_EDTA_standarisation_{{$id}}"),
    molaritas_EDTA_standarisation=getValue("molaritas_EDTA_standarisation_{{$id}}"),
    volume_sampel=getValue("volume_sampel_{{$id}}"),
    volume_EDTA_sampel=getValue("volume_EDTA_sampel_{{$id}}"),
    hasil=getValue("hasil_{{$id}}");




    $("#molaritas_CaCO₃_standarisation_{{$id}}").keyup(function() {
        molaritas_CaCO_standarisation=parseFloat($("#molaritas_CaCO₃_standarisation_{{$id}}").val());
        molaritas_EDTA_standarisation=molaritas_CaCO_standarisation*volume_CaCO_standarisation/volume_EDTA_standarisation;
        hasil=(1000/volume_sampel)*volume_EDTA_sampel*molaritas_EDTA_standarisation*100;
        
        $("#molaritas_EDTA_standarisation_{{$id}}").val(molaritas_EDTA_standarisation) 

        $("#hasil_{{$id}}").val(hasil) 
    });

    $("#volume_CaCO₃_standarisation_{{$id}}").keyup(function() {
        volume_CaCO_standarisation=parseFloat($("#volume_CaCO₃_standarisation_{{$id}}").val());
        molaritas_EDTA_standarisation=molaritas_CaCO_standarisation*volume_CaCO_standarisation/volume_EDTA_standarisation;
        hasil=(1000/volume_sampel)*volume_EDTA_sampel*molaritas_EDTA_standarisation*100;
        
        $("#molaritas_EDTA_standarisation_{{$id}}").val(molaritas_EDTA_standarisation) 
        
        $("#hasil_{{$id}}").val(hasil) 
    });

    $("#volume_EDTA_standarisation_{{$id}}").keyup(function() {
        volume_EDTA_standarisation=parseFloat($("#volume_EDTA_standarisation_{{$id}}").val());
        molaritas_EDTA_standarisation=molaritas_CaCO_standarisation*volume_CaCO_standarisation/volume_EDTA_standarisation;
        hasil=(1000/volume_sampel)*volume_EDTA_sampel*molaritas_EDTA_standarisation*100;
        $("#molaritas_EDTA_standarisation_{{$id}}").val(molaritas_EDTA_standarisation) 
        $("#hasil_{{$id}}").val(hasil) 
    });

    $("#volume_sampel_{{$id}}").keyup(function() {
        volume_sampel=parseFloat($("#volume_sampel_{{$id}}").val());
        hasil=(1000/volume_sampel)*volume_EDTA_sampel*molaritas_EDTA_standarisation*100;
        $("#hasil_{{$id}}").val(hasil) 
    });

    $("#volume_EDTA_sampel_{{$id}}").keyup(function() {
        volume_EDTA_sampel=parseFloat($("#volume_EDTA_sampel_{{$id}}").val());
        hasil=(1000/volume_sampel)*volume_EDTA_sampel*molaritas_EDTA_standarisation*100;
        $("#hasil_{{$id}}").val(hasil) 
    });

   

 
        
    function getValue(id) {
        var valueText=$("#"+id).val()
       
        if(valueText==''){
            return 0;
        }else{
            return parseFloat(valueText);
        }
    }

});
</script>