@extends('masterweb::template.admin.layout')
@section('title')
    Customer Management
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="">
                    <div class="template-demo">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/home') }}"><i
                                            class="fa fa-home menu-icon mr-1"></i>
                                        Beranda</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('/elits-customers') }}">Customer Management</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page"><span>edit</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Customer Management</h4>

            <form enctype="multipart/form-data" class="forms-sample" action="{{ route('elits-customers.update', [$id]) }}"
                method="POST">
                @csrf
                <input type="hidden" value="PUT" name="_method">

                <div class="form-group">

                    <label for="name_customer">Nama</label>
                    <input type="text" class="form-control" id="name_customer" name="name_customer"
                        value="{{ $customer->name_customer }}" placeholder="Isikan Nama" required
                        data-original-name="{{ $customer->name_customer }}">
                </div>

                <div class="form-group" id="wrap-update-nota-nama" style="display: none;">
                    <div class="alert alert-warning py-2 mb-2">
                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="update_nota_nama"
                                name="update_nota_nama" value="1">
                            <label class="form-check-label font-weight-bold" for="update_nota_nama">
                                Ganti juga nama di semua nota terkait
                                @if (($jumlah_nota ?? 0) > 0)
                                    <span class="text-muted font-weight-normal">({{ $jumlah_nota }} permohonan uji)</span>
                                @else
                                    <span class="text-muted font-weight-normal">(belum ada permohonan uji)</span>
                                @endif
                            </label>
                        </div>
                        <small class="form-text text-muted mb-0 mt-1">
                            Jika dicentang, field &quot;Telah Diterima Dari&quot; pada nota yang masih memakai nama lama
                            akan diganti ke nama baru.
                        </small>
                    </div>
                </div>

                <div class="form-group">

                    <label for="address_customer">Alamat</label>
                    <textarea class="form-control" id="address_customer" name="address_customer" placeholder="Isikan Alamat" required>{{ $customer->address_customer }}</textarea>
                </div>


                <div class="form-group">
                    <label for="kecamatan">Kecamatan</label>
                    @php
                        $kecamatan = [
                            'Bandongan',
                            'Borobudur',
                            'Candimulyo',
                            'Dukun',
                            'Grabag',
                            'Kajoran',
                            'Kaliangkrik',
                            'Mertoyudan',
                            'Mungkid',
                            'Muntilan',
                            'Ngablak',
                            'Ngluwar',
                            'Pakis',
                            'Salam',
                            'Salaman',
                            'Sawangan',
                            'Secang',
                            'Srumbung',
                            'Tegalrejo',
                            'Tempuran',
                            'Windusari',
                        ];
                    @endphp
                    <select name="kecamatan" class="form-control smt-select2" id="kecamatan" onchange="CheckKecamatan(this)">
                        <option value="" selected disabled>Pilih Kecamatan</option>
                        <option value="Bandongan" {{ $customer->kecamatan_customer == 'Bandongan' ? 'selected' : '' }}>
                            Bandongan
                        </option>
                        <option value="Borobudur" {{ $customer->kecamatan_customer == 'Borobudur' ? 'selected' : '' }}>
                            Borobudur
                        </option>
                        <option value="Candimulyo" {{ $customer->kecamatan_customer == 'Candimulyo' ? 'selected' : '' }}>
                            Candimulyo
                        </option>
                        <option value="Dukun" {{ $customer->kecamatan_customer == 'Dukun' ? 'selected' : '' }}>
                            Dukun
                        </option>
                        <option value="Grabag" {{ $customer->kecamatan_customer == 'Grabag' ? 'selected' : '' }}>
                            Grabag
                        </option>
                        <option value="Kajoran" {{ $customer->kecamatan_customer == 'Kajoran' ? 'selected' : '' }}>
                            Kajoran
                        </option>
                        <option value="Kaliangkrik" {{ $customer->kecamatan_customer == 'Kaliangkrik' ? 'selected' : '' }}>
                            Kaliangkrik
                        </option>
                        <option value="Mertoyudan" {{ $customer->kecamatan_customer == 'Mertoyudan' ? 'selected' : '' }}>
                            Mertoyudan
                        </option>
                        <option value="Mungkid" {{ $customer->kecamatan_customer == 'Mungkid' ? 'selected' : '' }}>
                            Mungkid
                        </option>
                        <option value="Muntilan" {{ $customer->kecamatan_customer == 'Muntilan' ? 'selected' : '' }}>
                            Muntilan
                        </option>
                        <option value="Ngablak" {{ $customer->kecamatan_customer == 'Ngablak' ? 'selected' : '' }}>
                            Ngablak
                        </option>
                        <option value="Ngluwar" {{ $customer->kecamatan_customer == 'Ngluwar' ? 'selected' : '' }}>
                            Ngluwar
                        </option>
                        <option value="Pakis" {{ $customer->kecamatan_customer == 'Pakis' ? 'selected' : '' }}>
                            Pakis
                        </option>
                        <option value="Salam" {{ $customer->kecamatan_customer == 'Salam' ? 'selected' : '' }}>
                            Salam
                        </option>
                        <option value="Salaman" {{ $customer->kecamatan_customer == 'Salaman' ? 'selected' : '' }}>
                            Salaman
                        </option>
                        <option value="Sawangan" {{ $customer->kecamatan_customer == 'Sawangan' ? 'selected' : '' }}>
                            Sawangan
                        </option>
                        <option value="Secang" {{ $customer->kecamatan_customer == 'Secang' ? 'selected' : '' }}>
                            Secang
                        </option>
                        <option value="Srumbung" {{ $customer->kecamatan_customer == 'Srumbung' ? 'selected' : '' }}>
                            Srumbung
                        </option>
                        <option value="Tegalrejo" {{ $customer->kecamatan_customer == 'Tegalrejo' ? 'selected' : '' }}>
                            Tegalrejo
                        </option>
                        <option value="Tempuran" {{ $customer->kecamatan_customer == 'Tempuran' ? 'selected' : '' }}>
                            Tempuran
                        </option>
                        <option value="Windusari" {{ $customer->kecamatan_customer == 'Windusari' ? 'selected' : '' }}>
                            Windusari
                        </option>
                        <option value="0"
                            {{ !in_array($customer->kecamatan_customer, $kecamatan) ? 'selected' : '' }}>Lainnya
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control mt-10" id="kecamatan_other"
                        style="{{ !in_array($customer->kecamatan_customer, $kecamatan) ? '' : 'display:none;' }}"
                        value="{{ !in_array($customer->kecamatan_customer, $kecamatan) ? $customer->kecamatan_customer : '' }}"
                        id="kecamatan_other" name="kecamatan_other" placeholder="Isikan Kecamatan Lain">
                </div>

                <div class="form-group">
                    <label for="email_customer">Email</label>
                    <input type="text" class="form-control" id="email_customer" name="email_customer"
                        value="{{ $customer->email_customer }}" placeholder="Isikan Email">
                </div>


                <div class="form-group">
                    <label for="cp_customer">Contact Person</label>
                    <textarea class="form-control" id="cp_customer" name="cp_customer" placeholder="Isikan Contact Person">{{ $customer->cp_customer }}</textarea>
                </div>


                <button type="submit" class="btn btn-primary mr-2">Simpan</button>
                <button type="button" onclick="goBack()" class="btn btn-light">Kembali</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function goBack() {
            window.history.back();
        }

        function CheckKecamatan(val) {
            var element = document.getElementById('kecamatan_other');
            if (!element) return;
            element.style.display = (val && val.value == '0') ? 'block' : 'none';
        }

        $(document).ready(function() {
            var $nameInput = $('#name_customer');
            var $wrapNota = $('#wrap-update-nota-nama');
            var $checkNota = $('#update_nota_nama');
            var $form = $nameInput.closest('form');
            var originalName = ($nameInput.attr('data-original-name') || $nameInput.data('original-name') || '')
                .toString().trim();
            var jumlahNota = parseInt(@json((int) ($jumlah_nota ?? 0)), 10) || 0;
            var confirmHandled = false;

            function toggleNotaOption() {
                var current = ($nameInput.val() || '').toString().trim();
                var changed = current !== originalName && current !== '';
                if (changed) {
                    $wrapNota.show();
                } else {
                    $checkNota.prop('checked', false);
                    $wrapNota.hide();
                }
            }

            $nameInput.on('input change keyup paste', toggleNotaOption);
            toggleNotaOption();

            $form.on('submit', function(e) {
                if (confirmHandled) {
                    confirmHandled = false;
                    return true;
                }

                var current = ($nameInput.val() || '').toString().trim();
                if (current === originalName) {
                    return true;
                }

                e.preventDefault();
                e.stopImmediatePropagation();

                var formEl = this;
                var pesan = jumlahNota > 0
                    ? 'Apakah ingin mengganti juga nama di semua nota terkait (' + jumlahNota + ' permohonan uji)?'
                    : 'Nama customer diubah. Tidak ada permohonan uji terkait untuk diganti di nota.';

                function lanjutSubmit(syncNota) {
                    if (syncNota) {
                        $checkNota.prop('checked', true);
                    } else {
                        $checkNota.prop('checked', false);
                    }
                    confirmHandled = true;
                    // native submit agar tidak loop ke handler jQuery
                    HTMLFormElement.prototype.submit.call(formEl);
                }

                if (typeof swal === 'function') {
                    swal({
                        title: 'Nama customer diubah',
                        text: pesan,
                        icon: 'warning',
                        buttons: jumlahNota > 0 ? {
                            cancel: 'Tidak, simpan nama saja',
                            confirm: {
                                text: 'Ya, ganti juga di nota',
                                value: true
                            }
                        } : {
                            confirm: {
                                text: 'OK, simpan',
                                value: false
                            }
                        }
                    }).then(function(willSync) {
                        lanjutSubmit(willSync === true);
                    });
                } else {
                    var sync = jumlahNota > 0
                        ? window.confirm(pesan)
                        : false;
                    lanjutSubmit(sync);
                }

                return false;
            });
        });
    </script>
@endsection
