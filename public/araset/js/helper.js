$(document).ready(function(){
    $('#btn-proses-registration').click(function(){
        const email = $('#email-form-registration');
        const pass = $('#password');
        const repass = $('#re-pass');
        
        if (email.val() == "") {
            $('#btn-proses-registration').html("Daftar");
        }else if(pass.val() == "") {
            $('#btn-proses-registration').html("Daftar");
        }else if(repass.val() == "") {
            $('#btn-proses-registration').html("Daftar");
        }else if(pass.val().length < 6 && repass.val().length <6 && pass.val() != repass.val()){
            $('#btn-proses-registration').html("Daftar");
        }else{
            $('#btn-proses-registration').html("LOADING..");
        }
    });
});