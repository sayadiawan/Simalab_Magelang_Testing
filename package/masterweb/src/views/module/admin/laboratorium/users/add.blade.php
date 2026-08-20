@extends('masterweb::template.admin.layout')
@section('title')
    User Management
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-custom bg-inverse-primary">
                                <li class="breadcrumb-item"><a href="{{url('/home')}}"><i class="fa fa-home menu-icon mr-1"></i> Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{url('/elits-users')}}">Pegawai</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><span>Tambah Data</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-users.store')}}"  method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Laboratorium</label>
                <select name="laboratory_users" id="laboratory_users" class="form-control">
                    <option value="">tidak memiliki laboratorium</option>
                    @foreach ($laboratories as $laboratory)
                        <option value="{{$laboratory->id_laboratorium}}">{{$laboratory->nama_laboratorium}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <input type="hidden" class="form-control" id="root_firebase" name="root_firebase" value="{!! Ramsey\Uuid\Uuid::uuid4();!!}" >
                <label for="name">Nama Lengkap</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nama lengkap" >
            </div>

            <div class="form-group">
                <label for="nip_users">NIP</label>
                <input type="text" class="form-control" id="nip_users" name="nip_users" placeholder="NIP" >
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" >
            </div>

            <div class="form-group">
                <label for="email">Alamat email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" >
            </div>

            <div class="form-group">
                <label for="phone">Nomor HP / WhatsApp</label>
                <input type="text" class="form-control" id="phone" name="phone" placeholder="Contoh: 6281234567890" >
                <small class="form-text text-muted">Format: 62xxxxxxxxxxx (tanpa + dan spasi)</small>
            </div>

            <div class="form-group">
                <label for="email">Hak Akses</label>
                <select name="level" id="level" class="form-control">
                    @foreach ($privileges as $privilege)
                        @if($privilege->id!="7d6bc1b7-5115-4724-820d-f04744f61828")
                            <option value="{{$privilege->id}}">{{$privilege->name}}</option>
                        @endif 
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Koneksi Petugas</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="petugas_action" id="petugas_none" value="none" checked>
                    <label class="form-check-label" for="petugas_none">
                        Tidak ada koneksi petugas
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="petugas_action" id="petugas_existing" value="existing">
                    <label class="form-check-label" for="petugas_existing">
                        Pilih petugas yang sudah ada
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="petugas_action" id="petugas_new" value="new">
                    <label class="form-check-label" for="petugas_new">
                        Buat petugas baru
                    </label>
                </div>

                <!-- Dropdown untuk pilih petugas yang sudah ada -->
                <div id="petugas_existing_section" style="display: none;">
                    <select name="id_petugas" id="id_petugas" class="form-control">
                        <option value="">-- Pilih Petugas --</option>
                        @foreach ($petugasList as $petugas)
                            <option value="{{$petugas->id_petugas}}">
                                {{$petugas->nama}} 
                                @if($petugas->nip) (NIP: {{$petugas->nip}}) @endif
                                @if($petugas->gelar) - {{$petugas->gelar}} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Form untuk membuat petugas baru -->
                <div id="petugas_new_section" style="display: none; border: 1px solid #dee2e6; padding: 20px; border-radius: 8px; margin-top: 15px; background: #f8f9fa;">
                    <h5 class="mb-3"><i class="fas fa-user-plus mr-2"></i>Form Petugas Baru</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nik_petugas">
                                    <i class="fas fa-id-card mr-1 text-primary"></i>NIK
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text" class="form-control" id="nik_petugas" name="nik_petugas" placeholder="Masukkan NIK (16 digit)" value="{{ old('nik_petugas') }}" maxlength="16">
                                <small class="form-text text-muted">16 digit angka</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gelar_petugas">
                                    <i class="fas fa-graduation-cap mr-1 text-primary"></i>Gelar
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text" class="form-control" id="gelar_petugas" name="gelar_petugas" placeholder="Contoh: S.Si, A.Md, dll" value="{{ old('gelar_petugas') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lab_id_petugas">
                            <i class="fas fa-flask mr-1 text-primary"></i>Laboratorium
                            <small class="text-muted">(Dapat memilih lebih dari satu - Opsional)</small>
                            <br>
                            <small class="text-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Kepala Lab</strong> bisa sebagai validator di banyak laboratorium.
                                <strong>Petugas</strong> juga bisa menempati banyak laboratorium.
                            </small>
                        </label>
                        <select class="form-control select2-multiple" id="lab_id_petugas" name="lab_id_petugas[]" multiple="multiple" style="width: 100%;">
                            @foreach ($laboratories as $lab)
                                <option value="{{ $lab->id_laboratorium }}" {{ is_array(old('lab_id_petugas')) && in_array($lab->id_laboratorium, old('lab_id_petugas')) ? 'selected' : '' }}>
                                    {{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-lightbulb"></i> Untuk Non Lab (Pendaftaran/Kepala Lab/Keuangan), kosongkan pilihan laboratorium
                        </small>
                    </div>

                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="is_kepala_lab_petugas" name="is_kepala_lab_petugas" value="1" {{ old('is_kepala_lab_petugas') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_kepala_lab_petugas">
                                <i class="fas fa-user-tie mr-1 text-warning"></i>
                                <strong>Kepala Lab</strong>
                                <small class="text-muted">(Centang jika petugas adalah Kepala Lab)</small>
                            </label>
                        </div>
                        <div id="kepala-lab-info-petugas" class="alert alert-info mt-2" style="display: none;">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Informasi Kepala Lab:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kepala Lab dapat sebagai validator di banyak laboratorium</li>
                                <li>Pilih laboratorium yang akan diawasi/divalidasi</li>
                                <li>Role untuk Kepala Lab dapat dikosongi (opsional)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group" id="role-group-petugas" style="display: none;">
                        <label for="role_petugas">
                            <i class="fas fa-tasks mr-1 text-primary"></i>Peran (Role)
                            <small class="text-muted">(Dapat memilih lebih dari satu - Opsional)</small>
                            <br>
                            <small class="text-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Untuk Pendaftaran:</strong> Pilih role, akan ditambahkan ke kolom "register" di Verification Activities
                                <br>
                                <strong>Untuk Kepala Lab/Keuangan:</strong> Role dapat dikosongi
                            </small>
                        </label>
                        <select class="form-control select2-multiple" id="role_petugas" name="role_petugas[]" multiple style="width: 100%;">
                            @foreach ($verificationActivities as $activity)
                                <option value="{{ $activity->id }}" {{ is_array(old('role_petugas')) && in_array($activity->id, old('role_petugas')) ? 'selected' : '' }}>
                                    {{ $activity->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Photo</label>
                <input type="file" name="photo" id="photo" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary mr-2" >Simpan</button>
            <button class="btn btn-light" onclick="goBack()">Kembali</button>
        </form>
        </div>
    </div>


    <script src="{{asset('assets/admin/cdn-local/js/jquery-3.6.0.min.js')}}"></script>
    <style>
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            min-height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 5px 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            padding: 2px 8px;
            margin: 3px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 5px;
        }
    </style>
    <script>
    function goBack() {
        window.history.back();
    }

    // Handle petugas action radio buttons
    $(document).ready(function() {
        // Initialize select2 for petugas form
        if ($('#lab_id_petugas').length) {
            $('#lab_id_petugas').select2({
                placeholder: 'Pilih Laboratorium (dapat memilih lebih dari satu)',
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });
        }

        if ($('#role_petugas').length) {
            $('#role_petugas').select2({
                placeholder: 'Pilih Peran (dapat memilih lebih dari satu)',
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });
        }

        // Handle petugas action radio buttons
        $('input[name="petugas_action"]').on('change', function() {
            var action = $(this).val();
            
            // Hide all sections
            $('#petugas_existing_section').hide();
            $('#petugas_new_section').hide();
            $('#id_petugas').prop('required', false);
            
            // Show relevant section
            if (action === 'existing') {
                $('#petugas_existing_section').show();
                $('#id_petugas').prop('required', true);
            } else if (action === 'new') {
                $('#petugas_new_section').show();
            }
        });

        // Trigger for Kepala Lab checkbox in petugas form
        $('#is_kepala_lab_petugas').on('change', function() {
            if ($(this).is(':checked')) {
                $('#kepala-lab-info-petugas').slideDown(300);
                if ($('#role-group-petugas').is(':hidden')) {
                    $('#role-group-petugas').slideDown(300);
                }
            } else {
                $('#kepala-lab-info-petugas').slideUp(300);
            }
        });

        // Show/hide role dropdown based on lab selection in petugas form
        $('#lab_id_petugas').on('change', function() {
            var labIds = $(this).val();
            if (labIds && labIds.length > 0) {
                $('#role-group-petugas').slideDown(300);
            }
        });

        // Trigger on page load if kepala lab is checked
        if ($('#is_kepala_lab_petugas').is(':checked')) {
            $('#kepala-lab-info-petugas').show();
        }

        // Trigger on page load if lab_id has value
        if ($('#lab_id_petugas').val()) {
            $('#role-group-petugas').show();
        }
    });
    // function myFunction() {

    //     var kode= document.getElementById("kode-user").value;
    //     var name= document.getElementById("name").value;
    //     var username= document.getElementById("username").value;
    //     var email= document.getElementById("email").value;

    //     var x = document.getElementById("level").selectedIndex;
    //     var level=document.getElementsByTagName("option")[x].value;

       

    //     if(level=="09405c01-092e-4eb7-a1d7-b511c74f6cda"){
           
    //         firebase.database().ref('users/'+kode).set({
    //             username: username,
    //             name: name,
    //             email: email,
    //             role:"user"
    //         }).then(function() {
    //             // window.location.href = "./dashboard"
        
            
    //         }).catch(function(error) {
    //             // An error happened.
    //         });


    //     }
        

        
    // }
    
    </script>
@endsection