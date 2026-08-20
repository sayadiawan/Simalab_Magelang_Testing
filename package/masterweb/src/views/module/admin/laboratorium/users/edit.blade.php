@extends('masterweb::template.admin.layout')
@section('title')
    User Management
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">User Management</h4>
        <p class="card-description">
            edit data
        </p>
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-users.update', [$id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">
            <div class="form-group">
                <label for="name">Laboratorium</label>
                <select name="laboratory_users" id="laboratory_users" class="form-control">
                    <option value="">tidak memiliki laboratorium</option>
                    @foreach ($laboratories as $laboratory)
                        <option value="{{$laboratory->id_laboratorium}}" {{isSelected($laboratory->id_laboratorium,$users->laboratory_users)}}>{{$laboratory->nama_laboratorium}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Nama lengkap" value="{{$users->name}}">
            </div>

            <div class="form-group">
                <label for="nip_users">NIP</label>
                <input type="text" class="form-control" id="nip_users" name="nip_users" value="{{$users->nip_users}}" placeholder="NIP" >
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="{{$users->username}}">
            </div>

            <div class="form-group">
                <label for="email">Alamat email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{$users->email}}">
            </div>

            <div class="form-group">
                <label for="phone">Nomor HP / WhatsApp</label>
                <input type="text" class="form-control" id="phone" name="phone" placeholder="Contoh: 6281234567890" value="{{$users->phone ?? ''}}">
                <small class="form-text text-muted">Format: 62xxxxxxxxxxx (tanpa + dan spasi)</small>
            </div>

            <div class="form-group">
                <label for="email">Hak Akses</label>
                <select name="level" id="level" class="form-control">
                    @foreach ($privileges as $privilege)
                        <option value="{{$privilege->id}}" {{($users->level==$privilege->id) ? "selected" : NULL}}>{{$privilege->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Koneksi Petugas</label>
                @php
                    $currentPetugasAction = $users->id_petugas ? 'existing' : 'none';
                @endphp
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="petugas_action" id="petugas_none" value="none" {{$currentPetugasAction == 'none' ? 'checked' : ''}}>
                    <label class="form-check-label" for="petugas_none">
                        Tidak ada koneksi petugas
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="petugas_action" id="petugas_existing" value="existing" {{$currentPetugasAction == 'existing' ? 'checked' : ''}}>
                    <label class="form-check-label" for="petugas_existing">
                        Pilih petugas yang sudah ada
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="petugas_action" id="petugas_update" value="update" {{$currentPetugasAction == 'existing' && $users->petugas ? 'style="display:none;"' : ''}}>
                    <label class="form-check-label" for="petugas_update" {{$currentPetugasAction == 'existing' && $users->petugas ? 'style="display:none;"' : ''}}>
                        Update petugas yang terhubung
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="petugas_action" id="petugas_new" value="new">
                    <label class="form-check-label" for="petugas_new">
                        Buat petugas baru
                    </label>
                </div>

                <!-- Dropdown untuk pilih petugas yang sudah ada -->
                <div id="petugas_existing_section" style="display: {{$currentPetugasAction == 'existing' ? 'block' : 'none'}};">
                    <select name="id_petugas" id="id_petugas" class="form-control">
                        <option value="">-- Pilih Petugas --</option>
                        @foreach ($petugasList as $petugas)
                            <option value="{{$petugas->id_petugas}}" {{($users->id_petugas == $petugas->id_petugas) ? 'selected' : ''}}>
                                {{$petugas->nama}} 
                                @if($petugas->nip) (NIP: {{$petugas->nip}}) @endif
                                @if($petugas->gelar) - {{$petugas->gelar}} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Form untuk update petugas yang terhubung -->
                @if($users->petugas)
                <div id="petugas_update_section" style="display: none; border: 1px solid #dee2e6; padding: 20px; border-radius: 8px; margin-top: 15px; background: #f8f9fa;">
                    <h5 class="mb-3"><i class="fas fa-user-edit mr-2"></i>Update Petugas Terhubung</h5>
                    <div class="alert alert-info">
                        <strong>Petugas Terhubung:</strong> {{$users->petugas->nama}}
                        @if($users->petugas->nip) (NIP: {{$users->petugas->nip}}) @endif
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nik_petugas">
                                    <i class="fas fa-id-card mr-1 text-primary"></i>NIK
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text" class="form-control" id="nik_petugas" name="nik_petugas" placeholder="Masukkan NIK (16 digit)" maxlength="16" value="{{$users->petugas->nik ?? ''}}">
                                <small class="form-text text-muted">16 digit angka</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gelar_petugas">
                                    <i class="fas fa-graduation-cap mr-1 text-primary"></i>Gelar
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text" class="form-control" id="gelar_petugas" name="gelar_petugas" placeholder="Contoh: S.Si, A.Md, dll" value="{{$users->petugas->gelar ?? ''}}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lab_id_petugas_update">
                            <i class="fas fa-flask mr-1 text-primary"></i>Laboratorium
                            <small class="text-muted">(Dapat memilih lebih dari satu - Opsional)</small>
                        </label>
                        <select class="form-control select2-multiple" id="lab_id_petugas_update" name="lab_id_petugas[]" multiple="multiple" style="width: 100%;">
                            @foreach ($laboratories as $lab)
                                @php
                                    $labIds = is_array($users->petugas->lab_id) ? $users->petugas->lab_id : (is_string($users->petugas->lab_id) ? json_decode($users->petugas->lab_id, true) : []);
                                    if (!is_array($labIds)) $labIds = [];
                                @endphp
                                <option value="{{ $lab->id_laboratorium }}" {{ in_array($lab->id_laboratorium, $labIds) ? 'selected' : '' }}>
                                    {{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="is_kepala_lab_petugas_update" name="is_kepala_lab_petugas" value="1" {{$users->petugas->is_kepala_lab ? 'checked' : ''}}>
                            <label class="form-check-label" for="is_kepala_lab_petugas_update">
                                <i class="fas fa-user-tie mr-1 text-warning"></i>
                                <strong>Kepala Lab</strong>
                            </label>
                        </div>
                        <div id="kepala-lab-info-petugas-update" class="alert alert-info mt-2" style="display: {{$users->petugas->is_kepala_lab ? 'block' : 'none'}};">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Informasi Kepala Lab:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kepala Lab dapat sebagai validator di banyak laboratorium</li>
                                <li>Pilih laboratorium yang akan diawasi/divalidasi</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group" id="role-group-petugas-update" style="display: none;">
                        <label for="role_petugas_update">
                            <i class="fas fa-tasks mr-1 text-primary"></i>Peran (Role)
                            <small class="text-muted">(Dapat memilih lebih dari satu - Opsional)</small>
                        </label>
                        <select class="form-control select2-multiple" id="role_petugas_update" name="role_petugas[]" multiple style="width: 100%;">
                            @php
                                $roles = is_array($users->petugas->role) ? $users->petugas->role : (is_string($users->petugas->role) ? json_decode($users->petugas->role, true) : []);
                                if (!is_array($roles)) $roles = [];
                            @endphp
                            @foreach ($verificationActivities as $activity)
                                <option value="{{ $activity->id }}" {{ in_array($activity->id, $roles) ? 'selected' : '' }}>
                                    {{ $activity->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Form untuk membuat petugas baru -->
                <div id="petugas_new_section" style="display: none; border: 1px solid #dee2e6; padding: 20px; border-radius: 8px; margin-top: 15px; background: #f8f9fa;">
                    <h5 class="mb-3"><i class="fas fa-user-plus mr-2"></i>Form Petugas Baru</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nik_petugas_new">
                                    <i class="fas fa-id-card mr-1 text-primary"></i>NIK
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text" class="form-control" id="nik_petugas_new" name="nik_petugas" placeholder="Masukkan NIK (16 digit)" maxlength="16">
                                <small class="form-text text-muted">16 digit angka</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gelar_petugas_new">
                                    <i class="fas fa-graduation-cap mr-1 text-primary"></i>Gelar
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <input type="text" class="form-control" id="gelar_petugas_new" name="gelar_petugas" placeholder="Contoh: S.Si, A.Md, dll">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lab_id_petugas_new">
                            <i class="fas fa-flask mr-1 text-primary"></i>Laboratorium
                            <small class="text-muted">(Dapat memilih lebih dari satu - Opsional)</small>
                        </label>
                        <select class="form-control select2-multiple" id="lab_id_petugas_new" name="lab_id_petugas[]" multiple="multiple" style="width: 100%;">
                            @foreach ($laboratories as $lab)
                                <option value="{{ $lab->id_laboratorium }}">
                                    {{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="is_kepala_lab_petugas_new" name="is_kepala_lab_petugas" value="1">
                            <label class="form-check-label" for="is_kepala_lab_petugas_new">
                                <i class="fas fa-user-tie mr-1 text-warning"></i>
                                <strong>Kepala Lab</strong>
                            </label>
                        </div>
                        <div id="kepala-lab-info-petugas-new" class="alert alert-info mt-2" style="display: none;">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Informasi Kepala Lab:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kepala Lab dapat sebagai validator di banyak laboratorium</li>
                                <li>Pilih laboratorium yang akan diawasi/divalidasi</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group" id="role-group-petugas-new" style="display: none;">
                        <label for="role_petugas_new">
                            <i class="fas fa-tasks mr-1 text-primary"></i>Peran (Role)
                            <small class="text-muted">(Dapat memilih lebih dari satu - Opsional)</small>
                        </label>
                        <select class="form-control select2-multiple" id="role_petugas_new" name="role_petugas[]" multiple style="width: 100%;">
                            @foreach ($verificationActivities as $activity)
                                <option value="{{ $activity->id }}">
                                    {{ $activity->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Photo</label>
                <br>
                <img src="{{asset('storage/photo/'.$users->photo)}}" alt="photo" width="5%">
                <input type="file" name="photo" id="photo" class="form-control">
                <span>*kosongkan gambar jika tidak ingin diubah</span>
            </div>

            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
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
        // Initialize select2 for petugas forms
        if ($('#lab_id_petugas_update').length) {
            $('#lab_id_petugas_update').select2({
                placeholder: 'Pilih Laboratorium (dapat memilih lebih dari satu)',
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });
        }

        if ($('#role_petugas_update').length) {
            $('#role_petugas_update').select2({
                placeholder: 'Pilih Peran (dapat memilih lebih dari satu)',
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });
            // Show role group if roles exist
            var selectedRoles = $('#role_petugas_update').val();
            if (selectedRoles && selectedRoles.length > 0) {
                $('#role-group-petugas-update').show();
            }
        }

        if ($('#lab_id_petugas_new').length) {
            $('#lab_id_petugas_new').select2({
                placeholder: 'Pilih Laboratorium (dapat memilih lebih dari satu)',
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });
        }

        if ($('#role_petugas_new').length) {
            $('#role_petugas_new').select2({
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
            $('#petugas_update_section').hide();
            $('#petugas_new_section').hide();
            $('#id_petugas').prop('required', false);
            
            // Show relevant section
            if (action === 'existing') {
                $('#petugas_existing_section').show();
                $('#id_petugas').prop('required', true);
            } else if (action === 'update') {
                $('#petugas_update_section').show();
            } else if (action === 'new') {
                $('#petugas_new_section').show();
            }
        });
        
        // Trigger for Kepala Lab checkbox in update section
        $('#is_kepala_lab_petugas_update').on('change', function() {
            if ($(this).is(':checked')) {
                $('#kepala-lab-info-petugas-update').slideDown(300);
                if ($('#role-group-petugas-update').is(':hidden')) {
                    $('#role-group-petugas-update').slideDown(300);
                }
            } else {
                $('#kepala-lab-info-petugas-update').slideUp(300);
            }
        });

        // Trigger for Kepala Lab checkbox in new section
        $('#is_kepala_lab_petugas_new').on('change', function() {
            if ($(this).is(':checked')) {
                $('#kepala-lab-info-petugas-new').slideDown(300);
                if ($('#role-group-petugas-new').is(':hidden')) {
                    $('#role-group-petugas-new').slideDown(300);
                }
            } else {
                $('#kepala-lab-info-petugas-new').slideUp(300);
            }
        });

        // Show/hide role dropdown based on lab selection in update section
        $('#lab_id_petugas_update').on('change', function() {
            var labIds = $(this).val();
            if (labIds && labIds.length > 0) {
                $('#role-group-petugas-update').slideDown(300);
            }
        });

        // Show/hide role dropdown based on lab selection in new section
        $('#lab_id_petugas_new').on('change', function() {
            var labIds = $(this).val();
            if (labIds && labIds.length > 0) {
                $('#role-group-petugas-new').slideDown(300);
            }
        });
        
        // Trigger on page load
        $('input[name="petugas_action"]:checked').trigger('change');
        
        // Show role group if lab_id has value in update section
        if ($('#lab_id_petugas_update').val() && $('#lab_id_petugas_update').val().length > 0) {
            $('#role-group-petugas-update').show();
        }
    });
    </script>
@endsection