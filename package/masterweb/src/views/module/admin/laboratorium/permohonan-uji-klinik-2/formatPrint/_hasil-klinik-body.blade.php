{{-- View partial untuk konten body hasil klinik (tanpa html/head/body tag) --}}
{{-- Di-extract dari hasil-klinik.blade.php baris 338-1028 --}}
@php
    // Hitung value_items untuk menentukan size_table
    $value_items = 0;
    foreach ($arr_permohonan_parameter as $item) {
        foreach ($item['item_permohonan_parameter_satuan'] as $key => $parameter) {
            $value_items++;
        }
    }

    if ($value_items < 3) {
        $size_table = 9;
        $padding_table = 5;
    } elseif ($value_items >= 4 && $value_items <= 16) {
        $size_table = 9;
    } else {
        $size_table = 8;
    }

    $bsreFooterReserve = 8;
@endphp

<div class="content-wrapper">
    <div class="header-section">
        {{-- Kop di setiap halaman dari .kop-repeat (position:fixed) --}}
        <H1 style="text-align: center; font-size: 14px; margin: 2px 0; line-height: 1.2;"><u>HASIL PEMERIKSAAN LABORATORIUM</u></H1>

        <table width="100%" cellspacing="0" cellpadding="0" border="1" class="patient-info-table"
            style="border-collapse: collapse; margin-top: 2px;">
            <tr>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    Dokter Pengirim
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    @if ($item_permohonan_uji_klinik->doctor_type == 'rujukan')
                            {{ $item_permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik ?? '-' }}
                    @elseif (isset($item_permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik))
                        {{ $item_permohonan_uji_klinik->nama_dokter_pengirim_permohonan_uji_klinik ?? '-' }}
                    @else
                        dr. Sunantyo, M.P.H.
                    @endif
                </td>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    No. Spesimen
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ $item_permohonan_uji_klinik->getSpesimenNumber() }}
                </td>
            </tr>
            <tr>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    No. Laboratorium
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ $item_permohonan_uji_klinik->getLabNumber() }}
                </td>
                <td width="20%"
                    style="vertical-align: middle; padding: 5px; border: 1px solid black; font-weight: bold;">
                    No. Rekam Medis
                </td>
                <td width="30%" style="padding: 5px 3px; border: 1px solid black;">
                    {{ $item_permohonan_uji_klinik->getNoRekamMedis() }}
                </td>
            </tr>
            <tr>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    Nama
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ mb_strtoupper($item_permohonan_uji_klinik->pasien->nama_pasien ?? '-', 'UTF-8') }}
                </td>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    Jenis Kelamin
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ $item_permohonan_uji_klinik->pasien->jeniskelamin_pasien == 'L' ? 'Laki-laki' : 'Perempuan' }}
                </td>
            </tr>
            <tr>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    Umur/Tanggal Lahir
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik ?? '-' }} Tahun
                    /{{ isset($item_permohonan_uji_klinik->pasien->tgllahir_pasien)
                        ? \Carbon\Carbon::createFromFormat('Y-m-d', $item_permohonan_uji_klinik->pasien->tgllahir_pasien)->isoFormat(
                            'D MMMM Y',
                        )
                        : '' }}
                </td>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    Tanggal Diambil
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ $tanggal_pengambilan_sample }}
                </td>
            </tr>
            <tr>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    Alamat
                </td>
                <td style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ \Smt\Masterweb\Helpers\Smt::alamatPasienCetak($item_permohonan_uji_klinik->pasien) }}
                </td>
                <td width="20%"
                    style="vertical-align: middle; padding: 2px; border: 1px solid black; font-weight: bold; font-size: 8pt;">
                    Tanggal Diperiksa
                </td>
                <td width="30%" style="padding: 2px; border: 1px solid black; font-size: 8pt;">
                    {{ $tanggal_pemeriksaan_sample }}
                </td>
            </tr>
        </table>
    </div>
    {{-- End header-section --}}

    <table cellspacing="0" cellpadding="0" border="1" class="table-with-signature"
        style="margin-top: 3px; margin-bottom: 0px; border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th style="border: 1px solid black; padding: 3px; text-align: left; background-color: #f0f0f0;">
                    PEMERIKSAAN</th>
                <th style="border: 1px solid black; padding: 3px; text-align: center; background-color: #f0f0f0;">
                    HASIL
                </th>
                <th style="border: 1px solid black; padding: 3px; text-align: center; background-color: #f0f0f0;">
                    SATUAN
                </th>
                <th style="border: 1px solid black; padding: 3px; text-align: center; background-color: #f0f0f0;">
                    NILAI
                    NORMAL</th>
            </tr>
        </thead>

        <tbody>
            @php
                $item_permohonan_parameter_satuan = 0; // Variabel untuk menyimpan total jumlah elemen
                $total_rows = 0; // Hitung total baris yang akan ditampilkan (termasuk header grup)
                $all_params_with_results = []; // Simpan semua parameter yang punya hasil untuk tracking

                // Hitung total baris yang akan ditampilkan
                foreach ($arr_permohonan_parameter as $group_key => $item) {
                    // Pastikan item_permohonan_parameter_satuan ada dan adalah array
                    if (
                        isset($item['item_permohonan_parameter_satuan']) &&
                        is_array($item['item_permohonan_parameter_satuan'])
                    ) {
                        // Hitung elemen di dalam setiap array item_permohonan_parameter_satuan
                        $item_permohonan_parameter_satuan += count($item['item_permohonan_parameter_satuan']);

                        // Tambah 1 untuk header grup
                        $total_rows++;

                        // Hitung baris untuk setiap parameter (termasuk hasil masih kosong)
                        foreach ($item['item_permohonan_parameter_satuan'] as $param_key => $param) {
                            $param_row_count = 0;

                            if (count($param['data_permohonan_uji_subsatuan_klinik'] ?? []) > 0) {
                                $param_row_count++;
                                foreach ($param['data_permohonan_uji_subsatuan_klinik'] as $sub) {
                                    $param_row_count++;
                                }
                            } else {
                                $param_row_count = 1;
                            }

                            $total_rows += $param_row_count;
                        }
                    }
                }

                // Semua parameter harus tercetak (mis. Lain-lain di Sedimen).
                // DomPDF memecah halaman otomatis; tanda tangan mengikuti di halaman berikutnya bila perlu.
                $max_rows_before_cut = 18;
                $should_cut_last_param = false;

                // Menampilkan total jumlah elemen
                // dd ($arr_permohonan_parameter);
                $count = 0;
                $current_row_count = 0;
            @endphp

            @foreach ($arr_permohonan_parameter as $key_parameter_jenis_klinik => $item_parameter_jenis_klinik)
                @php
                    $nama_param_jenis = str_replace(
                        '<br>',
                        '',
                        $item_parameter_jenis_klinik['name_parameter_jenis_klinik'],
                    );
                    $nama_param_jenis = html_entity_decode(
                        $item_parameter_jenis_klinik['name_parameter_jenis_klinik'],
                    );

                    // Tampilkan grup jika ada parameter (hasil boleh kosong / belum diisi analis)
                    $has_parameters = !empty($item_parameter_jenis_klinik['item_permohonan_parameter_satuan'])
                        && is_array($item_parameter_jenis_klinik['item_permohonan_parameter_satuan']);
                @endphp
                @if ($has_parameters)
                    @if ($item_permohonan_uji_klinik->is_prolanis_gula == 1 || $item_permohonan_uji_klinik->is_prolanis_gula == 1)
                        @php $current_row_count++; @endphp
                        <tr>
                            <th colspan="4"
                                style="text-align: center; padding: 2px; border: 1px solid black; background-color: #e8e8e8;">
                                <strong>{!! strtoupper($nama_param_jenis) !!}</strong>
                            </th>
                        </tr>
                    @else
                        @php $current_row_count++; @endphp
                        <tr>
                            <th colspan="4"
                                style="text-align: center; padding: 2px; border: 1px solid black;">
                                <strong>{!! strtoupper($nama_param_jenis) !!}</strong>
                            </th>
                        </tr>
                    @endif
                @endif

                @php
                    $count_parameter = 0;
                    // Urutkan item_permohonan_parameter_satuan berdasarkan sort_parameter_satuan_klinik (ascending)
                    $items_sorted = $item_parameter_jenis_klinik['item_permohonan_parameter_satuan'] ?? [];
                    usort($items_sorted, function ($a, $b) {
                        $resolveSort = function ($item) {
                            if (isset($item['sort_parameter_satuan_klinik']) && $item['sort_parameter_satuan_klinik'] !== null && $item['sort_parameter_satuan_klinik'] !== '') {
                                return (int) $item['sort_parameter_satuan_klinik'];
                            }

                            return PHP_INT_MAX;
                        };

                        $sortA = $resolveSort($a);
                        $sortB = $resolveSort($b);

                        if ($sortA !== $sortB) {
                            return $sortA <=> $sortB;
                        }

                        return strcmp(
                            (string) ($a['parameter_satuan_klinik'] ?? ''),
                            (string) ($b['parameter_satuan_klinik'] ?? '')
                        );
                    });
                @endphp
                @php
                    $last_param_key = !empty($items_sorted) ? array_key_last($items_sorted) : null;
                @endphp
                @foreach ($items_sorted as $key_satuan_klinik => $item_satuan_klinik)
                    @php
                        $is_last_param_in_group = $key_satuan_klinik == $last_param_key;
                        $should_skip =
                            $should_cut_last_param &&
                            $is_last_param_in_group &&
                            $current_row_count >= $max_rows_before_cut;
                    @endphp
                    @if (!$should_skip)
                        @if (count($item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] ?? []) > 0)
                            {{-- melakukan mapping data permohonan uji parameter satuan yang memiliki permohonan uji parameter subsatuan --}}
                            @php
                                // Urutkan subsatuan jika ada berdasarkan sort_parameter_sub_satuan_klinik
                                $subs_sorted = $item_satuan_klinik['data_permohonan_uji_subsatuan_klinik'] ?? [];
                                if (isset($subs_sorted['id_permohonan_uji_sub_parameter_klinik'])) {
                                    $subs_sorted = [$subs_sorted];
                                }
                                usort($subs_sorted, function ($a, $b) {
                                    $sa = isset($a['sort_parameter_sub_satuan_klinik'])
                                        ? (int) $a['sort_parameter_sub_satuan_klinik']
                                        : PHP_INT_MAX;
                                    $sb = isset($b['sort_parameter_sub_satuan_klinik'])
                                        ? (int) $b['sort_parameter_sub_satuan_klinik']
                                        : PHP_INT_MAX;
                                    return $sa <=> $sb;
                                });

                                $last_sub_key = !empty($subs_sorted) ? array_key_last($subs_sorted) : null;
                                $has_subs = !empty($subs_sorted);
                            @endphp

                            @if ($has_subs)
                                @php $current_row_count++; @endphp
                                {{-- Header parent: colspan agar tanpa garis vertikal antar kolom --}}
                                <tr>
                                    <td colspan="4"
                                        style="text-align: left; padding: 5px 3px; border: 1px solid black; font-weight: bold; background-color: #f5f5f5; vertical-align: middle;">
                                        {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                    </td>
                                </tr>

                                @foreach ($subs_sorted as $key_subsatuan_klinik => $item_subsatuan_klinik)
                                    @php
                                        // Cek apakah ini sub-parameter terakhir dan perlu dipotong
                                        $is_last_sub = $key_subsatuan_klinik == $last_sub_key;
                                        $should_skip_sub =
                                            $should_cut_last_param &&
                                            $is_last_param_in_group &&
                                            $is_last_sub &&
                                            $current_row_count >= $max_rows_before_cut;
                                    @endphp
                                    @if (!$should_skip_sub)
                                        @php $current_row_count++; @endphp
                                        <tr>
                                            {{-- nama test --}}
                                            <td style="text-align: left; padding: 5px 3px; border: 1px solid black; vertical-align: middle;">
                                                {{ $item_subsatuan_klinik['nama_parameter_sub_satuan_klinik_id'] }}
                                            </td>

                                            {{-- hasil (kosong jika belum diisi analis) --}}
                                            <td style="text-align: center; padding: 2px; border: 1px solid black;">
                                                {!! formatHasilSubMultipleBakuMutu(
                                                    $item_subsatuan_klinik['hasil_permohonan_uji_sub_parameter_klinik'] ?? null,
                                                    $item_subsatuan_klinik,
                                                    $item_satuan_klinik,
                                                    $item_permohonan_uji_klinik ?? null,
                                                ) !!}
                                            </td>

                                            {{-- satuan --}}
                                            <td style="text-align: center; padding: 2px; border: 1px solid black;">
                                                @if ($item_subsatuan_klinik['satuan_permohonan_uji_sub_parameter_klinik'] != null)
                                                    {{ $item_subsatuan_klinik['nama_satuan_permohonan_uji_sub_parameter_klinik'] }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            {{-- nilai rujukan --}}
                                            <td style="text-align: center; padding: 2px; border: 1px solid black;">
                                                {{ $item_subsatuan_klinik['nilai_baku_mutu_detail_parameter_klinik'] }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        @else
                                @php $current_row_count++; @endphp
                                <tr>
                                    {{-- nama test --}}
                                    <td style="text-align: left; padding: 2px 3px; border: 1px solid black;">
                                        {{ $item_satuan_klinik['nama_parameter_satuan_klinik'] }}
                                    </td>

                                    {{-- hasil (kosong jika belum diisi analis) --}}

                                    <td style="text-align: center; padding: 2px; border: 1px solid black;">

                                        {!! formatHasilMultipleBakuMutu(
                                            $item_satuan_klinik['hasil_permohonan_uji_parameter_klinik'] ?? null,
                                            $item_satuan_klinik,
                                            $item_permohonan_uji_klinik ?? null,
                                        ) !!}
                                    </td>

                                    {{-- satuan --}}
                                    <td style="text-align: center; padding: 2px; border: 1px solid black;">
                                        @if ($item_satuan_klinik['satuan_permohonan_uji_parameter_klinik'] != null)
                                            {!! $item_satuan_klinik['nama_satuan_permohonan_uji_parameter_klinik'] !!}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- nilai rujukan --}}
                                    <td class="keterangan-table"
                                        style="text-align: center; padding: 2px; border: 1px solid black; font-family: DejaVu Sans !important; font-size:7pt!important;">

                                        @php
                                            $value = str_replace(
                                                '<br>',
                                                '',
                                                $item_satuan_klinik['keterangan_permohonan_uji_parameter_klinik'],
                                            );

                                            $value = str_replace('<p></p>', '', $value);
                                            $value = str_replace('&#60;p&#62;&#60;/p&#62;', '', $value);
                                            $value = str_replace('&#60;p&#62;&#60;/p&#62;', '', $value);

                                            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                                        @endphp
                                        {!! $value !!}
                                    </td>
                                </tr>
                        @endif
                    @endif
                    @php
                        $count_parameter++;
                    @endphp
                @endforeach
                @php
                    $count++;
                @endphp
            @endforeach
        </tbody>
    </table>

    {{-- TTD selalu di bawah tabel, tidak perlu page break karena sudah dipotong jika perlu --}}
    <div class="signature-section" style="margin-top: 3px; page-break-inside: avoid; padding-bottom: {{ $bsreFooterReserve }}pt; margin-bottom: 4pt;">
        <p style="font-size: 8px; margin: 2px 0; line-height: 1.2;">Dokter Penanggungjawab: dr. Sunantyo, M.P.H. <span
                style="font-size: 7px; padding-left: 10px;">(dokter yang melakukan validasi)</span></p>
        <div style="width: 600px !important;">
            <table cellspacing="0" cellpadding="0" border="0"
                style="margin-top: 2px; border-collapse: collapse;">
                <tr>
                    <td style="width: 100px !important;">
                        <table width="100px" cellspacing="0" cellpadding="0" border="1"
                            style="margin-top: 2px; border-collapse: collapse;">
                            <tr>
                                <td width="40%" style="padding: 2px; border: 1px solid black; font-size: 7pt;">
                                    Diperiksa oleh
                                </td>
                                <td width="2%"
                                    style="padding: 2px; border: 1px solid black; text-align: center; font-size: 7pt;">
                                    :
                                </td>
                                <td width="58%" style="padding: 2px; border: 1px solid black; font-size: 7pt;">
                                    {{ $nama_petugas_pemeriksa }}
                                </td>
                            </tr>
                            <tr>
                                <td width="40%" style="padding: 2px; border: 1px solid black; font-size: 7pt;">
                                    Diverifikasi oleh
                                </td>
                                <td width="2%"
                                    style="padding: 2px; border: 1px solid black; text-align: center; font-size: 7pt;">
                                    :
                                </td>
                                <td width="58%" style="padding: 2px; border: 1px solid black; font-size: 7pt;">
                                    {{ $nama_petugas_verifikator }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-left: 5px;" >
                       
                    </td>
                </tr>
            </table>
        </div>

        <table class="no-break" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td width="10%" style="text-align: center">
                </td>
                <td width="25%">
                </td>
                <td width="28%" style="text-align: center; font-size: 10pt !important; padding-right: 10px;">
                    <table>
                        @php
                            $validasi = Smt\Masterweb\Models\VerificationActivitySample::where(
                                'is_klinik',
                                $item_permohonan_uji_klinik->id_permohonan_uji_klinik,
                            )
                                ->where('id_verification_activity', 5)
                                ->first();

                            if (isset($validasi)) {
                                $tanggal_validasi = $validasi->stop_date;
                                $nama_petugas_validasi = $validasi->nama_petugas;
                            } else {
                                $tanggal_validasi = null;
                                $nama_petugas_validasi = null;
                            }
                        @endphp
                        <tr>
                            <td width="10%"></td>
                            <td width="10%" style="text-align: center; font-size: 8pt; line-height: 1.2;">
                                Mungkid,
                                {{ isset($tanggal_validasi)
                                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $tanggal_validasi)->isoFormat('D MMMM Y')
                                    : '-' }}
                            </td>
                        </tr>
                        @if (isset($validasi) && isset($nama_petugas_validasi))
                            @if (isset($signOption) && $signOption == 0)
                                <tr>
                                    <td width="10%"></td>
                                    <td width="50%"
                                        style="text-align: center;  padding-right: 10px; font-size: 9pt !important;">
                                        Validator</td>
                                </tr>
                                <tr>
                                    <td width="10%"></td>
                                    <td width="50%"
                                        style="text-align: center;  padding-right: 10px; font-size: 9pt !important; height: 30px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="10%" style="text-align: center!important;font-size: 7px;">
                                        
                                    </td>
                                    <td width="50%"
                                        style="text-align: center; padding-right: 10px; vertical-align: middle; font-size: 9pt;">
                                        <u>dr. Sunantyo, M.P.H.</u>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%" style="text-align: center!important;font-size: 7px;"></td>
                                    <td width="50%" style="text-align: center!important; font-size: 8pt;">
                                        NIP.
                                        197001282000031001
                                    </td>
                                </tr>
                            @endif
                        @else
                            <tr>
                                <td width="10%"></td>
                                <td width="50%"
                                    style="text-align: center;  padding-right: 10px; font-size: 9pt !important;">
                                    Validator</td>
                            </tr>
                            <tr>
                                <td width="10%"></td>
                                <td width="50%"
                                    style="text-align: center;  padding-right: 10px; font-size: 9pt !important; height: 30px;">
                                </td>
                            </tr>
                            <tr>
                                <td width="10%" style="text-align: center!important;font-size: 7px;" >
                                   
                                </td>
                                <td width="50%"
                                    style="text-align: center; padding-right: 10px; vertical-align: middle; font-size: 9pt;">
                                    <u>dr. Sunantyo, M.P.H.</u>
                                </td>
                            </tr>
                            <tr>
                                <td width="30%" style="text-align: center!important;font-size: 7px;"></td>
                                <td width="50%" style="text-align: center!important; font-size: 8pt;">
                                    NIP.
                                    197001282000031001
                                </td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </div>
    {{-- End signature-section --}}
</div>
{{-- End content-wrapper --}}

@include('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint._bsre-footer-klinik', [
    'fs' => 9,
])
