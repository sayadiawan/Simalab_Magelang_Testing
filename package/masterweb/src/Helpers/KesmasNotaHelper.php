<?php

namespace Smt\Masterweb\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\Sample;

/**
 * Perhitungan harga nota Kesmas (non-klinik).
 * Dipakai cetak nota dan laporan agar nominal per sampel selaras.
 */
class KesmasNotaHelper
{
  /** @var array<string, array<string, float>> */
  private static $amountCache = [];

  /**
   * Total biaya per sample_id untuk satu permohonan (sebelum merge baris nota).
   *
   * @return array<string, float>
   */
  public static function amountsBySampleId($idPermohonanUji): array
  {
    $idPermohonanUji = (string) $idPermohonanUji;
    if ($idPermohonanUji === '') {
      return [];
    }

    if (isset(self::$amountCache[$idPermohonanUji])) {
      return self::$amountCache[$idPermohonanUji];
    }

    $built = self::build($idPermohonanUji);
    $amounts = [];
    foreach ($built['samples_data'] as $sampleId => $row) {
      $amounts[(string) $sampleId] = (float) ($row['total_biaya'] ?? 0);
    }

    self::$amountCache[$idPermohonanUji] = $amounts;

    return $amounts;
  }

  /**
   * Nominal nota untuk satu sampel.
   */
  public static function amountForSample($sampleId, $idPermohonanUji): float
  {
    $sampleId = (string) $sampleId;
    $amounts = self::amountsBySampleId($idPermohonanUji);

    return (float) ($amounts[$sampleId] ?? 0);
  }

  /**
   * Baris nota siap cetak (merge batch kimia+mikro).
   *
   * @return array<int, array>
   */
  public static function buildValueItems($idPermohonanUji): array
  {
    return self::build($idPermohonanUji)['value_items'];
  }

  public static function clearCache(): void
  {
    self::$amountCache = [];
  }

  /**
   * @return array{samples_data: array, value_items: array}
   */
  public static function build($idPermohonanUji): array
  {
    $value_items = [];
    $samplesData = [];

    $samples = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->whereNull('deleted_at')
      ->get();

    if ($samples->isEmpty()) {
      return ['samples_data' => $samplesData, 'value_items' => $value_items];
    }

    $additionalMethods = DB::table('tb_samples')
      ->whereNull('tb_samples.deleted_at')
      ->where('permohonan_uji_id', '=', $idPermohonanUji)
      ->join('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->leftJoin('ms_packet_detail', function ($join) {
        $join->on('ms_packet_detail.packet_id', '=', 'tb_samples.packet_id')
          ->on('ms_packet_detail.method_id', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->leftJoin('ms_method', 'ms_method.id_method', '=', 'tb_sample_method.method_id')
      ->whereNull('ms_packet_detail.id_packet_detail')
      ->select('tb_samples.*', 'tb_sample_method.*', 'ms_sample_type.name_sample_type', 'ms_method.params_method as method_name')
      ->get();

    foreach ($samples as $sample) {
      $lokasi_raw = $sample->titik_pengambilan ??
        ($sample->name_customer_pdam && $sample->address_location_pdam
          ? $sample->name_customer_pdam . ' ' . $sample->address_location_pdam
          : '-');
      $jenis_sampel_display = $sample->sampletype->name_sample_type ?? '-';

      $samplesData[$sample->id_samples] = [
        'id_samples' => $sample->id_samples,
        'jenis_sampel' => $jenis_sampel_display,
        'lokasi' => $lokasi_raw,
        'parameters' => [],
        'total_biaya' => 0,
        'sample_ids' => [$sample->id_samples],
        'codeKimiaMikro' => $sample->codeKimiaMikro ?? null,
        'group_id' => $sample->group_id ?? null,
        'sample_type_group' => $sample->sample_type_group ?? null,
        'packet_id' => $sample->packet_id ?? null,
      ];
    }

    foreach ($additionalMethods as $item) {
      $sampleId = $item->id_samples;

      if (isset($samplesData[$sampleId])) {
        if (!empty($item->method_name) && !in_array($item->method_name, $samplesData[$sampleId]['parameters'])) {
          $samplesData[$sampleId]['parameters'][] = $item->method_name;
        }

        $price = $item->price_method ?? 0;
        $samplesData[$sampleId]['total_biaya'] += $price;
      }
    }

    $additionalMethodIds = $additionalMethods->pluck('id_sample_method')->toArray();
    $excludeCondition = empty($additionalMethodIds)
      ? '1=1'
      : 'tb_sample_method.id_sample_method NOT IN (' . implode(',', array_map('intval', $additionalMethodIds)) . ')';

    $methodPackets = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->leftjoin('ms_packet', function ($join) {
        $join->on('ms_packet.id_packet', '=', 'tb_samples.packet_id')
          ->whereNull('ms_packet.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->leftJoin('tb_sample_method', function ($join) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at');
      })
      ->leftJoin('ms_packet_detail', function ($join) {
        $join->on('ms_packet_detail.packet_id', '=', 'ms_packet.id_packet')
          ->whereNull('ms_packet_detail.deleted_at');
      })
      ->leftJoin('ms_sample_type', 'ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
      ->whereNotNull('ms_packet.id_packet')
      ->select(
        'tb_samples.id_samples',
        'tb_samples.sample_type_group',
        'tb_samples.created_at',
        'tb_samples.titik_pengambilan',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_packet.id_packet',
        'ms_packet.name_packet',
        'ms_sample_type.name_sample_type',
        DB::raw('COUNT(DISTINCT ms_packet_detail.id_packet_detail) as count_packet_detail'),
        DB::raw('COUNT(DISTINCT CASE
                    WHEN ' . $excludeCondition . '
                    THEN tb_sample_method.id_sample_method
                    ELSE NULL
                END) as count_sample_method'),
        DB::raw('CASE
                    WHEN COUNT(DISTINCT ms_packet_detail.id_packet_detail) >= COUNT(DISTINCT CASE
                        WHEN ' . $excludeCondition . '
                        THEN tb_sample_method.id_sample_method
                        ELSE NULL
                    END)
                    THEN ms_packet.price_total_packet
                    ELSE (
                        SELECT SUM(sm.price_method)
                        FROM tb_sample_method sm
                        INNER JOIN tb_samples s ON sm.sample_id = s.id_samples
                        WHERE s.created_at = tb_samples.created_at
                        AND s.packet_id = ms_packet.id_packet
                        AND s.deleted_at IS NULL
                        AND sm.deleted_at IS NULL
                        AND (' . $excludeCondition . ')
                    )
                END as price_total_packet'),
        DB::raw('COUNT(DISTINCT CONCAT(tb_samples.created_at, "-", ms_packet.id_packet)) as count_method')
      )
      ->groupBy(
        'tb_samples.id_samples',
        'tb_samples.sample_type_group',
        'tb_samples.created_at',
        'tb_samples.titik_pengambilan',
        'tb_samples.name_customer_pdam',
        'tb_samples.address_location_pdam',
        'ms_packet.id_packet',
        'ms_packet.name_packet',
        'ms_packet.price_total_packet',
        'ms_sample_type.name_sample_type'
      )
      ->orderBy('ms_packet.id_packet', 'DESC')
      ->get();

    $packetNameAddedForKey = [];
    foreach ($methodPackets as $packet) {
      $sampleId = $packet->id_samples;
      if (!isset($samplesData[$sampleId])) {
        continue;
      }
      $batchKey = self::packetBatchKey(
        $packet->id_packet,
        $packet->created_at,
        $samplesData[$sampleId]
      );
      if (empty($packet->name_packet) || isset($packetNameAddedForKey[$batchKey])) {
        continue;
      }
      $packetNameAddedForKey[$batchKey] = true;
      if (!in_array($packet->name_packet, $samplesData[$sampleId]['parameters'], true)) {
        $samplesData[$sampleId]['parameters'][] = $packet->name_packet;
      }
    }

    $packetChargeByKey = [];
    $sampleIdToPacketBatchKey = [];
    foreach ($methodPackets as $packet) {
      $sampleId = (string) $packet->id_samples;
      if (!isset($samplesData[$sampleId])) {
        continue;
      }
      $key = self::packetBatchKey(
        $packet->id_packet,
        $packet->created_at,
        $samplesData[$sampleId]
      );
      if (!isset($packetChargeByKey[$key])) {
        $packetChargeByKey[$key] = [
          'price_total_packet' => (float) ($packet->price_total_packet ?? 0),
          'sample_ids' => [],
        ];
      } else {
        $packetChargeByKey[$key]['price_total_packet'] = max(
          $packetChargeByKey[$key]['price_total_packet'],
          (float) ($packet->price_total_packet ?? 0)
        );
      }
      $packetChargeByKey[$key]['sample_ids'][$sampleId] = true;
      $sampleIdToPacketBatchKey[$sampleId] = $key;
    }

    foreach ($packetChargeByKey as $group) {
      $sampleIds = array_keys($group['sample_ids']);
      sort($sampleIds, SORT_STRING);
      $firstSampleId = $sampleIds[0] ?? null;
      $packetPrice = (float) ($group['price_total_packet'] ?? 0);
      if ($firstSampleId !== null && isset($samplesData[$firstSampleId]) && $packetPrice > 0) {
        $samplesData[$firstSampleId]['total_biaya'] += $packetPrice;
      }
    }

    $samplesWithSampling = Sample::where('permohonan_uji_id', '=', $idPermohonanUji)
      ->where('is_sampling', '=', 1)
      ->where('cost_sampling_samples', '>', 0)
      ->whereNull('tb_samples.deleted_at')
      ->get();

    foreach ($samplesWithSampling as $sample) {
      if (isset($samplesData[$sample->id_samples])) {
        $samplesData[$sample->id_samples]['total_biaya'] += $sample->cost_sampling_samples;
      }
    }

    $batchGroups = [];
    $standaloneSampleIds = [];
    foreach (array_keys($samplesData) as $sid) {
      $sid = (string) $sid;
      if (isset($sampleIdToPacketBatchKey[$sid])) {
        $bk = $sampleIdToPacketBatchKey[$sid];
        $batchGroups[$bk][] = $sid;
      } else {
        $standaloneSampleIds[] = $sid;
      }
    }
    foreach ($batchGroups as $bk => $ids) {
      $batchGroups[$bk] = array_values(array_unique($ids));
      sort($batchGroups[$bk], SORT_STRING);
    }

    $mergeSampleRows = function (array $sampleIds) use ($samplesData) {
      $parameters = [];
      $jenisList = [];
      $lokasi = '-';
      $totalBiaya = 0;
      $allSampleIds = [];
      foreach ($sampleIds as $sid) {
        if (!isset($samplesData[$sid])) {
          continue;
        }
        $row = $samplesData[$sid];
        foreach ($row['parameters'] as $p) {
          if ($p !== '' && !in_array($p, $parameters, true)) {
            $parameters[] = $p;
          }
        }
        $j = $row['jenis_sampel'] ?? '-';
        if ($j !== '-' && !in_array($j, $jenisList, true)) {
          $jenisList[] = $j;
        }
        $lokasi = ($row['lokasi'] ?? '') !== '' ? ($row['lokasi'] ?? '-') : $lokasi;
        $totalBiaya += (float) ($row['total_biaya'] ?? 0);
        foreach ($row['sample_ids'] ?? [$sid] as $id) {
          $id = (string) $id;
          if (!in_array($id, $allSampleIds, true)) {
            $allSampleIds[] = $id;
          }
        }
      }
      sort($allSampleIds, SORT_STRING);
      $jenisStr = !empty($jenisList) ? implode(', ', $jenisList) : '-';

      $noLab = null;
      foreach ($allSampleIds as $sampleId) {
        $sampleModel = Sample::find($sampleId);
        if ($sampleModel) {
          $assigned = $sampleModel->getAssignedNomorLabOrNull();
          if ($assigned) {
            $noLab = $assigned;
            break;
          }
        }
      }

      return [
        'name_item' => !empty($parameters) ? implode(', ', $parameters) : '-',
        'count_item' => count($sampleIds),
        'price_item' => $totalBiaya,
        'total' => $totalBiaya,
        'jenis_sampel' => $jenisStr,
        'lokasi' => $lokasi,
        'sample_ids' => $allSampleIds,
        'no_lab' => $noLab,
      ];
    };

    $batchEntries = [];
    foreach ($batchGroups as $ids) {
      $batchEntries[] = ['min_id' => min($ids), 'ids' => $ids];
    }
    usort($batchEntries, function ($a, $b) {
      return $a['min_id'] <=> $b['min_id'];
    });
    foreach ($batchEntries as $entry) {
      $value_items[] = $mergeSampleRows($entry['ids']);
    }

    sort($standaloneSampleIds, SORT_STRING);
    foreach ($standaloneSampleIds as $sid) {
      $value_items[] = $mergeSampleRows([$sid]);
    }

    return ['samples_data' => $samplesData, 'value_items' => $value_items];
  }

  /**
   * Kunci pengelompokan biaya paket (satu paket = satu charge, kimia+mikro).
   */
  public static function packetBatchKey($packetId, $createdAt, array $sampleMeta = []): string
  {
    $packetId = (int) $packetId;
    if (!empty($sampleMeta['codeKimiaMikro'])) {
      return $packetId . "\x1e" . 'ckm:' . $sampleMeta['codeKimiaMikro'];
    }
    if (!empty($sampleMeta['group_id'])) {
      return $packetId . "\x1e" . 'grp:' . $sampleMeta['group_id'];
    }
    if (!empty($sampleMeta['sample_type_group'])) {
      return $packetId . "\x1e" . 'stg:' . $sampleMeta['sample_type_group'];
    }
    $createdKey = $createdAt
      ? Carbon::parse($createdAt)->format('Y-m-d H:i:s')
      : '';

    return $packetId . "\x1e" . 'ts:' . $createdKey;
  }
}
