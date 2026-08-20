@php
    $footerKotaLabel = $footerKotaLabel ?? 'Kota Mungkid';

    // Tanggal footer: hanya dari pengesahan (stop verifikasi id 5 atau kolom pengesahan_hasil).
    // Jika belum ada pengesahan → tampilkan placeholder (pratinjau sebelum pengesahan).
    $tanggalCetak = null;

    $sampleIdForFooter = null;
    if (isset($sample) && !empty($sample->id_samples)) {
        $sampleIdForFooter = $sample->id_samples;
    } elseif (isset($data['sample']) && !empty($data['sample']->id_samples)) {
        $sampleIdForFooter = $data['sample']->id_samples;
    }

    if ($sampleIdForFooter) {
        $vasPengesahan = \Smt\Masterweb\Models\VerificationActivitySample::where('id_sample', $sampleIdForFooter)
            ->where('id_verification_activity', 5)
            ->first();
        if ($vasPengesahan && !empty($vasPengesahan->stop_date)) {
            $tanggalCetak = $vasPengesahan->stop_date;
        }
    }

    if (!$tanggalCetak && isset($pengesahan_hasil) && !empty($pengesahan_hasil->pengesahan_hasil_date)) {
        $tanggalCetak = $pengesahan_hasil->pengesahan_hasil_date;
    }

    if ($tanggalCetak) {
        try {
            $tanggalTeks = \Carbon\Carbon::parse($tanggalCetak)->isoFormat('D MMMM Y');
        } catch (\Throwable $e) {
            $tanggalTeks = '........';
        }
    } else {
        $tanggalTeks = '........';
    }
@endphp
<td width="33%"></td>
<td width="33%"></td>
<td width="33%" style="text-align: left;">{{ $footerKotaLabel }}, {{ $tanggalTeks }}</td>
