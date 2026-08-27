<?php

namespace Smt\Masterweb\Http\Controllers;

use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \Smt\Masterweb\Models\Sample;
use \Smt\Masterweb\Models\SampleMethod;
use \Smt\Masterweb\Models\PermohonanUji;
use \Smt\Masterweb\Models\PermohonanUjiKlinik;
use \Smt\Masterweb\Models\PermohonanUjiKlinik2;
use \Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use \Smt\Masterweb\Models\ParameterPaketKlinik;
use \Smt\Masterweb\Models\ParameterPaketExtra;
use \Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;
use \Smt\Masterweb\Models\Pasien;
use \Smt\Masterweb\Models\PengambilanSampleKlinik;
use Smt\Masterweb\Models\VerificationActivitySample;
use \Smt\Masterweb\Helpers\Smt;
use \Smt\Masterweb\Helpers\KlinikPaymentHelper;
use \Smt\Masterweb\Helpers\KesmasNotaHelper;
use \Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use \Smt\Masterweb\Models\ParameterSatuanKlinik;

class LaboratoriumNotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Cetak Nota Kesmas
     *
     * @param int $id_permohonan_uji
     * @return \Illuminate\Http\Response
     */
    public function cetakNotaKesmas($id_permohonan_uji, $documentType = 'nota')
    {
        $user = Auth()->user();

        $permohonan_uji = PermohonanUji::where("id_permohonan_uji", $id_permohonan_uji)
            ->join('ms_customer', function ($join) {
                $join->on('ms_customer.id_customer', '=', 'tb_permohonan_uji.customer_id')
                    ->whereNull('tb_permohonan_uji.deleted_at')
                    ->whereNull('ms_customer.deleted_at');
            })
            ->select('tb_permohonan_uji.*', 'ms_customer.*', 'tb_permohonan_uji.created_at as dibuat')
            ->first();

        if (!$permohonan_uji) {
            return abort(404, 'Permohonan Uji tidak ditemukan');
        }

        if ($documentType === 'nota' && (int) $permohonan_uji->status_pembayaran !== 1) {
            return abort(403, 'Nota hanya dapat dicetak setelah pembayaran lunas');
        }

        // Prepare data untuk nota
        $value_items = $this->prepareNotaDataKesmas($id_permohonan_uji);

        if (empty($value_items)) {
            return abort(404, 'Tidak ada data yang dapat dicetak');
        }

        // Hitung total
        $grand_total = array_sum(array_map(function ($item) {
            return (int) ($item['total'] ?? 0);
        }, $value_items));

        // Tanggal Pemeriksaan dan Nama Petugas Pendaftar
        $sample = Sample::query()->where('permohonan_uji_id', $id_permohonan_uji)->first();
        $tanggalPemeriksaan = null;
        $nama_pendaftar = '-';

        if ($sample) {
            try {
                $verificationActivity = VerificationActivitySample::query()
                    ->where('id_sample', '=', $sample->id_samples)
                    ->where('id_verification_activity', '=', 1)
                    ->first();

                if ($verificationActivity) {
                    $tanggalPemeriksaan = $verificationActivity->stop_date;
                    $nama_pendaftar = $verificationActivity->nama_petugas ?? '-';
                }
            } catch (\Exception $e) {
                // Tanggal pemeriksaan tidak ditemukan
            }
        }

        // Jika tidak ada dari sample pertama, coba cari dari semua sample
        if ($nama_pendaftar == '-') {
            $samples = Sample::where('permohonan_uji_id', $id_permohonan_uji)
                ->whereNull('deleted_at')
                ->get();

            foreach ($samples as $s) {
                $verificationActivity = VerificationActivitySample::query()
                    ->where('id_sample', '=', $s->id_samples)
                    ->where('id_verification_activity', '=', 1)
                    ->first();

                if ($verificationActivity && !empty($verificationActivity->nama_petugas)) {
                    $nama_pendaftar = $verificationActivity->nama_petugas;
                    if (!$tanggalPemeriksaan) {
                        $tanggalPemeriksaan = $verificationActivity->stop_date;
                    }
                    break;
                }
            }

            if ($nama_pendaftar == '-') {
                $nama_pendaftar = $permohonan_uji->petugas_penerima ?? '-';
            }
        }

        // Kumpulkan jenis sampel dan parameter
        $jenis_samples = [];
        $parameters = [];
        $allUnitPemeriksaan = [];

        // Get sample data untuk informasi tambahan
        $samples = Sample::where('permohonan_uji_id', $id_permohonan_uji)
            ->whereNull('deleted_at')
            ->with('sampletype')
            ->get();

        // Hitung jumlah sampel dari total record di tabel tb_samples
        $jumlah_sampel = $samples->count();

        // Get semua method untuk parameter (ambil dari params_method)
        $allMethods = DB::table('tb_samples')
            ->where('tb_samples.permohonan_uji_id', '=', $id_permohonan_uji)
            ->whereNull('tb_samples.deleted_at')
            ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                    ->whereNull('tb_sample_method.deleted_at');
            })
            ->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
            ->whereNull('ms_method.deleted_at')
            ->select('ms_method.params_method')
            ->distinct()
            ->get();

        // Kumpulkan parameter dari method (params_method)
        foreach ($allMethods as $method) {
            if (!empty($method->params_method) && !in_array($method->params_method, $parameters)) {
                $parameters[] = $method->params_method;
            }
        }

        // Kumpulkan jenis sampel dari value_items
        foreach ($value_items as $item) {
            if (isset($item['jenis_sampel']) && !in_array($item['jenis_sampel'], $jenis_samples)) {
                $jenis_samples[] = $item['jenis_sampel'];
            }
        }

        // Get unit pemeriksaan dari laboratorium
        $labCodes = [];
        foreach ($samples as $sample) {
            $sampleMethods = SampleMethod::where('tb_sample_method.sample_id', $sample->id_samples)
                ->whereNull('tb_sample_method.deleted_at')
                ->join('ms_laboratorium', 'ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                ->whereNull('ms_laboratorium.deleted_at')
                ->select('ms_laboratorium.kode_laboratorium', 'ms_laboratorium.nama_laboratorium')
                ->distinct()
                ->get();

            foreach ($sampleMethods as $sm) {
                if ($sm->kode_laboratorium == 'KMA' || $sm->kode_laboratorium == 'FKA' || $sm->kode_laboratorium == 'KIM') {
                    if (!in_array('KIMIA/FISIKA', $allUnitPemeriksaan)) {
                        $allUnitPemeriksaan[] = 'KIMIA/FISIKA';
                    }
                } elseif ($sm->kode_laboratorium == 'MBI') {
                    if (!in_array('MIKROBIOLOGI', $allUnitPemeriksaan)) {
                        $allUnitPemeriksaan[] = 'MIKROBIOLOGI';
                    }
                }
            }
        }

        // Get first sample untuk data tambahan
        $firstSample = $samples->first();

        // Nomor sampel (rentang) — mengikuti blangko permintaan
        $samplesFirst = Sample::where('permohonan_uji_id', $id_permohonan_uji)
            ->whereNull('deleted_at')
            ->orderBy('count_id', 'ASC')
            ->first();
        $samplesLast = Sample::where('permohonan_uji_id', $id_permohonan_uji)
            ->whereNull('deleted_at')
            ->orderBy('count_id', 'DESC')
            ->first();

        $no_sampel = '-';
        if ($samplesFirst && !empty($samplesFirst->codesample_samples)) {
            $segFirst = Sample::codesampleNomorUrutForPrint(
                $samplesFirst->codesample_samples,
                (bool) $samplesFirst->is_nomor_sampel_manual
            );
            $segLast = Sample::codesampleNomorUrutForPrint(
                $samplesLast->codesample_samples ?? '',
                (bool) ($samplesLast->is_nomor_sampel_manual ?? false)
            );
            if ($samplesLast && $segFirst !== $segLast) {
                $no_sampel = $segFirst . ' - ' . $segLast;
            } else {
                $no_sampel = $segLast !== '' ? $segLast : $segFirst;
            }
        }

        // Tanggal pengiriman (date_sending)
        $sampleSendingFirst = Sample::where('permohonan_uji_id', $id_permohonan_uji)
            ->whereNull('deleted_at')
            ->whereNotNull('date_sending')
            ->orderBy('date_sending', 'ASC')
            ->first();
        $sampleSendingLast = Sample::where('permohonan_uji_id', $id_permohonan_uji)
            ->whereNull('deleted_at')
            ->whereNotNull('date_sending')
            ->orderBy('date_sending', 'DESC')
            ->first();

        $tanggal_pengiriman = '-';
        if ($sampleSendingFirst && $sampleSendingFirst->date_sending) {
            $formatSendingDate = function ($date) {
                return Carbon::parse($date)->locale('id')->translatedFormat('d-F-Y');
            };
            $tanggalAwal = $formatSendingDate($sampleSendingFirst->date_sending);
            $tanggalAkhir = ($sampleSendingLast && $sampleSendingLast->date_sending)
                ? $formatSendingDate($sampleSendingLast->date_sending)
                : $tanggalAwal;
            $tanggal_pengiriman = ($tanggalAwal !== $tanggalAkhir)
                ? $tanggalAwal . ' - ' . $tanggalAkhir
                : $tanggalAwal;
        }

        // Format tanggal
        $tanggalDiambil = $firstSample && $firstSample->datesampling_samples
            ? Carbon::parse($firstSample->datesampling_samples)->locale('id')->translatedFormat('d-F-Y')
            : ($tanggalPemeriksaan ? Carbon::parse($tanggalPemeriksaan)->locale('id')->translatedFormat('d-F-Y') : '-');

        $tanggalDiterima = $tanggalDiambil;

        // Jam diambil dan diterima
        $jamDiambil = $tanggalPemeriksaan ? Carbon::parse($tanggalPemeriksaan)->format('H:i') : '';
        $jamDiterima = $jamDiambil;

        // Payment data

        $total_harga = $grand_total;
        $dibayar = $permohonan_uji->terbayar ?? $total_harga;
        $sisa = $total_harga - $dibayar;

        if ($permohonan_uji->status_pembayaran == 1) {
            $dibayar = $total_harga;
            $sisa = 0;
        }
        

        // Prepare data untuk view
        $data = [
            'no_rekaman' => 'F/labkesKabMgl/04/01/Rev00',
            'nama_customer' => $permohonan_uji->nota_diterima_dari ?? $permohonan_uji->name_customer,
            'alamat_customer' => $permohonan_uji->nota_address_from ?? $permohonan_uji->address_customer,
            'no_hp' => $permohonan_uji->cp_customer ?? '-',
            'no_sampel' => $no_sampel,
            'tanggal_pengiriman' => $tanggal_pengiriman,
            'unit_pemeriksaan' => !empty($allUnitPemeriksaan) ? implode(', ', $allUnitPemeriksaan) : 'KESMAS',
            'jenis_sampel' => !empty($jenis_samples) ? implode(', ', $jenis_samples) : '-',
            'parameter' => !empty($parameters) ? implode(', ', $parameters) : '-',
            'wadah_sampel' => '',
            'volume' => '',
            'jam_diambil' => $jamDiambil,
            'jam_diterima' => $jamDiterima,
            'keterangan' => '',
            'total_harga' => $total_harga,
            'dibayar' => $dibayar,
            'sisa' => $sisa,
            'tanggal_diambil' => $tanggalDiambil,
            'tanggal_diterima' => $tanggalDiterima,
            'pengambil_sampel' => $firstSample ? $firstSample->pengambil_sampel : '-',
            'pendaftar' => $nama_pendaftar,
            'value_items' => $value_items,
            'permohonan_uji' => $permohonan_uji,
            'lab_name' => 'KESMAS',
            'user' => $user,
            'jumlah_sampel' => $jumlah_sampel,
            'is_klinik' => false,
        ];

        return $this->streamDocumentPdf($data, $documentType, 'KESMAS', $id_permohonan_uji);
    }

    public function cetakInvoiceKesmas($id_permohonan_uji)
    {
        return $this->cetakNotaKesmas($id_permohonan_uji, 'invoice');
    }

    /**
     * Cetak Nota Klinik
     *
     * @param int $id_permohonan_uji_klinik
     * @return \Illuminate\Http\Response
     */
    public function cetakNotaKlinik($id_permohonan_uji_klinik)
    {
        $data = $this->buildNotaKlinikData($id_permohonan_uji_klinik);

        if (empty($data)) {
            return abort(404, 'Tidak ada data yang dapat dicetak');
        }

        if ((int) ($data['permohonan_uji']->status_pembayaran ?? 0) !== 1) {
            return abort(403, 'Nota hanya dapat dicetak setelah pembayaran lunas');
        }

        return $this->streamDocumentPdf($data, 'nota', 'KLINIK', $id_permohonan_uji_klinik);
    }

    public function cetakInvoiceKlinik($id_permohonan_uji_klinik)
    {
        $data = $this->buildNotaKlinikData($id_permohonan_uji_klinik);

        if (empty($data)) {
            return abort(404, 'Tidak ada data yang dapat dicetak');
        }

        return $this->streamDocumentPdf($data, 'invoice', 'KLINIK', $id_permohonan_uji_klinik);
    }

    /**
     * Susun data nota klinik untuk satu permohonan.
     *
     * @param int|string $id_permohonan_uji_klinik
     * @return array|null
     */
    public function buildNotaKlinikData($id_permohonan_uji_klinik)
    {
        $user = Auth()->user();

        // Coba ambil dari PermohonanUjiKlinik2 terlebih dahulu
        $permohonan_uji_klinik = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);

        // Jika tidak ada, coba dari PermohonanUjiKlinik
        if (!$permohonan_uji_klinik) {
            $permohonan_uji_klinik = PermohonanUjiKlinik::find($id_permohonan_uji_klinik);
        }

        if (!$permohonan_uji_klinik) {
            return null;
        }

        // Ambil payment jika ada
        $payment = PermohonanUjiPaymentKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->first();

        // Prepare data untuk nota
        $value_items = $this->prepareNotaDataKlinik($id_permohonan_uji_klinik);

        if (empty($value_items)) {
            return null;
        }

        // Hitung jumlah sampel dari parameter paket
        $jumlah_sampel = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->count();

        // Hitung total
        $grand_total = array_sum(array_map(function ($item) {
            return (int) ($item['total'] ?? $item['subtotal'] ?? 0);
        }, $value_items));

        // Tanggal Pemeriksaan dan Nama Petugas Pendaftar
        $tanggalPemeriksaan = null;
        $tanggalPendaftaran = null;
        $nama_pendaftar = '-';

        try {
            $verificationActivity = VerificationActivitySample::query()
                ->where('is_klinik', '=', $id_permohonan_uji_klinik)
                ->where('id_verification_activity', '=', 1)
                ->first();

            if ($verificationActivity) {
                $tanggalPemeriksaan = $verificationActivity->stop_date;
                $tanggalPendaftaran = $verificationActivity->stop_date;
                $nama_pendaftar = $verificationActivity->nama_petugas ?? '-';
            }
        } catch (\Exception $e) {
            // Tanggal pemeriksaan tidak ditemukan
        }

        // Kumpulkan jenis sampel dan parameter
        $jenis_samples = [];
        $parameters = [];

        foreach ($value_items as $item) {
            if (isset($item['jenis_sampel']) && !in_array($item['jenis_sampel'], $jenis_samples)) {
                $jenis_samples[] = $item['jenis_sampel'];
            }
            if (isset($item['name_item'])) {
                $parameters[] = $item['name_item'];
            }
        }

        // Get pasien data if available
        $pasien = null;
        if ($permohonan_uji_klinik->pasien_permohonan_uji_klinik) {
            $pasien = Pasien::find($permohonan_uji_klinik->pasien_permohonan_uji_klinik);
        }

        // Get pengambilan sample klinik
        $pengambilanSample = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->first();

        // Format tanggal
        $tanggalDiambil = $tanggalPemeriksaan
            ? Carbon::parse($tanggalPemeriksaan)->locale('id')->translatedFormat('d-F-Y')
            : ($pengambilanSample && $pengambilanSample->created_at
                ? Carbon::parse($pengambilanSample->created_at)->locale('id')->translatedFormat('d-F-Y')
                : '-');

        $tanggalDiterima = $tanggalDiambil;

        // Jam diambil dan diterima
        $jamDiambil = $pengambilanSample && $pengambilanSample->time_sampling
            ? Carbon::parse($pengambilanSample->time_sampling)->format('H:i')
            : ($tanggalPemeriksaan ? Carbon::parse($tanggalPemeriksaan)->format('H:i') : '');
        $jamDiterima = $jamDiambil;

        // Volume sampel
        $volume = '';
        if ($pengambilanSample && $pengambilanSample->volume_sample) {
            $volumeData = is_string($pengambilanSample->volume_sample)
                ? json_decode($pengambilanSample->volume_sample, true)
                : $pengambilanSample->volume_sample;
            if (is_array($volumeData)) {
                $volume = implode(', ', array_filter($volumeData));
            } else {
                $volume = $pengambilanSample->volume_sample;
            }
        }

        // Jenis sampel dari pengambilan sample
        $jenisSampelKlinik = '';
        if ($pengambilanSample && $pengambilanSample->jenis_sample) {
            $jenisData = is_string($pengambilanSample->jenis_sample)
                ? json_decode($pengambilanSample->jenis_sample, true)
                : $pengambilanSample->jenis_sample;
            if (is_array($jenisData)) {
                $jenisSampelKlinik = implode(', ', $jenisData);
            } else {
                $jenisSampelKlinik = $pengambilanSample->jenis_sample;
            }
        }
        if (empty($jenisSampelKlinik) && $permohonan_uji_klinik->jenis_sampel) {
            $jenisSampelKlinik = $permohonan_uji_klinik->jenis_sampel;
        }

        // Get biaya_pengambilan_sampel
        $biaya_pengambilan_sampel = (int) ($permohonan_uji_klinik->biaya_pengambilan_sampel ?? 0);

        // Payment data — sinkronkan terbayar dengan total (cap jika overpay setelah edit)
        $total_parameter = $grand_total;
        $total_harga = $total_parameter + $biaya_pengambilan_sampel;
        $paymentSync = KlinikPaymentHelper::syncWithTotal($id_permohonan_uji_klinik, (int) $total_harga);
        $dibayar = (float) $paymentSync['sudah_dibayar'];
        $sisa = (float) $paymentSync['sisa_tagihan'];
        if ($payment) {
            $payment->refresh();
        }

        // Convert permohonan_uji_klinik ke format yang kompatibel dengan view
        $permohonan_uji = (object) [
            'id_permohonan_uji' => $permohonan_uji_klinik->id_permohonan_uji_klinik ?? $id_permohonan_uji_klinik,
            'no_lab' => $permohonan_uji_klinik->no_lab_manual ?? null,
            'nomor_nota' => $permohonan_uji_klinik->nomor_nota ?? '-',
            'nota_diterima_dari' => $permohonan_uji_klinik->nota_diterima_dari ?? ($pasien ? $pasien->nama_pasien : null),
            'status_pembayaran' => $sisa <= 0 && $dibayar > 0 ? '1' : ($permohonan_uji_klinik->status_pembayaran ?? '0'),
            'tanggal_bayar' => $payment ? ($payment->date_done_estimation_permohonan_uji_payment_klinik ?? $payment->created_at) : null,
            'petugas_penerima' => $permohonan_uji_klinik->petugas_penerima ?? '-',
            'signature_nota_pasien' => $permohonan_uji_klinik->signature_nota_pasien ?? null,
            'signature_nota_petugas' => $permohonan_uji_klinik->signature_nota_petugas ?? null,
            'customer' => (object) [
                'name_customer' => $pasien ? $pasien->nama_pasien : ($permohonan_uji_klinik->nota_diterima_dari ?? '-'),
                'address_customer' => $pasien ? Smt::alamatPasienCetak($pasien) : ($permohonan_uji_klinik->address_customer ?? '-'),
                'phone_customer' => $pasien ? $pasien->phone_pasien : ($permohonan_uji_klinik->phone_customer ?? '-'),
            ],
        ];

        // Prepare data untuk view
        $data = [
            'no_rekaman' => 'F/labkesKabMgl/04/01/Rev00',
            'nama_customer' => $pasien ? $pasien->nama_pasien : ($permohonan_uji_klinik->nota_diterima_dari ?? '-'),
            'alamat_customer' => $pasien ? Smt::alamatPasienCetak($pasien) : ($permohonan_uji_klinik->address_customer ?? '-'),
            'no_hp' => $pasien ? $pasien->phone_pasien : ($permohonan_uji_klinik->phone_customer ?? '-'),
            'unit_pemeriksaan' => 'KLINIK',
            'jenis_sampel' => !empty($jenisSampelKlinik) ? $jenisSampelKlinik : (!empty($jenis_samples) ? implode(', ', $jenis_samples) : ''),
            'parameter' => !empty($parameters) ? implode(', ', $parameters) : '-',
            'wadah_sampel' => '',
            'volume' => $volume,
            'jam_diambil' => $jamDiambil,
            'jam_diterima' => $jamDiterima,
            'keterangan' => '',
            'total_parameter' => $total_parameter,
            'biaya_pengambilan_sampel' => $biaya_pengambilan_sampel,
            'total_harga' => $total_harga,
            'dibayar' => $dibayar,
            'sisa' => $sisa,
            'tanggal_diambil' => $tanggalDiambil,
            'tanggal_diterima' => $tanggalDiterima,
            'pengambil_sampel' => $pengambilanSample ? ($pengambilanSample->petugas_name ?? '-') : '-',
            'pendaftar' => $nama_pendaftar,
            'value_items' => $value_items,
            'permohonan_uji' => $permohonan_uji,
            'lab_name' => 'KLINIK',
            'user' => $user,
            'jumlah_sampel' => $jumlah_sampel,
            'no_lab' => $permohonan_uji_klinik->getDisplayNoregister(),
            'no_sampel' => $permohonan_uji_klinik->noregister_permohonan_uji_klinik,
            'tanggal_pendaftaran' => $tanggalPendaftaran,
            'is_klinik' => true,
        ];

        return $data;
    }

    /**
     * Prepare data nota untuk Kesmas (logika harga di KesmasNotaHelper).
     *
     * @param int|string $idPermohonanUji
     * @return array
     */
    private function prepareNotaDataKesmas($idPermohonanUji)
    {
        return KesmasNotaHelper::buildValueItems($idPermohonanUji);
    }

    /**
     * Prepare data nota untuk Klinik
     *
     * @param int $idPermohonanUjiKlinik
     * @return array
     */
    private function prepareNotaDataKlinik($idPermohonanUjiKlinik)
    {
        $value_items = [];

        // Resolve jenis_sampel: sama dengan logic di data-formulir
        $jenisSampelResolved = '-';
        $pengambilanSample = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $idPermohonanUjiKlinik)
            ->where('status_sampling', 'Berhasil')
            ->first();

        if (isset($pengambilanSample->jenis_sample)) {
            $jenisData = json_decode($pengambilanSample->jenis_sample, true);
            if (is_array($jenisData) && !empty($jenisData)) {
                $jenisSampelResolved = implode(', ', array_unique(array_filter(array_map('trim', $jenisData))));
            } else {
                $jenisSampelResolved = $pengambilanSample->jenis_sample ?: '-';
            }
        } else {
            $fromParameter = Smt::getJenisSampelFromParameter($idPermohonanUjiKlinik);
            if (!empty($fromParameter)) {
                $jenisSampelResolved = is_array($fromParameter)
                    ? implode(', ', array_unique(array_filter(array_map('trim', $fromParameter))))
                    : $fromParameter;
            }
        }

        // Get parameter paket untuk permohonan ini
        $parameterPaket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $idPermohonanUjiKlinik)
            ->whereNull('deleted_at')
            ->get();

        foreach ($parameterPaket as $val) {
            // Resolve jenis_sampel per paket dari ParameterSatuanKlinik, fallback ke global
            $satuanIds = PermohonanUjiParameterKlinik::where('permohonan_uji_paket_klinik', $val->id_permohonan_uji_paket_klinik)
                ->whereNull('deleted_at')
                ->pluck('parameter_satuan_klinik')
                ->filter()
                ->toArray();

            $jenisSampelPerPaket = $jenisSampelResolved;
            if (!empty($satuanIds)) {
                $jenisArr = ParameterSatuanKlinik::whereIn('id_parameter_satuan_klinik', $satuanIds)
                    ->pluck('jenis_sampel')
                    ->toArray();

                $flatJenis = [];
                foreach ($jenisArr as $j) {
                    $parsed = is_array($j) ? $j : (json_decode($j, true) ?? (array) $j);
                    foreach ($parsed as $item) {
                        $item = trim((string) $item);
                        if ($item !== '' && !in_array($item, $flatJenis)) {
                            $flatJenis[] = $item;
                        }
                    }
                }

                if (!empty($flatJenis)) {
                    $jenisSampelPerPaket = implode(', ', $flatJenis);
                }
            }

            if (!empty($val->parameter_paket_extra)) {
                // Extra Paket - ambil harga dari master data
                $extra_paket = ParameterPaketExtra::where('id_parameter_paket_extra', $val->parameter_paket_extra)->first();
                if ($extra_paket) {
                    // Gunakan harga dari master data, fallback ke harga tersimpan jika master data tidak ada
                    $harga = $extra_paket->harga_parameter_paket_extra ?? $val->harga_permohonan_uji_paket_klinik;
                    $value_items[] = [
                        'name_item' => $extra_paket->nama_parameter_paket_extra,
                        'price_item' => $harga,
                        'count_item' => 1,
                        'total' => $harga,
                        'subtotal' => $harga,
                        'jenis_sampel' => $jenisSampelPerPaket,
                        'lokasi' => '-',
                    ];
                }
            } else {
                // Paket Normal - ambil harga dari master data
                $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $val->parameter_paket_klinik)->first();
                if ($paket) {
                    // Gunakan harga dari master data, fallback ke harga tersimpan jika master data tidak ada
                    $harga = $paket->harga_parameter_paket_klinik ?? $val->harga_permohonan_uji_paket_klinik;
                    $value_items[] = [
                        'name_item' => $paket->name_parameter_paket_klinik,
                        'price_item' => $harga,
                        'count_item' => 1,
                        'total' => $harga,
                        'subtotal' => $harga,
                        'jenis_sampel' => $jenisSampelPerPaket,
                        'lokasi' => '-',
                    ];
                }
            }
        }

        return $value_items;
    }

    private function applyDocumentType(array $data, $documentType = 'nota'): array
    {
        $type = $documentType === 'invoice' ? 'invoice' : 'nota';
        $data['document_type'] = $type;
        $data['document_title'] = $type === 'invoice'
            ? 'INVOICE / TAGIHAN PEMERIKSAAN'
            : 'NOTA PEMBAYARAN';

        return $data;
    }

    private function streamDocumentPdf(array $data, $documentType, $labName, $id)
    {
        $data = $this->applyDocumentType($data, $documentType);
        $prefix = ($data['document_type'] ?? 'nota') === 'invoice' ? 'Invoice' : 'Nota';

        $pdf = PDF::loadView('masterweb::module.admin.laboratorium.persuratan.nota.nota', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream($prefix . '_' . $labName . '_' . $id . '.pdf');
    }
}

