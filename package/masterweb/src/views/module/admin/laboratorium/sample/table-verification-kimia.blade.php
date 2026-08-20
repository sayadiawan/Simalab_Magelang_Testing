<table class="table table-bordered">
    <thead>
        <tr>
            <th scope="col" class="border border-primary">Jenis Kegiatan Lab Kesmas</th>
            <th scope="col" class="border border-primary">Tanggal Mulai</th>
            <th scope="col" class="border border-primary">Tanggal Selesai</th>
            <th scope="col" class="border border-primary">Nama Petugas</th>
            <th scope="col" class="text-center border border-primary">Action</th>
        </tr>
    </thead>
    <tbody>
        <tr id="registrasi">
            <th scope="row">Pendaftaran / Registrasi</th>
            @if (isset($listVerifications[1]))
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatDate($listVerifications[1]->start_date) }}</td>
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatDate($listVerifications[1]->stop_date) }}</td>
                <td>{{ $listVerifications[1]->nama_petugas }}</td>
                <td class="text-center">
                    <svg id="toggle-registrasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="48"
                        height="48" viewBox="0 0 48 48" style="cursor: pointer;">
                        <title>Edit Pendaftaran / Registrasi</title>
                        <path fill="#c8e6c9"
                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                        </path>
                        <path fill="#4caf50"
                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                        </path>
                    </svg>
                </td>
            @else
                <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}"
                    method="post" class="formPendaftaran">
                    @csrf
                  <td><input id="start_date_registrasi" type="datetime-local" class="form-control datetime" name="start_date" required>
                    <input type="number" value="{{ 1 }}" name="verification_step" hidden>
                    </td>
                    <td><input id="stop_date_registrasi" type="datetime-local" class="form-control datetime" name="stop_date" required>
                    </td>
                    <td>
                        <select name="nama_petugas" id="namaPetugasPendaftaran" required>
                            @php
                                $list_name_petugas = explode(', ', $verificationActivity[0]->register);
                                $default_pendaftar = $permohonanUji->petugas_penerima ?? '';
                            @endphp
                            @foreach ($list_name_petugas as $nama_petugas)
                                <option value="{{ $nama_petugas }}"
                                    {{ trim($default_pendaftar) === trim($nama_petugas) ? 'selected' : '' }}>
                                    {{ $nama_petugas }}
                                </option>
                            @endforeach
                        </select>


                    <td class="text-center"><button type="submit" class="btn btn-success"
                            onclick="checkNikAndPassword(document.getElementById('namaPetugasPendaftaran').value, 'formPendaftaran')">Selesai</button>
                    </td>

                </form>
            @endif
        </tr>
        <tr id="registrasi-update" style="display: none;">
            <th scope="row">Pendaftaran / Registrasi</th>
            <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}" method="post"
                class="formPendaftaranUpdate">
                @csrf
                <td><input id="start_date_registrasi_update" type="text" class="form-control datetime" name="start_date"
                        value="{{ isset($listVerifications[1]) ? \Smt\Masterweb\Helpers\DateHelper::formatDateTimePicker($listVerifications[1]->start_date) : '' }}"
                        required><input type="number" value="{{ 1 }}" name="verification_step" hidden></td>
                <td><input id="stop_date_registrasi_update" type="text" class="form-control datetime" name="stop_date"
                        value="{{ isset($listVerifications[1]) ? \Smt\Masterweb\Helpers\DateHelper::formatDateTimePicker($listVerifications[1]->stop_date) : '' }}"
                        required></td>
                <td>
                    <select name="nama_petugas" id="namaPetugasPendaftaranUpdate" required>
                        @php
                            $list_name_petugas = explode(', ', $verificationActivity[0]->register);
                            $stored_pendaftar = $listVerifications[1]->nama_petugas ?? ($permohonanUji->petugas_penerima ?? '');
                        @endphp
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ $nama_petugas }}"
                                {{ trim($stored_pendaftar) === trim($nama_petugas) ? 'selected' : '' }}>
                                {{ $nama_petugas }}
                            </option>
                        @endforeach
                    </select>

                <td class="text-center"><button type="submit" class="btn btn-success"
                        onclick="checkNikAndPassword(document.getElementById('namaPetugasPendaftaranUpdate').value, 'formPendaftaranUpdate')">Selesai</button>
                </td>
            </form>
        </tr>

        {{-- Pengambilan Sampel --}}
        @if (isset($sample->permohonanuji) && $sample->permohonanuji->is_sampling == 1)
            <tr id="pengambilan-sampel">
                <th scope="row">Pengambilan Sampel</th>
                @if (isset($listVerifications[6]))
                    <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatDate($listVerifications[6]->start_date) }}</td>
                    <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatDate($listVerifications[6]->stop_date) }}</td>
                    <td>{{ $listVerifications[6]->nama_petugas }}</td>
                    <td class="text-center">
                        <svg id="toggle-pengambilan-sampel" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                            width="48" height="48" viewBox="0 0 48 48" style="cursor: pointer;">
                            <title>Edit Pengambilan Sampel</title>
                            <path fill="#c8e6c9"
                                d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                            </path>
                            <path fill="#4caf50"
                                d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                            </path>
                        </svg>
                    </td>
                @else
                    <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}"
                        method="post" class="formPengambilanSampel">
                        @csrf
                        <td><input type="datetime-local" class="form-control datetime" name="start_date"
                                id="start_date_pengambilan" required>
                                <input type="number" value="{{ 6 }}" name="verification_step" hidden>
                       
                        </td>
                        <td><input type="datetime-local" class="form-control datetime" name="stop_date"
                                id="stop_date_pengambilan" required>
                        </td>
                        <td>
                            <select name="nama_petugas" id="namaPetugasPengambilan" required>
                                @php
                                    // Gabungkan semua petugas: register, kimia, dan mikro
                                    $petugas_register = explode(', ', $verificationActivity[0]->register ?? '');
                                    $petugas_kimia = explode(', ', $verificationActivity[5]->kimia ?? '');
                                    $petugas_mikro = explode(', ', $verificationActivity[5]->mikro ?? '');

                                    // Gabungkan dan hilangkan duplikat
                                    $list_name_petugas = array_unique(
                                        array_merge($petugas_register, $petugas_kimia, $petugas_mikro),
                                    );
                                    // Hilangkan string kosong
                                    $list_name_petugas = array_filter($list_name_petugas, function ($value) {
                                        return trim($value) !== '';
                                    });
                                @endphp
                                @foreach ($list_name_petugas as $nama_petugas)
                                    <option value="{{ trim($nama_petugas) }}">{{ trim($nama_petugas) }}
                                    </option>
                                @endforeach

                            </select>
                        </td>
                        <td class="text-center"><button type="submit" class="btn btn-success"
                                @if (!isset($listVerifications[1])) disabled @endif
                                onclick="checkNikAndPassword(document.getElementById('namaPetugasPengambilan').value, 'formPengambilanSampel')">Selesai</button>
                        </td>
                    </form>
                @endif
            </tr>
            <tr id="pengambilan-sampel-update" style="display: none;">
                <th scope="row">Pengambilan Sampel</th>
                <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}"
                    method="post" class="formPengambilanSampelUpdate">
                    @csrf
                    <td><input type="text" class="form-control datetime" name="start_date"
                            value="{{ isset($listVerifications[6]) ? \Smt\Masterweb\Helpers\DateHelper::formatDateTimePicker($listVerifications[6]->start_date) : '' }}"
                            required><input type="number" value="{{ 6 }}" name="verification_step" hidden></td>
                    <td><input type="text" class="form-control datetime" name="stop_date"
                            value="{{ isset($listVerifications[6]) ? \Smt\Masterweb\Helpers\DateHelper::formatDateTimePicker($listVerifications[6]->stop_date) : '' }}"
                            required></td>
                    <td>
                        <select name="nama_petugas" id="namaPetugasPengambilanUpdate" required>
                            @php
                                // Gabungkan semua petugas: register, kimia, dan mikro
                                $petugas_register = explode(', ', $verificationActivity[0]->register ?? '');
                                $petugas_kimia = explode(', ', $verificationActivity[5]->kimia ?? '');
                                $petugas_mikro = explode(', ', $verificationActivity[5]->mikro ?? '');

                                // Gabungkan dan hilangkan duplikat
                                $list_name_petugas = array_unique(
                                    array_merge($petugas_register, $petugas_kimia, $petugas_mikro),
                                );
                                // Hilangkan string kosong
                                $list_name_petugas = array_filter($list_name_petugas, function ($value) {
                                    return trim($value) !== '';
                                });
                            @endphp
                            @foreach ($list_name_petugas as $nama_petugas)
                                <option value="{{ trim($nama_petugas) }}"
                                    {{ isset($listVerifications[6]->nama_petugas) && trim($listVerifications[6]->nama_petugas) == trim($nama_petugas) ? 'selected' : '' }}>
                                    {{ trim($nama_petugas) }}
                                </option>
                            @endforeach

                        </select>

                    <td class="text-center"><button type="submit" class="btn btn-success"
                            @if (!isset($listVerifications[1])) disabled @endif
                            onclick="checkNikAndPassword(document.getElementById('namaPetugasPengambilanUpdate').value, 'formPengambilanSampelUpdate')">Selesai</button>
                    </td>
                </form>
            </tr>
        @endif

        {{-- Penerimaan Sampel --}}
        <tr id="penerimaan-sampel">
            <th scope="row">Penerimaan Sampel</th>
            @if (isset($listVerifications[7]))
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatDate($listVerifications[7]->start_date) }}</td>
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatDate($listVerifications[7]->stop_date) }}</td>
                <td>{{ $listVerifications[7]->nama_petugas }}</td>
                <td class="text-center">
                    <a href="{{ route('elits-samples.penerimaan-sampel-form', [$sample->id_samples, $sample->permohonan_uji_id, $sample->id_laboratorium]) }}"
                        class="btn btn-info btn-sm">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                </td>
            @else
                @php
                    $is_sampling = isset($sample->permohonanuji) && $sample->permohonanuji->is_sampling == 1;
                    $pengambilan_done = isset($listVerifications[6]) && $listVerifications[6]->is_done == 1;
                    $registrasi_done = isset($listVerifications[1]) && $listVerifications[1]->is_done == 1;

                    // Aktif jika: (is_sampling=1 AND pengambilan done) OR (is_sampling=0 AND registrasi done)
                    $can_submit = ($is_sampling && $pengambilan_done) || (!$is_sampling && $registrasi_done);
                @endphp
                <td colspan="3" class="text-center">
                    <p class="mb-2">Klik tombol di bawah untuk mengisi data penerimaan sampel secara massal</p>
                </td>
                <td class="text-center">
                    <a href="{{ route('elits-samples.penerimaan-sampel-form', [$sample->id_samples, $sample->permohonan_uji_id, $sample->id_laboratorium]) }}"
                        class="btn btn-success"
                        @if (!$can_submit) onclick="return false;" style="opacity: 0.5; cursor: not-allowed;" @endif>
                        Input Penerimaan Sampel
                    </a>
                </td>
            @endif
        </tr>
        <tr id="penerimaan-sampel-update" style="display: none;">
            <th scope="row">Penerimaan Sampel</th>
            <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}" method="post"
                class="formPenerimaanSampelUpdate">
                @csrf
                <td><input type="text" class="form-control datetime" name="start_date"
                        value="{{ isset($listVerifications[7]) ? \Smt\Masterweb\Helpers\DateHelper::formatDateTimePicker($listVerifications[7]->start_date) : '' }}"
                        required><input type="number" value="{{ 7 }}" name="verification_step" hidden></td>
                <td><input type="text" class="form-control datetime" name="stop_date"
                        value="{{ isset($listVerifications[7]) ? \Smt\Masterweb\Helpers\DateHelper::formatDateTimePicker($listVerifications[7]->stop_date) : '' }}"
                        required></td>
                <td>
                    <select name="nama_petugas" id="namaPetugasPenerimaanUpdate" required>
                        @php
                            if ($sample->kode_laboratorium == 'MBI') {
                                $list_name_petugas = explode(', ', $verificationActivity[6]->mikro ?? '');
                            } elseif ($sample->kode_laboratorium == 'KIM') {
                                $list_name_petugas = explode(', ', $verificationActivity[6]->kimia ?? '');
                            } else {
                                $list_name_petugas = explode(', ', $verificationActivity[6]->klnik ?? '');
                            }
                            // Hilangkan string kosong
                            $list_name_petugas = array_filter($list_name_petugas, function ($value) {
                                return trim($value) !== '';
                            });
                        @endphp
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ trim($nama_petugas) }}"
                                {{ isset($listVerifications[7]->nama_petugas) && trim($listVerifications[7]->nama_petugas) == trim($nama_petugas) ? 'selected' : '' }}>
                                {{ trim($nama_petugas) }}
                            </option>
                        @endforeach

                    </select>

                <td class="text-center"><button type="submit" class="btn btn-success"
                        @php
$is_sampling = isset($sample->permohonanuji) && $sample->permohonanuji->is_sampling == 1;
                            $pengambilan_done = isset($listVerifications[6]) && $listVerifications[6]->is_done == 1;
                            $registrasi_done = isset($listVerifications[1]) && $listVerifications[1]->is_done == 1;
                            
                            $can_submit = ($is_sampling && $pengambilan_done) || (!$is_sampling && $registrasi_done); @endphp
                        @if (!$can_submit) disabled @endif
                        onclick="checkNikAndPassword(document.getElementById('namaPetugasPenerimaanUpdate').value, 'formPenerimaanSampelUpdate')">Selesai</button>
                </td>
            </form>
        </tr>

     

        <tr id="analitik">
            <th scope="row">Pemeriksaan / Analitik</th>
            @if (isset($listVerifications[2]))
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[2]->start_date) }}</td>
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[2]->stop_date) }}</td>
                <td>{{ $listVerifications[2]->nama_petugas }}</td>
                <td class="text-center">
                    <svg id="toggle-analitik" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="48"
                        height="48" viewBox="0 0 48 48" style="cursor: pointer;">
                        <title>Edit Pemeriksaan / Analitik</title>
                        <path fill="#c8e6c9"
                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                        </path>
                        <path fill="#4caf50"
                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                        </path>
                    </svg>
                </td>
            @else
        
                <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}"
                    method="post" class="formPemeriksaan">
                    @csrf
                    <td><input type="text" class="form-control datetime" name="start_date"
                            id="start_date_pemeriksaan" required>
                            <input type="number" value="{{ 2 }}" name="verification_step" hidden>
                        </td>
                    <td><input type="text" class="form-control datetime" name="stop_date"
                            id="stop_date_pemeriksaan" required></td>
                    <td>
                        <select name="nama_petugas" id="namaPetugasPemeriksaan" required>
                            @php
                                if ($sample->kode_laboratorium == 'MBI') {
                                    # code...
                                    $list_name_petugas = explode(', ', $verificationActivity[1]->mikro);
                                } elseif ($sample->kode_laboratorium == 'KIM') {
                                    # code...
                                    $list_name_petugas = explode(', ', $verificationActivity[1]->kimia);
                                } else {
                                    $list_name_petugas = explode(', ', $verificationActivity[1]->klnik);
                                }
                            @endphp
                            @foreach ($list_name_petugas as $nama_petugas)
                                <option value="{{ $nama_petugas }}"
                                    {{ $default_analis == $nama_petugas ? 'selected' : '' }}>{{ $nama_petugas }}
                                </option>
                            @endforeach

                        </select>
                        {{-- <input type="text" class="form-control datetime" placeholder="Nama Petugas" value="{{ Auth()->user()->name }}"
            name="nama_petugas" required> --}}
                    </td>
                    <td class="text-center"><button type="submit" class="btn btn-success"
                            @if (!isset($listVerifications[7]) || (isset($listVerifications[7]) && $listVerifications[7]->is_done != 1)) disabled @endif
                            @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasPemeriksaan').value, 'formPemeriksaan')" @endif>Selesai</button>
                    </td>
                </form>
            @endif
        </tr>
        <tr id="analitik-update" style="display: none;">
            <th scope="row">Pemeriksaan / Analitik</th>
            <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}" method="post"
                class="formPemeriksaanUpdate">
                @csrf
                <td><input type="text" class="form-control datetime" name="start_date"
                        value="{{ isset($listVerifications[2]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[2]->start_date) : '' }}"
                        required>
                        <input type="number" value="{{ 2 }}" name="verification_step" hidden>
             
                </td>
                <td><input type="text" class="form-control datetime" name="stop_date"
                        value="{{ isset($listVerifications[2]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[2]->stop_date) : '' }}"
                        required></td>
                <td>
                    <select name="nama_petugas" id="namaPetugasPemeriksaanUpdate" required>
                        @php
                            if ($sample->kode_laboratorium == 'MBI') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[1]->mikro);
                            } elseif ($sample->kode_laboratorium == 'KIM') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[1]->kimia);
                            } else {
                                $list_name_petugas = explode(', ', $verificationActivity[1]->klnik);
                            }
                        @endphp
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                            </option>
                        @endforeach

                    </select>
                    {{-- <input type="text" class="form-control datetime" placeholder="Nama Petugas" value="{{ Auth()->user()->name }}"
            name="nama_petugas" required> --}}
                </td>
                <td class="text-center"><button type="submit" class="btn btn-success"
                        @if (!isset($listVerifications[7]) || (isset($listVerifications[7]) && $listVerifications[7]->is_done != 1)) disabled @endif
                        @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasPemeriksaanUpdate').value, 'formPemeriksaanUpdate')" @endif>Selesai</button>
                </td>
            </form>
        </tr>
        <tr id="baca-hasil">
            <th scope="row">Input / Output Hasil Px</th>
            @if (isset($listVerifications[3]))
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[3]->start_date) }}</td>
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[3]->stop_date) }}</td>
                <td>{{ $listVerifications[3]->nama_petugas }}</td>
                <td class="text-center">
                    <svg id="toggle-baca-hasil" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="48"
                        height="48" viewBox="0 0 48 48" style="cursor: pointer">
                        <title>Edit Baca Hasil</title>
                        <path fill="#c8e6c9"
                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                        </path>
                        <path fill="#4caf50"
                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                        </path>
                    </svg>
                </td>
            @else
                <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}"
                    method="post" class="formInputHasil">
                    @csrf
                    <td><input type="text" class="form-control datetime" name="start_date"
                            id="start_date_input" required><input type="number" value="{{ 3 }}" name="verification_step" hidden></td>
                    <td><input type="text" class="form-control datetime" name="stop_date"
                            id="stop_date_input" required></td>

                    <td>
                        @php
                            if ($sample->kode_laboratorium == 'MBI') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[2]->mikro);
                            } elseif ($sample->kode_laboratorium == 'KIM') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[2]->kimia);
                            } else {
                                $list_name_petugas = explode(', ', $verificationActivity[2]->klnik);
                            }
                        @endphp
                        <select name="nama_petugas" id="namaPetugasInputHasil" required>
                            @foreach ($list_name_petugas as $nama_petugas)
                                <option value="{{ $nama_petugas }}"
                                    {{ $default_analis == $nama_petugas ? 'selected' : '' }}>{{ $nama_petugas }}
                                </option>
                            @endforeach

                        </select>
                        {{-- <input type="text" class="form-control datetime" placeholder="Nama Petugas" value="{{ Auth()->user()->name }}"
            name="nama_petugas" required> --}}
                    </td>

                    <td class="text-center">
                        <button type="submit" class="btn btn-success"
                            @if (!isset($listVerifications[2])) disabled @endif
                            @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasInputHasil').value, 'formInputHasil')" @endif>Selesai</button>
                </form>
                <a
                    href="{{ route('elits-baca-hasil.index', [$sample->id_samples, $lab_num->lab_id, optional($labAnalitikProgres)->id_laboratorium_progress ?? '57f7f491-288c-4c48-afa8-a9669bb62180']) }}"><button
                        type="button" class="btn btn-primary"
                        @if (!isset($listVerifications[2])) disabled @endif>Input
                        Hasil</button></a>
                </td>
            @endif
        </tr>
        <tr id="baca-hasil-update" style="display: none;">
            <th scope="row">Input / Output Hasil Px</th>
            <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}" method="post"
                class="formInputHasilUpdate">
                @csrf
               <td><input type="text" class="form-control datetime" name="start_date"
                        value="{{ isset($listVerifications[3]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[3]->start_date) : '' }}"
                        required><input type="number" value="{{ 3 }}" name="verification_step" hidden></td>
                <td><input type="text" class="form-control datetime" name="stop_date"
                        value="{{ isset($listVerifications[3]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[3]->stop_date) : '' }}"
                        required></td>

                <td>
                    @php
                        if ($sample->kode_laboratorium == 'MBI') {
                            # code...
                            $list_name_petugas = explode(', ', $verificationActivity[2]->mikro);
                        } elseif ($sample->kode_laboratorium == 'KIM') {
                            # code...
                            $list_name_petugas = explode(', ', $verificationActivity[2]->kimia);
                        } else {
                            $list_name_petugas = explode(', ', $verificationActivity[2]->klnik);
                        }
                    @endphp
                    <select name="nama_petugas" id="namaPetugasInputHasilUpdate" required>
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                            </option>
                        @endforeach

                    </select>
                    {{-- <input type="text" class="form-control datetime" placeholder="Nama Petugas" value="{{ Auth()->user()->name }}"
            name="nama_petugas" required> --}}
                </td>
                
               
                <td class="text-center">
                    <button type="submit" class="btn btn-success" @if (!isset($listVerifications[2])) disabled @endif
                        @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasInputHasilUpdate').value, 'formInputHasilUpdate')" @endif>Selesai</button>
            </form>
            <a
                href="{{ route('elits-baca-hasil.index', [$sample->id_samples, $lab_num->lab_id, optional($labAnalitikProgres)->id_laboratorium_progress ?? '57f7f491-288c-4c48-afa8-a9669bb62180']) }}"><button
                    type="button" class="btn btn-primary" @if (!isset($listVerifications[2])) disabled @endif>Input
                    Hasil</button></a>
            </td>
        </tr>
        <tr id="verifikasi">
            <th scope="row">Verifikasi</th>
            @if (isset($listVerifications[4]))
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[4]->start_date) }}</td>
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[4]->stop_date) }}</td>
                <td>{{ $listVerifications[4]->nama_petugas }}</td>
                <td class="text-center">
                    <svg id="toggle-verifikasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="48"
                        height="48" viewBox="0 0 48 48" style="cursor: pointer;">
                        <title>Edit Verifikasi</title>
                        <path fill="#c8e6c9"
                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z"></path>
                        <path fill="#4caf50"
                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                        </path>
                    </svg>
                </td>
            @else
                <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}"
                    method="post" class="formVerifikasi">
                    @csrf
                    <td><input type="text" class="form-control datetime" name="start_date"
                            id="start_date_verifikasi" required><input type="number" value="{{ 4 }}" name="verification_step" hidden></td>
                    </td>
                    <td><input type="text" class="form-control datetime" name="stop_date"
                            id="stop_date_verifikasi" required></td>
                    <td>
                        <select name="nama_petugas" id="namaPetugasVerifikasi" required>
                            @php
                                if ($sample->kode_laboratorium == 'MBI') {
                                    # code...
                                    $list_name_petugas = explode(', ', $verificationActivity[3]->mikro);
                                } elseif ($sample->kode_laboratorium == 'KIM') {
                                    # code...
                                    $list_name_petugas = explode(', ', $verificationActivity[3]->kimia);
                                } else {
                                    $list_name_petugas = explode(', ', $verificationActivity[3]->klnik);
                                }
                            @endphp
                            @foreach ($list_name_petugas as $nama_petugas)
                                <option value="{{ $nama_petugas }}"
                                    {{ $default_koordinator_kesmas == $nama_petugas ? 'selected' : '' }}>
                                    {{ $nama_petugas }}
                                </option>
                            @endforeach

                        </select>

                    </td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-success"
                            @if (!isset($listVerifications[3]) or !isset($verifikasiHasil) or !isset($verifikasiHasil->verifikasi_hasil_date)) disabled @endif
                            @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasVerifikasi').value, 'formVerifikasi')" @endif>Selesai</button>
                </form>
                <a href="{{ route('elits-verifikasi-hasil.index', [$sample->id_samples, $lab_num->lab_id]) }}"><button
                        type="button" class="btn btn-primary"
                        @if (!isset($listVerifications[3])) disabled @endif>Verifikasi
                        Hasil</button></a>
                </td>
            @endif
        </tr>
        <tr id="verifikasi-update" style="display:none;">
            <th scope="row">Verifikasi</th>
            <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}" method="post"
                class="formVerifikasiUpdate">
                @csrf
               <td><input type="text" class="form-control datetime" name="start_date"
                        value="{{ isset($listVerifications[4]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[4]->start_date) : '' }}"
                        required><input type="number" value="{{ 4 }}" name="verification_step" hidden></td>
                </td>
                <td><input type="text" class="form-control datetime" name="stop_date"
                        value="{{ isset($listVerifications[4]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[4]->stop_date) : '' }}"
                        required></td>
                <td>
                    <select name="nama_petugas" id="namaPetugasVerifikasiUpdate" required>
                        @php
                            if ($sample->kode_laboratorium == 'MBI') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[3]->mikro);
                            } elseif ($sample->kode_laboratorium == 'KIM') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[3]->kimia);
                            } else {
                                $list_name_petugas = explode(', ', $verificationActivity[3]->klnik);
                            }
                        @endphp
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                            </option>
                        @endforeach

                    </select>

                </td>
                <td class="text-center">
                    <button type="submit" class="btn btn-success" @if (!isset($listVerifications[3]) or !isset($verifikasiHasil) or !isset($verifikasiHasil->verifikasi_hasil_date)) disabled @endif
                        @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasVerifikasiUpdate').value, 'formVerifikasiUpdate')" @endif>Verifikasi</button>
            </form>
            <a href="{{ route('elits-verifikasi-hasil.index', [$sample->id_samples, $lab_num->lab_id]) }}"><button
                    type="button" class="btn btn-primary"
                    @if (!isset($listVerifications[3])) disabled @endif>Verifikasi
                    Hasil</button></a>
            </td>
        </tr>
        <tr id="validasi">
            <th scope="row">Validasi</th>
            @if (isset($listVerifications[5]))
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[5]->start_date) }}</td>
                <td>{{ \Smt\Masterweb\Helpers\DateHelper::formatOnlyDate($listVerifications[5]->stop_date) }}</td>
                <td>{{ $listVerifications[5]->nama_petugas }}</td>
                <td class="text-center">
                    <svg id="toggle-validasi" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="48"
                        height="48" viewBox="0 0 48 48" style="cursor: pointer;">
                        <title>Edit Validasi</title>
                        <path fill="#c8e6c9"
                            d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z">
                        </path>
                        <path fill="#4caf50"
                            d="M34.586,14.586l-13.57,13.586l-5.602-5.586l-2.828,2.828l8.434,8.414l16.395-16.414L34.586,14.586z">
                        </path>
                    </svg>
                </td>
            @else
                <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}"
                    method="post" class="formValidasi">
                    @csrf
                     <td><input type="text" class="form-control datetime" name="start_date"
                            id="start_date_validasi" required><input type="number" value="{{ 5 }}" name="verification_step" hidden></td>
                    <td><input type="text" class="form-control datetime" name="stop_date"
                            id="stop_date_validasi" required></td>
                    <td>
                        <select name="nama_petugas" id="namaPetugasValidasi" required>
                            @php
                                if ($sample->kode_laboratorium == 'MBI') {
                                    # code...
                                    $list_name_petugas = explode(', ', $verificationActivity[4]->mikro);
                                } elseif ($sample->kode_laboratorium == 'KIM') {
                                    # code...
                                    $list_name_petugas = explode(', ', $verificationActivity[4]->kimia);
                                } else {
                                    $list_name_petugas = explode(', ', $verificationActivity[4]->klnik);
                                }
                            @endphp
                            @foreach ($list_name_petugas as $nama_petugas)
                                <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                                </option>
                            @endforeach

                        </select>

                    </td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-success"
                            @if (!isset($listVerifications[4]) or !isset($pengesahanHasil) or !isset($pengesahanHasil->pengesahan_hasil_date)) disabled @endif
                            @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasValidasi').value, 'formValidasi')" @endif>Selesai</button>
                </form>
                <a href="{{ route('elits-pengesahan-hasil.index', [$sample->id_samples, $lab_num->lab_id]) }}"><button
                        type="button" class="btn btn-primary"
                        @if (!isset($listVerifications[4])) disabled @endif>Validasi
                        Hasil</button></a>
                </td>
            @endif
        </tr>
        <tr id="validasi-update" style="display: none;">
            <th scope="row">Validasi</th>
            <form action="{{ route('elits-samples.verification-analytic-2', [$sample->id_samples]) }}" method="post"
                class="formValidasiUpdate">
                @csrf
                <td><input type="text" class="form-control datetime" name="start_date"
                        value="{{ isset($listVerifications[5]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[5]->start_date) : '' }}"
                        required><input type="number" value="{{ 5 }}" name="verification_step" hidden></td>
                <td><input type="text" class="form-control datetime" name="stop_date"
                        style="background-color: white!important;"
                        value="{{ isset($listVerifications[5]) ? \Smt\Masterweb\Helpers\DateHelper::formatDatePicker($listVerifications[5]->stop_date) : '' }}"
                        required></td>
                <td>
                    <select name="nama_petugas" id="namaPetugasValidasiUpdate" required>
                        @php
                            if ($sample->kode_laboratorium == 'MBI') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[4]->mikro);
                            } elseif ($sample->kode_laboratorium == 'KIM') {
                                # code...
                                $list_name_petugas = explode(', ', $verificationActivity[4]->kimia);
                            } else {
                                $list_name_petugas = explode(', ', $verificationActivity[4]->klnik);
                            }
                        @endphp
                        @foreach ($list_name_petugas as $nama_petugas)
                            <option value="{{ $nama_petugas }}">{{ $nama_petugas }}
                            </option>
                        @endforeach

                    </select>

                </td>
                <td class="text-center">
                    <button type="submit" class="btn btn-success" @if (!isset($listVerifications[4]) or !isset($pengesahanHasil) or !isset($pengesahanHasil->pengesahan_hasil_date)) disabled @endif
                        @if (config('app.bsre_use', false)) onclick="checkNikAndPassword(document.getElementById('namaPetugasValidasiUpdate').value, 'formValidasiUpdate')" @endif>Selesai</button>
            </form>
            <a href="{{ route('elits-pengesahan-hasil.index', [$sample->id_samples, $lab_num->lab_id]) }}"><button
                    type="button" class="btn btn-primary"
                    @if (!isset($listVerifications[4])) disabled @endif>Validasi
                    Hasil</button></a>
            </td>
        </tr>
    </tbody>
</table>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        let pendaftaranStop, pengambilanStart, pengambilanStop, penerimaanStart, penerimaanStop,
            pemeriksaanStart, pemeriksaanStop, inputStart,
            inputStop, verifikasiStart,
            verifikasiStop, validasiStart, validasiStop;

            // 1. Pendaftaran
        @if (isset($listVerifications[1]))
            pendaftaranStop = new Date("{{ $listVerifications[1]->stop_date }}");
            pendaftaranStart = new Date("{{ $listVerifications[1]->start_date }}");
        @else
            pendaftaranStop = new Date();
            pendaftaranStart = new Date();
        @endif

        // 1.5 Pengambilan Sampel
        @if (isset($listVerifications[6]))
            // Gunakan data yang sudah tersimpan
            pengambilanStart = new Date("{{ $listVerifications[6]->start_date }}");
            pengambilanStop = new Date("{{ $listVerifications[6]->stop_date }}");
        @else
            // Default: gunakan tanggal setelah pendaftaran
            pengambilanStart = new Date(pendaftaranStop);
            adjustToWorkHours(pengambilanStart);
            pengambilanStop = new Date(pengambilanStart);
            adjustToWorkHours(pengambilanStop);
        @endif

        // 1.6 Penerimaan Sampel
        @if (isset($listVerifications[7]))
            // Gunakan data yang sudah tersimpan
            penerimaanStart = new Date("{{ $listVerifications[7]->start_date }}");
            penerimaanStop = new Date("{{ $listVerifications[7]->stop_date }}");
        @else
            // Default: setelah pengambilan sampel (jika is_sampling=1) atau setelah pendaftaran (jika is_sampling=0)
            @php
                $is_sampling = isset($sample->permohonanuji) && $sample->permohonanuji->is_sampling == 1;
            @endphp
            @if ($is_sampling)
                penerimaanStart = new Date(pengambilanStop);
            @else
                penerimaanStart = new Date(pendaftaranStop);
            @endif
            adjustToWorkHours(penerimaanStart);
            penerimaanStop = new Date(penerimaanStart);
            adjustToWorkHours(penerimaanStop);
        @endif

        // 2. Pemeriksaan (Start dari Stop Penerimaan)
        @if (isset($listVerifications[2]))
            pemeriksaanStart = new Date("{{ $listVerifications[2]->start_date }}");
            pemeriksaanStop = new Date("{{ $listVerifications[2]->stop_date }}");
        @else
            pemeriksaanStart = new Date(pendaftaranStop); // Pemeriksaan Start = Pendaftaran Stop
            adjustToWorkHours(pemeriksaanStart);

            pemeriksaanStop = new Date(pemeriksaanStart);
            pemeriksaanStop.setDate(pemeriksaanStop.getDate() + 2); // +2 hari dari Pemeriksaan Start
            adjustToWorkHours(pemeriksaanStop);
        @endif

        // 3. Input / Output (Start dari Stop Pemeriksaan)
        @if (isset($listVerifications[3]))
            inputStart = new Date("{{ $listVerifications[3]->start_date }}");
            inputStop = new Date("{{ $listVerifications[3]->stop_date }}");
        @else
            inputStart = new Date(pemeriksaanStop); // Input Start = Pemeriksaan Stop
            adjustToWorkHours(inputStart);

            inputStop = new Date(inputStart.getTime() + 10 * 60000); // +10 menit dari Input Start
            adjustToWorkHours(inputStop);
        @endif

        // 4. Verifikasi (Start dari Stop Input)
        @if (isset($listVerifications[4]))
            verifikasiStart = new Date("{{ $listVerifications[4]->start_date }}");
            verifikasiStop = new Date("{{ $listVerifications[4]->stop_date }}");
        @else
            verifikasiStart = new Date(inputStop); // Verifikasi Start = Input Stop
            adjustToWorkHours(verifikasiStart);

            verifikasiStop = new Date(verifikasiStart.getTime() + 1 * 3600000); // +1 jam dari Verifikasi Start
            adjustToWorkHours(verifikasiStop);
        @endif

        // 5. Validasi (Start dari Stop Verifikasi)
        @if (isset($listVerifications[5]))
            validasiStart = new Date("{{ $listVerifications[5]->start_date }}");
            validasiStop = new Date("{{ $listVerifications[5]->stop_date }}");
        @else
            validasiStart = new Date(verifikasiStop); // Validasi Start = Verifikasi Stop
            adjustToWorkHours(validasiStart);

            validasiStop = new Date(validasiStart.getTime() + 1 * 3600000); // +1 jam dari Validasi Start
            adjustToWorkHours(validasiStop);
        @endif

        // Populate the date fields in the HTML
        if (document.querySelector('#start_date_registrasi')) {
            const start_date_registrasi = flatpickr("#start_date_registrasi", {
                enableTime: true,
                allowInput: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true
            });

            start_date_registrasi.setDate(formatDate(pendaftaranStart), true);

            $('#start_date_registrasi').inputmask("datetime", {
                placeholder: "dd/mm/yyyy hh:mm",
            });
        }

        if (document.querySelector('#stop_date_registrasi')) {
            const stop_date_registrasi = flatpickr("#stop_date_registrasi", {
                enableTime: true,
                allowInput: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true
            });

            stop_date_registrasi.setDate(formatDate(pendaftaranStop), true);

            $('#stop_date_registrasi').inputmask("datetime", {
                placeholder: "dd/mm/yyyy hh:mm",
            });
        }

        if (document.querySelector('#start_date_penerimaan')) {
            const start_date_penerimaan = flatpickr("#start_date_penerimaan", {
                enableTime: true,
                allowInput: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true
            });

            start_date_penerimaan.setDate(formatDate(penerimaanStart), true);

            $('#start_date_penerimaan').inputmask("datetime", {
                placeholder: "dd/mm/yyyy hh:mm",
            });
        }

        if (document.querySelector('#stop_date_penerimaan')) {
            const stop_date_penerimaan = flatpickr("#stop_date_penerimaan", {
                enableTime: true,
                allowInput: true,
                locale: "id",
                dateFormat: "d/m/Y H:i",
                time_24hr: true
            });

            stop_date_penerimaan.setDate(formatDate(penerimaanStop), true);

            $('#stop_date_penerimaan').inputmask("datetime", {
                placeholder: "dd/mm/yyyy hh:mm",
            });
        }

        if (document.querySelector('#start_date_pengambilan')) {
            const start_date_pengambilan = flatpickr("#start_date_pengambilan", {
                enableTime: true,
                allowInput: true,
                dateFormat: "d/m/Y H:i",
                time_24hr: true
            });

            start_date_pengambilan.setDate(formatDate(pengambilanStart), true);

            $('#start_date_pengambilan').inputmask("datetime", {
                placeholder: "dd/mm/yyyy hh:mm",
            });
        }

        if (document.querySelector('#stop_date_pengambilan')) {
            const stop_date_pengambilan = flatpickr("#stop_date_pengambilan", {
                enableTime: true,
                allowInput: true,
                locale: "id",
                dateFormat: "d/m/Y H:i",
                time_24hr: true
            });

            stop_date_pengambilan.setDate(formatDate(pengambilanStop), true);

            $('#stop_date_pengambilan').inputmask("datetime", {
                placeholder: "dd/mm/yyyy hh:mm",
            });
        }

        if (document.querySelector('#start_date_pemeriksaan')) {
            // document.querySelector('#start_date_pemeriksaan').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const start_date_pemeriksaan = flatpickr("#start_date_pemeriksaan", {
                // Opsi lain jika diperlukan
                enableTime: false,
                allowInput: true,
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });

            start_date_pemeriksaan.setDate(formatDateOnly(pemeriksaanStart), true); //

            $('#start_date_pemeriksaan').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }

        if (document.querySelector('#stop_date_pemeriksaan')) {
            // document.querySelector('#stop_date_pemeriksaan').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const stop_date_pemeriksaan = flatpickr("#stop_date_pemeriksaan", {
                // Opsi lain jika diperlukan
                enableTime: false,
                allowInput: true,
                locale: "id", // Setting locale to Indonesian
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });

            stop_date_pemeriksaan.setDate(formatDateOnly(pemeriksaanStop), true); //

            $('#stop_date_pemeriksaan').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }
        // if (document.querySelector('#stop_date_pemeriksaan')) {
        //     document.querySelector('#stop_date_pemeriksaan').value = formatDate(pemeriksaanStop);
        // }

        if (document.querySelector('#start_date_input')) {
            // document.querySelector('#stop_date_pemeriksaan').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const start_date_input = flatpickr("#start_date_input", {
                // Opsi lain jika diperlukan
                enableTime: false,
                allowInput: true,
                locale: "id", // Setting locale to Indonesian
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });

            start_date_input.setDate(formatDateOnly(inputStart), true); //

            $('#start_date_input').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }

        // if (document.querySelector('#start_date_input')) {
        //     document.querySelector('#start_date_input').value = formatDate(inputStart);
        // }

        if (document.querySelector('#stop_date_input')) {
            // document.querySelector('#stop_date_input').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const stop_date_input = flatpickr("#stop_date_input", {
                // Opsi lain jika diperlukan
                enableTime: false,
                allowInput: true,
                locale: "id", // Setting locale to Indonesian
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });

            stop_date_input.setDate(formatDateOnly(inputStop), true); //

            $('#stop_date_input').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }
        // if (document.querySelector('#stop_date_input')) {
        //     document.querySelector('#stop_date_input').value = formatDate(inputStop);
        // }

        if (document.querySelector('#start_date_verifikasi')) {
            // document.querySelector('#stop_date_input').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const start_date_verifikasi = flatpickr("#start_date_verifikasi", {
                // Opsi lain jika diperlukan
                enableTime: false,
                allowInput: true,
                locale: "id", // Setting locale to Indonesian
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });

            start_date_verifikasi.setDate(formatDateOnly(verifikasiStart), true); //

            $('#start_date_verifikasi').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }

        // if (document.querySelector('#start_date_verifikasi')) {
        //     document.querySelector('#start_date_verifikasi').value = formatDate(verifikasiStart);
        // }

        if (document.querySelector('#stop_date_verifikasi')) {
            // document.querySelector('#stop_date_input').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const stop_date_verifikasi = flatpickr("#stop_date_verifikasi", {
                // Opsi lain jika diperlukan

                allowInput: true,
                locale: "id", // Setting locale to Indonesian
                enableTime: false,
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });

            stop_date_verifikasi.setDate(formatDateOnly(verifikasiStop), true); //

            $('#stop_date_verifikasi').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }
        // if (document.querySelector('#stop_date_verifikasi')) {
        //     document.querySelector('#stop_date_verifikasi').value = formatDate(verifikasiStop);
        // }

        if (document.querySelector('#start_date_validasi')) {
            // document.querySelector('#stop_date_input').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const start_date_validasi = flatpickr("#start_date_validasi", {
                // Opsi lain jika diperlukan

                allowInput: true,
                enableTime: false,
                locale: "id", // Setting locale to Indonesian
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });

            start_date_validasi.setDate(formatDateOnly(validasiStart), true); //

            $('#start_date_validasi').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }

        // if (document.querySelector('#start_date_validasi')) {
        //     document.querySelector('#start_date_validasi').value = formatDate(validasiStart);
        // }

        if (document.querySelector('#stop_date_validasi')) {
            // document.querySelector('#stop_date_input').value = formatDate(pemeriksaanStart);

            // flatpickr(".datetime", {
            //     enableTime: true,
            //     dateFormat: "d/m/Y H:i", // 24-hour format
            //     time_24hr: true
            // });
            const stop_date_validasi = flatpickr("#stop_date_validasi", {
                // Opsi lain jika diperlukan

                allowInput: true,
                enableTime: false,
                locale: "id", // Setting locale to Indonesian
                dateFormat: "d/m/Y", // 24-hour format
                time_24hr: true
            });



            stop_date_validasi.setDate(formatDateOnly(validasiStop), true); //

            $('#stop_date_validasi').inputmask("99/99/9999", {
                placeholder: "dd/mm/yyyy",

            });
        }


        // if (document.querySelector('#stop_date_validasi')) {
        //     document.querySelector('#stop_date_validasi').value = formatDate(validasiStop);
        // }



        // Helper function to format date as YYYY-MM-DDTHH:MM
        function formatDate(date) {
            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');
            let hours = String(date.getHours()).padStart(2, '0');
            let minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        function formatDateOnly(date) {
            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');
            return `${day}/${month}/${year}`;
        }

        // Helper function to adjust times to working hours (8:00 AM to 3:00 PM)
        function adjustToWorkHours(date) {
            const startHour = 8;
            const endHour = 15;

            if (date.getHours() < startHour) {
                date.setHours(startHour, 0, 0, 0); // Set time to 8:00 AM
            } else if (date.getHours() >= endHour) {
                // If time is after 3:00 PM, move to the next day at 8:00 AM
                date.setDate(date.getDate() + 1);
                date.setHours(startHour, 0, 0, 0);
            }
        }



    });


    // $(document).ready(function() {
    //     flatpickr(".datetime", {
    //         enableTime: true,
    //         dateFormat: "d/m/Y H:i", // 24-hour format
    //         time_24hr: true
    //     });
    // });
</script>
<script>
    $(document).ready(function() {

        if (document.getElementById('toggle-registrasi')) {
            document.getElementById('toggle-registrasi').addEventListener('click', function() {
                var registrasiRow = document.getElementById('registrasi');
                var registrasiUpdateRow = document.getElementById('registrasi-update');

                if (registrasiRow.style.display === 'none') {
                    registrasiRow.style.display = '';
                    registrasiUpdateRow.style.display = 'none';
                } else {
                    registrasiRow.style.display = 'none';
                    registrasiUpdateRow.style.display = '';
                }


                var registrasiUpdateRowStart = $('#registrasi-update [name="start_date"]').val();

                flatpickr('#registrasi-update [name="start_date"]', {
                    enableTime: true,
                    dateFormat: "d/m/Y H:i", // 24-hour format
                    time_24hr: true
                });
                const register_update_start = flatpickr('#registrasi-update [name="start_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: true,
                    dateFormat: "d/m/Y H:i", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(register_update_start, registrasiUpdateRowStart); //

                $('#registrasi-update [name="start_date"]').inputmask("datetime", {
                    placeholder: "dd/mm/yyyy hh:mm",

                });

                var registrasiUpdateRowStop = $('#registrasi-update [name="stop_date"]').val();

                flatpickr('#registrasi-update [name="stop_date"]', {
                    enableTime: true,
                    dateFormat: "d/m/Y H:i", // 24-hour format
                    time_24hr: true
                });
                const register_update_stop = flatpickr('#registrasi-update [name="stop_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: true,
                    dateFormat: "d/m/Y H:i", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(register_update_stop, registrasiUpdateRowStop); //

                $('#registrasi-update [name="stop_date"]').inputmask("datetime", {
                    placeholder: "dd/mm/yyyy hh:mm",

                });
            });
        }


        if (document.getElementById('toggle-pengambilan-sampel')) {
            document.getElementById('toggle-pengambilan-sampel').addEventListener('click', function() {
                var pengambilanSampelRow = document.getElementById('pengambilan-sampel');
                var pengambilanSampelUpdateRow = document.getElementById('pengambilan-sampel-update');

                if (pengambilanSampelRow.style.display === 'none') {
                    pengambilanSampelRow.style.display = '';
                    pengambilanSampelUpdateRow.style.display = 'none';
                } else {
                    pengambilanSampelRow.style.display = 'none';
                    pengambilanSampelUpdateRow.style.display = '';
                }

                var pengambilanSampelUpdateRowStart = $(
                    '#pengambilan-sampel-update [name="start_date"]').val();

                const pengambilan_update_start = flatpickr(
                    '#pengambilan-sampel-update [name="start_date"]', {
                        allowInput: true,
                        locale: "id",
                        enableTime: true,
                        dateFormat: "d/m/Y H:i",
                        time_24hr: true
                    });

                setFlatpickrFromStored(pengambilan_update_start, pengambilanSampelUpdateRowStart);

                $('#pengambilan-sampel-update [name="start_date"]').inputmask("datetime", {
                    placeholder: "dd/mm/yyyy hh:mm",
                });

                var pengambilanSampelUpdateRowStop = $('#pengambilan-sampel-update [name="stop_date"]')
                    .val();

                const pengambilan_update_stop = flatpickr(
                    '#pengambilan-sampel-update [name="stop_date"]', {
                        allowInput: true,
                        locale: "id",
                        enableTime: true,
                        dateFormat: "d/m/Y H:i",
                        time_24hr: true
                    });

                setFlatpickrFromStored(pengambilan_update_stop, pengambilanSampelUpdateRowStop);

                $('#pengambilan-sampel-update [name="stop_date"]').inputmask("datetime", {
                    placeholder: "dd/mm/yyyy hh:mm",
                });
            });
        }

        if (document.getElementById('toggle-penerimaan-sampel')) {
            document.getElementById('toggle-penerimaan-sampel').addEventListener('click', function() {
                var penerimaanSampelRow = document.getElementById('penerimaan-sampel');
                var penerimaanSampelUpdateRow = document.getElementById('penerimaan-sampel-update');

                if (penerimaanSampelRow.style.display === 'none') {
                    penerimaanSampelRow.style.display = '';
                    penerimaanSampelUpdateRow.style.display = 'none';
                } else {
                    penerimaanSampelRow.style.display = 'none';
                    penerimaanSampelUpdateRow.style.display = '';
                }

                var penerimaanSampelUpdateRowStart = $('#penerimaan-sampel-update [name="start_date"]')
                    .val();

                const penerimaan_update_start = flatpickr(
                    '#penerimaan-sampel-update [name="start_date"]', {
                        allowInput: true,
                        locale: "id",
                        enableTime: true,
                        dateFormat: "d/m/Y H:i",
                        time_24hr: true
                    });

                setFlatpickrFromStored(penerimaan_update_start, penerimaanSampelUpdateRowStart);

                $('#penerimaan-sampel-update [name="start_date"]').inputmask("datetime", {
                    placeholder: "dd/mm/yyyy hh:mm",
                });

                var penerimaanSampelUpdateRowStop = $('#penerimaan-sampel-update [name="stop_date"]')
                    .val();

                const penerimaan_update_stop = flatpickr(
                    '#penerimaan-sampel-update [name="stop_date"]', {
                        allowInput: true,
                        locale: "id",
                        enableTime: true,
                        dateFormat: "d/m/Y H:i",
                        time_24hr: true
                    });

                setFlatpickrFromStored(penerimaan_update_stop, penerimaanSampelUpdateRowStop);

                $('#penerimaan-sampel-update [name="stop_date"]').inputmask("datetime", {
                    placeholder: "dd/mm/yyyy hh:mm",
                });
            });
        }

        if (document.getElementById('toggle-analitik')) {

            document.getElementById('toggle-analitik').addEventListener('click', function() {
                var analitikRow = document.getElementById('analitik');
                var analitikUpdateRow = document.getElementById('analitik-update');

                if (analitikRow.style.display === 'none') {
                    analitikRow.style.display = '';
                    analitikUpdateRow.style.display = 'none';
                } else {
                    analitikRow.style.display = 'none';
                    analitikUpdateRow.style.display = '';
                }

                var analitikUpdateRowStart = $('#analitik-update [name="start_date"]').val();

                flatpickr('#analitik-update [name="start_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const analitik_update_start = flatpickr('#analitik-update [name="start_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(analitik_update_start, analitikUpdateRowStart); //

                $('#analitik-update [name="start_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });

                var analitikUpdateRowStop = $('#analitik-update [name="stop_date"]').val();

                flatpickr('#analitik-update [name="stop_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const analitik_update_stop = flatpickr('#analitik-update [name="stop_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(analitik_update_stop, analitikUpdateRowStop); //

                $('#analitik-update [name="stop_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });
            });
        }

        if (document.getElementById('toggle-baca-hasil')) {

            document.getElementById('toggle-baca-hasil').addEventListener('click', function() {
                var bacaHasilRow = document.getElementById('baca-hasil');
                var bacaHasilUpdateRow = document.getElementById('baca-hasil-update');

                if (bacaHasilRow.style.display === 'none') {
                    bacaHasilRow.style.display = '';
                    bacaHasilUpdateRow.style.display = 'none';
                } else {
                    bacaHasilRow.style.display = 'none';
                    bacaHasilUpdateRow.style.display = '';
                }
                var bacaHasilUpdateRowStart = $('#baca-hasil-update [name="start_date"]').val();

                flatpickr('#baca-hasil-update [name="start_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const bacaHasil_update_start = flatpickr('#baca-hasil-update [name="start_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(bacaHasil_update_start, bacaHasilUpdateRowStart); //

                $('#baca-hasil-update [name="start_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });

                var bacaHasilUpdateRowStop = $('#baca-hasil-update [name="stop_date"]').val();

                flatpickr('#baca-hasil-update [name="stop_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const bacaHasil_update_stop = flatpickr('#baca-hasil-update [name="stop_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(bacaHasil_update_stop, bacaHasilUpdateRowStop); //

                $('#baca-hasil-update [name="stop_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });
            });

        }


        if (document.getElementById('toggle-verifikasi')) {

            document.getElementById('toggle-verifikasi').addEventListener('click', function() {
                var verifikasiRow = document.getElementById('verifikasi');
                var verifikasiUpdateRow = document.getElementById('verifikasi-update');

                if (verifikasiRow.style.display === 'none') {
                    verifikasiRow.style.display = '';
                    verifikasiUpdateRow.style.display = 'none';
                } else {
                    verifikasiRow.style.display = 'none';
                    verifikasiUpdateRow.style.display = '';
                }

                var verifikasiUpdateRowStart = $('#verifikasi-update [name="start_date"]').val();

                flatpickr('#verifikasi-update [name="start_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const verifikasi_update_start = flatpickr('#verifikasi-update [name="start_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(verifikasi_update_start, verifikasiUpdateRowStart); //

                $('#verifikasi-update [name="start_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });

                var verifikasiUpdateRowStop = $('#verifikasi-update [name="stop_date"]').val();

                flatpickr('#verifikasi-update [name="stop_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const verifikasi_update_stop = flatpickr('#verifikasi-update [name="stop_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(verifikasi_update_stop, verifikasiUpdateRowStop); //

                $('#verifikasi-update [name="stop_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });
            });

        }

        if (document.getElementById('toggle-validasi')) {

            document.getElementById('toggle-validasi').addEventListener('click', function() {
                var validasiRow = document.getElementById('validasi');
                var validasiUpdateRow = document.getElementById('validasi-update');

                if (validasiRow.style.display === 'none') {
                    validasiRow.style.display = '';
                    validasiUpdateRow.style.display = 'none';
                } else {
                    validasiRow.style.display = 'none';
                    validasiUpdateRow.style.display = '';
                }

                var validasiUpdateRowStart = $('#validasi-update [name="start_date"]').val();

                flatpickr('#validasi-update [name="start_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const validasi_update_start = flatpickr('#validasi-update [name="start_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(validasi_update_start, validasiUpdateRowStart); //

                $('#validasi-update [name="start_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });

                var validasiUpdateRowStop = $('#validasi-update [name="stop_date"]').val();

                flatpickr('#validasi-update [name="stop_date"]', {
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });
                const validasi_update_stop = flatpickr('#validasi-update [name="stop_date"]', {
                    // Opsi lain jika diperlukan

                    allowInput: true,
                    locale: "id", // Setting locale to Indonesian
                    enableTime: false,
                    dateFormat: "d/m/Y", // 24-hour format
                    time_24hr: true
                });


                setFlatpickrFromStored(validasi_update_stop, validasiUpdateRowStop); //

                $('#validasi-update [name="stop_date"]').inputmask("99/99/9999", {
                    placeholder: "dd/mm/yyyy",

                });
            });
        }



        function parseStoredDate(val) {
            if (!val) return null;
            val = String(val).trim();
            // d/m/Y H:i or d-m-Y H:i
            var m = val.match(/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})(?:\s+(\d{2}):(\d{2})(?::\d{2})?)?$/);
            if (m) {
                return new Date(+m[3], +m[2] - 1, +m[1], +(m[4] || 0), +(m[5] || 0));
            }
            // Y-m-d H:i:s or ISO
            var normalized = val.indexOf('T') === -1 ? val.replace(' ', 'T') : val;
            var d = new Date(normalized);
            return isNaN(d.getTime()) ? null : d;
        }

        function setFlatpickrFromStored(fp, val) {
            if (!fp) return;
            var d = parseStoredDate(val);
            if (d) {
                fp.setDate(d, true);
            }
        }

        function formatDate(date) {
            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');
            let hours = String(date.getHours()).padStart(2, '0');
            let minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }


    });
</script>
