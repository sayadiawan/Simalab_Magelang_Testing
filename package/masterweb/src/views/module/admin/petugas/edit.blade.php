@extends('masterweb::template.admin.layout')
@section('title')
    Petugas Management
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit mr-2"></i>Petugas Management
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle mr-1"></i>Edit data petugas
                    </p>
                    <form enctype="multipart/form-data" class="forms-sample"
                        action="{{ route('adm-petugas-update', $petugas->id_petugas) }}" method="POST">
                        @csrf
                        <input type="hidden" value="PUT" name="_method">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama">
                                        <i class="fas fa-user mr-1 text-primary"></i>Nama Lengkap <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="nama" name="nama"
                                        placeholder="Masukkan nama lengkap" value="{{ old('nama', $petugas->nama) }}"
                                        required>
                                    @error('nama')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nik">
                                        <i class="fas fa-id-card mr-1 text-primary"></i>NIK
                                        <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="nik" name="nik"
                                        placeholder="Masukkan NIK (16 digit)" value="{{ old('nik', $petugas->nik) }}"
                                        maxlength="16">
                                    @error('nik')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nip">
                                        <i class="fas fa-briefcase mr-1 text-primary"></i>NIP
                                        <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <input type="text" class="form-control" id="nip" name="nip"
                                        placeholder="Masukkan NIP" value="{{ old('nip', $petugas->nip) }}">
                                    @error('nip')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gelar">
                                        <i class="fas fa-graduation-cap mr-1 text-primary"></i>Gelar
                                        <small class="text-muted">(Contoh: S.Si, A.Md, dll)</small>
                                    </label>
                                    <input type="text" class="form-control" id="gelar" name="gelar"
                                        placeholder="Masukkan gelar" value="{{ old('gelar', $petugas->gelar) }}">
                                    @error('gelar')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">
                                        <i class="fas fa-lock mr-1 text-primary"></i>Password BSRE
                                        <small class="text-muted">(Opsional)</small>
                                    </label>
                                    <input type="text" class="form-control" id="password" name="password"
                                        placeholder="Password BSRE dinonaktifkan sementara"
                                        value="{{ old('password', $petugas->password) }}" disabled>
                                    @error('password')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lab_id">
                                        <i class="fas fa-flask mr-1 text-primary"></i>Laboratorium
                                        <small class="text-muted">(Dapat memilih lebih dari satu - Opsional)</small>
                                        <br>
                                        <small class="text-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Kepala Lab</strong> bisa sebagai validator di banyak laboratorium.
                                            <strong>Petugas</strong> juga bisa menempati banyak laboratorium.
                                        </small>
                                    </label>
                                    <select class="form-control select2-multiple" id="lab_id" name="lab_id[]"
                                        multiple="multiple" style="width: 100%;">
                                        @php
                                            $selectedLabIds = old(
                                                'lab_id',
                                                is_array($petugas->lab_id)
                                                    ? $petugas->lab_id
                                                    : ($petugas->lab_id
                                                        ? [$petugas->lab_id]
                                                        : []),
                                            );
                                            if (!is_array($selectedLabIds)) {
                                                $selectedLabIds = $selectedLabIds ? [$selectedLabIds] : [];
                                            }
                                            // Check if should show NON_LAB (if no lab selected)
                                            $hasNonLab = empty($selectedLabIds);
                                        @endphp
                                        <option value="NON_LAB"
                                            {{ $hasNonLab ? 'selected' : '' }}>
                                            Non Lab (Pendaftaran/Kepala Lab/Keuangan)
                                        </option>
                                        @foreach ($laboratoriums as $lab)
                                            <option value="{{ $lab->id_laboratorium }}"
                                                data-lab-code="{{ $lab->kode_laboratorium }}"
                                                {{ in_array($lab->id_laboratorium, $selectedLabIds) ? 'selected' : '' }}>
                                                {{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="fas fa-lightbulb"></i> Untuk Non Lab (Pendaftaran/Kepala Lab/Keuangan),
                                        kosongkan pilihan laboratorium
                                    </small>
                                    @error('lab_id')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    @error('lab_id.*')
                                        <div class="error text-danger mt-1 text-small">
                                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="is_kepala_lab"
                                            name="is_kepala_lab" value="1"
                                            {{ old('is_kepala_lab', $petugas->is_kepala_lab) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_kepala_lab">
                                            <i class="fas fa-user-tie mr-1 text-warning"></i>
                                            <strong>Kepala Lab</strong>
                                            <small class="text-muted">(Centang jika petugas adalah Kepala Lab)</small>
                                        </label>
                                    </div>
                                    <div id="kepala-lab-info" class="alert alert-info mt-2" style="display: none;">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Informasi Kepala Lab:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Kepala Lab dapat sebagai validator di banyak laboratorium</li>
                                            <li>Pilih laboratorium yang akan diawasi/divalidasi</li>
                                            <li>Role untuk Kepala Lab dapat dikosongi (opsional)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="role-per-lab-group"
                            style="display: {{ (is_array($petugas->lab_id) && !empty($petugas->lab_id)) || (!is_array($petugas->lab_id) && $petugas->lab_id) || (!$petugas->lab_id && !empty($petugas->role)) ? 'block' : 'none' }};">
                            <label>
                                <i class="fas fa-tasks mr-1 text-primary"></i>Peran Perlab (Role)
                                <small class="text-muted">(Pilih role untuk setiap lab yang dipilih - Opsional)</small>
                                <br>
                                <small class="text-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Pilih role untuk setiap lab secara terpisah.</strong> Role yang tersedia disesuaikan dengan lab yang dipilih.
                                    <br>
                                    <strong>Untuk Kepala Lab/Keuangan:</strong> Role dapat dikosongi
                                </small>
                            </label>
                            <div id="role-per-lab-container">
                                <!-- Dynamic role inputs per lab will be added here -->
                            </div>
                            @error('role_per_lab')
                                <div class="error text-danger mt-1 text-small">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </div>
                            @enderror
                            @error('role_per_lab.*')
                                <div class="error text-danger mt-1 text-small">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-warning btn-lg mr-2">
                                <i class="fas fa-save mr-2"></i>Update
                            </button>
                            <button type="button" class="btn btn-light btn-lg"
                                onclick="window.location.href='{{ route('adm-petugas') }}'">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
            padding: 2px 8px;
            margin: 3px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #212529;
            margin-right: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #212529;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .form-control-lg {
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }
    </style>
    <script>
        $(document).ready(function() {
            // Initialize select2 for lab_id (multiple select)
            $('#lab_id').select2({
                placeholder: 'Pilih Laboratorium (dapat memilih lebih dari satu)',
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });

            // Initialize select2 for role (multiple select)
            $('#role').select2({
                placeholder: 'Pilih Peran (dapat memilih lebih dari satu)',
                allowClear: true,
                theme: 'bootstrap4',
                width: '100%',
                closeOnSelect: false
            });

            // Trigger for Kepala Lab checkbox
            $('#is_kepala_lab').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#kepala-lab-info').slideDown(300);
                    // Auto-show role group if not visible
                    if ($('#role-group').is(':hidden')) {
                        $('#role-group').slideDown(300);
                    }
                    // Show lab selection info
                    $('#lab_id').closest('.form-group').find('small.text-info').html(
                        '<i class="fas fa-info-circle"></i> ' +
                        '<strong>Kepala Lab:</strong> Pilih laboratorium yang akan diawasi/divalidasi. ' +
                        'Dapat memilih lebih dari satu laboratorium.'
                    );
                } else {
                    $('#kepala-lab-info').slideUp(300);
                    // Reset lab selection info
                    $('#lab_id').closest('.form-group').find('small.text-info').html(
                        '<i class="fas fa-info-circle"></i> ' +
                        '<strong>Kepala Lab</strong> bisa sebagai validator di banyak laboratorium. ' +
                        '<strong>Petugas</strong> juga bisa menempati banyak laboratorium.'
                    );
                }
            });

            // Store role per lab data
            @php
                $rolePerLab = old('role_per_lab', []);
                // If no old data, try to distribute existing roles to labs
                if (empty($rolePerLab)) {
                    $existingRoles = is_array($petugas->role) ? $petugas->role : ($petugas->role ? [$petugas->role] : []);
                    $labIds = is_array($petugas->lab_id) ? $petugas->lab_id : ($petugas->lab_id ? [$petugas->lab_id] : []);
                    if (empty($labIds)) {
                        $rolePerLab['NON_LAB'] = $existingRoles;
                    } else {
                        // Distribute roles to all labs (for backward compatibility)
                        foreach ($labIds as $labId) {
                            $rolePerLab[$labId] = $existingRoles;
                        }
                    }
                }
            @endphp
            var rolePerLabData = @json($rolePerLab);

            // Function to update role options per lab
            function updateRolePerLab() {
                var labIds = $('#lab_id').val();
                var container = $('#role-per-lab-container');
                
                if (!labIds || labIds.length === 0) {
                    container.empty();
                    $('#role-per-lab-group').slideUp(300);
                    return;
                }

                // Show role per lab group
                $('#role-per-lab-group').slideDown(300);
                container.empty();

                // Create role select for each selected lab
                $('#lab_id option:selected').each(function() {
                    var labId = $(this).val();
                    var labName = $(this).text();
                    
                    if (labId === 'NON_LAB') {
                        // For NON_LAB, create role select
                        var roleDiv = $('<div class="form-group role-lab-input mb-3" data-lab-id="NON_LAB">' +
                            '<label for="role_per_lab_NON_LAB">Role untuk Non Lab (Pendaftaran/Kepala Lab/Keuangan)</label>' +
                            '<select class="form-control select2-multiple role-select" id="role_per_lab_NON_LAB" name="role_per_lab[NON_LAB][]" multiple style="width: 100%;">' +
                            '</select>' +
                            '</div>');
                        container.append(roleDiv);
                        
                        // Load roles for NON_LAB
                        loadRolesForLab('NON_LAB', '#role_per_lab_NON_LAB', rolePerLabData['NON_LAB'] || []);
                    } else {
                        var labCode = $(this).data('lab-code');
                        var roleDiv = $('<div class="form-group role-lab-input mb-3" data-lab-id="' + labId + '">' +
                            '<label for="role_per_lab_' + labId + '">Role untuk ' + labName + '</label>' +
                            '<select class="form-control select2-multiple role-select" id="role_per_lab_' + labId + '" name="role_per_lab[' + labId + '][]" multiple style="width: 100%;">' +
                            '</select>' +
                            '</div>');
                        container.append(roleDiv);
                        
                        // Load roles for this lab
                        loadRolesForLab(labId, '#role_per_lab_' + labId, rolePerLabData[labId] || []);
                    }
                });
            }

            // Function to load roles for a specific lab
            function loadRolesForLab(labId, selectId, selectedRoles) {
                var roleSelect = $(selectId);
                roleSelect.prop('disabled', true);
                roleSelect.html('<option>Memuat role...</option>');

                // Get all selected lab IDs including NON_LAB
                var allSelectedLabIds = $('#lab_id').val() || [];
                
                // Call API to get roles by lab (pass all selected labs to check if NON_LAB is selected, and target lab for filtering)
                $.ajax({
                    url: '{{ route("adm-petugas-get-roles-by-lab") }}',
                    method: 'GET',
                    data: { lab_ids: allSelectedLabIds, target_lab_id: labId },
                    success: function(response) {
                        roleSelect.empty();
                        
                        if (response.roles && response.roles.length > 0) {
                            $.each(response.roles, function(index, role) {
                                var selected = '';
                                if (selectedRoles.includes(role.id.toString()) || selectedRoles.includes(parseInt(role.id))) {
                                    selected = 'selected';
                                }
                                roleSelect.append('<option value="' + role.id + '" ' + selected + '>' + role.name + '</option>');
                            });
                            
                            // Initialize select2 for this select
                            roleSelect.select2({
                                placeholder: 'Pilih Role (dapat memilih lebih dari satu)',
                                allowClear: true,
                                theme: 'bootstrap4',
                                width: '100%',
                                closeOnSelect: false
                            });
                            
                            roleSelect.trigger('change');
                            
                            // If this is NON_LAB and role id=1 is selected, add it to all other lab selects
                            if (labId === 'NON_LAB') {
                                roleSelect.on('change', function() {
                                    var selectedValues = $(this).val() || [];
                                    if (selectedValues.includes('1') || selectedValues.includes(1)) {
                                        // Add role id=1 to all other lab selects
                                        $('.role-select').not(selectId).each(function() {
                                            var otherSelect = $(this);
                                            var otherValues = otherSelect.val() || [];
                                            if (!otherValues.includes('1') && !otherValues.includes(1)) {
                                                otherValues.push('1');
                                                otherSelect.val(otherValues).trigger('change');
                                            }
                                        });
                                    } else {
                                        // Remove role id=1 from all other lab selects if unselected from NON_LAB
                                        $('.role-select').not(selectId).each(function() {
                                            var otherSelect = $(this);
                                            var otherValues = otherSelect.val() || [];
                                            if (otherValues.includes('1') || otherValues.includes(1)) {
                                                otherValues = otherValues.filter(function(val) {
                                                    return val !== '1' && val !== 1;
                                                });
                                                otherSelect.val(otherValues).trigger('change');
                                            }
                                        });
                                    }
                                });
                            }
                        } else {
                            roleSelect.append('<option>Tidak ada role tersedia</option>');
                        }
                        
                        roleSelect.prop('disabled', false);
                    },
                    error: function(xhr) {
                        roleSelect.empty();
                        roleSelect.append('<option>Error memuat role</option>');
                        roleSelect.prop('disabled', false);
                        console.error('Error loading roles:', xhr);
                    }
                });
            }

            // Show/hide role dropdown based on lab selection
            $('#lab_id').on('change', function() {
                updateRolePerLab();
            });

            // Trigger on page load if lab_id has value or role exists
            var labIds = $('#lab_id').val();
            if (labIds && labIds.length > 0) {
                updateRolePerLab();
            }

            // Trigger on page load if kepala lab is checked
            if ($('#is_kepala_lab').is(':checked')) {
                $('#kepala-lab-info').show();
                // Update lab selection info
                $('#lab_id').closest('.form-group').find('small.text-info').html(
                    '<i class="fas fa-info-circle"></i> ' +
                    '<strong>Kepala Lab:</strong> Pilih laboratorium yang akan diawasi/divalidasi. ' +
                    'Dapat memilih lebih dari satu laboratorium.'
                );
            }
        });
    </script>
@endsection
