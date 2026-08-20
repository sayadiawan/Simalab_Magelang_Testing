<?php

namespace Smt\Masterweb\Exports;

use Carbon\Carbon;
use Smt\Masterweb\Helpers\Smt;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;
use Smt\Masterweb\Models\PengambilanSampleKlinik;

/**
 * REGISTER PENDAFTARAN (klinik) — layout sama dengan sheet non-klinik;
 * sumber data dari {@see PermohonanUjiKlinik2}.
 */
class RegisterPendaftaranKlinikMonthSheet extends RegisterPendaftaranNonKlinikMonthSheet
{
  protected function loadDataRows(): array
  {
    $permohonans = PermohonanUjiKlinik2::query()
      ->with([
        'pasien',
        'permohonanujiparameterklinik.parametersatuanklinik',
        'permohonanujipaketklinik.parameterpaketklinik',
        'permohonanujipaketklinik.parameterpaketextra',
      ])
      ->whereYear('tglregister_permohonan_uji_klinik', $this->year)
      ->whereMonth('tglregister_permohonan_uji_klinik', $this->month);

    if ($this->filterDate !== null) {
      $permohonans = $permohonans->whereDate('tglregister_permohonan_uji_klinik', $this->filterDate);
    }

    $permohonans = $permohonans->whereNull('deleted_at')
      ->orderBy('tglregister_permohonan_uji_klinik')
      ->orderBy('noregister_permohonan_uji_klinik')
      ->orderBy('created_at')
      ->get();

    if ($permohonans->isEmpty()) {
      return [];
    }

    $ids = $permohonans->pluck('id_permohonan_uji_klinik')->all();

    $pengambilanBerhasilByPu = PengambilanSampleKlinik::query()
      ->whereIn('permohonan_uji_klinik_id', $ids)
      ->whereNull('deleted_at')
      ->whereRaw('LOWER(TRIM(status_sampling)) = ?', ['berhasil'])
      ->orderBy('created_at')
      ->orderBy('id_pengambilan_sample_klinik')
      ->get()
      ->groupBy('permohonan_uji_klinik_id')
      ->map(function ($grp) {
        return $grp->first();
      });

    $paymentsByPu = PermohonanUjiPaymentKlinik::query()
      ->whereIn('permohonan_uji_klinik_id', $ids)
      ->whereNull('deleted_at')
      ->orderBy('date_done_estimation_permohonan_uji_payment_klinik')
      ->orderBy('created_at')
      ->get()
      ->groupBy('permohonan_uji_klinik_id');

    $idsNeedParamJenis = [];
    foreach ($permohonans as $pu) {
      $pAmbil = $pengambilanBerhasilByPu->get($pu->id_permohonan_uji_klinik);
      $fromSampling = $pAmbil ? $this->formatJenisSampleField($pAmbil->getAttribute('jenis_sample')) : '';
      if ($fromSampling === '') {
        $idsNeedParamJenis[] = $pu->id_permohonan_uji_klinik;
      }
    }

    $bulkJenisPerPu = $idsNeedParamJenis !== []
      ? Smt::bulkJenisSampelFromParameter(array_values(array_unique($idsNeedParamJenis)))
      : [];

    $rows = [];
    foreach ($permohonans as $pu) {
      $dateSrc = $pu->tglregister_permohonan_uji_klinik
        ? (string) $pu->tglregister_permohonan_uji_klinik
        : ($pu->created_at ? Carbon::parse($pu->created_at)->toDateString() : '');

      $pasien = $pu->pasien;
      $namaPasien = $pasien && $pasien->nama_pasien ? $pasien->nama_pasien : '-';
      $alamat = $pasien && !empty($pasien->alamat_pasien) ? $pasien->alamat_pasien : '';

      $pAmbil = $pengambilanBerhasilByPu->get($pu->id_permohonan_uji_klinik);
      $jenisSampel = $this->resolveJenisSampelKlinik($pAmbil, (string) $pu->id_permohonan_uji_klinik, $bulkJenisPerPu);

      $paramLabel = $this->buildParameterLabelKlinik($pu);

      $totalHarga = (float) ($pu->total_harga_permohonan_uji_klinik ?? 0);
      $paymentColl = $paymentsByPu->get($pu->id_permohonan_uji_klinik, collect());
      $terbayar = (float) $paymentColl->sum('terbayar_permohonan_uji_payment_klinik');
      $ket = ($terbayar > 0 && $terbayar >= $totalHarga && $totalHarga > 0) ? 'lunas' : '';

      $nomorDaftar = $pu->getDisplayNoregister() ?? '-';

      $rows[] = [
        'tanggal_raw' => $dateSrc ? (string) $dateSrc : '',
        'tanggal' => $this->formatTanggal($dateSrc),
        'nomor_sampel' => $nomorDaftar ? (string) $nomorDaftar : '-',
        'asal' => $namaPasien,
        'alamat' => $alamat,
        'jenis' => $jenisSampel,
        'pengirim' => $pu->nama_dokter_pengirim_permohonan_uji_klinik ? (string) $pu->nama_dokter_pengirim_permohonan_uji_klinik : '-',
        'unit' => $this->unitLabel,
        'parameter' => $paramLabel,
        'tarif' => rupiah($totalHarga),
        'ket' => $ket,
      ];
    }

    return $rows;
  }

  /**
   * Kolom parameter: paket → nama paket saja (Urine Rutin, Darah Rutin, dll);
   * pemeriksaan satuan → nama parameter satuan.
   */
  protected function buildParameterLabelKlinik(PermohonanUjiKlinik2 $pu): string
  {
    $labels = [];

    foreach ($pu->permohonanujipaketklinik as $paket) {
      $name = $this->resolvePaketName($paket);
      if ($name !== '') {
        $labels[] = $name;
      }
    }

    foreach ($pu->permohonanujiparameterklinik as $p) {
      if (!empty($p->permohonan_uji_paket_klinik)) {
        continue;
      }

      $sat = $p->parametersatuanklinik;
      $name = ($sat && $sat->name_parameter_satuan_klinik)
        ? $sat->name_parameter_satuan_klinik
        : '';
      if ($name !== '') {
        $labels[] = $name;
      }
    }

    $labels = array_values(array_unique($labels));

    return count($labels) ? implode(', ', $labels) : '-';
  }

  protected function resolvePaketName(PermohonanUjiPaketKlinik $paket): string
  {
    if (!empty($paket->parameter_paket_extra)) {
      $extra = $paket->parameterpaketextra;

      return ($extra && !empty($extra->nama_parameter_paket_extra))
        ? (string) $extra->nama_parameter_paket_extra
        : '';
    }

    $master = $paket->parameterpaketklinik;

    return ($master && !empty($master->name_parameter_paket_klinik))
      ? (string) $master->name_parameter_paket_klinik
      : '';
  }

  /**
   * Jenis dari pengambilan sukses (batch), lalu fallback array hasil {@see Smt::bulkJenisSampelFromParameter}
   * (2 query per sheet), baru {@see Smt::getJenisSampelFromParameter} jika id tidak ikut batch (seharusnya jarang).
   */
  protected function resolveJenisSampelKlinik(
    ?PengambilanSampleKlinik $pengambilanBerhasil,
    string $idPermohonanUjiKlinik,
    array $bulkJenisPerPu = []
  ): string {
    if ($pengambilanBerhasil !== null) {
      $fromSampling = $this->formatJenisSampleField($pengambilanBerhasil->getAttribute('jenis_sample'));
      if ($fromSampling !== '') {
        return $fromSampling;
      }
    }

    if ($bulkJenisPerPu !== [] && array_key_exists($idPermohonanUjiKlinik, $bulkJenisPerPu)) {
      return $this->formatJenisParameterList($bulkJenisPerPu[$idPermohonanUjiKlinik]);
    }

    return $this->formatJenisParameterList(Smt::getJenisSampelFromParameter($idPermohonanUjiKlinik));
  }

  /** @param mixed $raw Nilai kolom JSON/array/string */
  protected function formatJenisSampleField($raw): string
  {
    if ($raw === null || $raw === '') {
      return '';
    }

    if (is_array($raw)) {
      return $this->implodeUniqueTrimmed($raw);
    }

    $decoded = json_decode((string) $raw, true);
    if (\JSON_ERROR_NONE === json_last_error() && is_array($decoded)) {
      return $this->implodeUniqueTrimmed($decoded);
    }

    $s = trim((string) $raw);

    return $s !== '' ? $s : '';
  }

  /** @param mixed $fromParameter */
  protected function formatJenisParameterList($fromParameter): string
  {
    if ($fromParameter === null || $fromParameter === [] || $fromParameter === '') {
      return '-';
    }

    if (is_array($fromParameter)) {
      $s = $this->implodeUniqueTrimmed($fromParameter);

      return $s !== '' ? $s : '-';
    }

    $s = trim((string) $fromParameter);

    return $s !== '' ? $s : '-';
  }

  /**
   * @param array<int|string,mixed> $parts
   */
  protected function implodeUniqueTrimmed(array $parts): string
  {
    $flat = [];
    foreach ($parts as $p) {
      if ($p === null || !is_scalar($p)) {
        continue;
      }
      $t = trim((string) $p);
      if ($t !== '') {
        $flat[] = $t;
      }
    }

    return $flat ? implode(', ', array_unique($flat)) : '';
  }
}
