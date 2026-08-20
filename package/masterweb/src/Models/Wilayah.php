<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = "ms_wilayah";
    public $incrementing = false;
    protected $primaryKey = 'id_wilayah';
    public $timestamps = false;

    protected $fillable = [
        'id_wilayah',
        'wilayah_kode',
        'wilayah',
        'tipe'
    ];

    /**
     * Get all provinces (tipe = PROV)
     */
    public static function getProvinsi()
    {
        return self::where('tipe', 'PROV')->orderBy('wilayah', 'ASC')->get();
    }

    /**
     * Get kabupaten/kota by province code
     * Example: If province code is 11, get all that start with 11 and type KAB
     */
    public static function getKabupatenByProvinsi($provinsiKode)
    {
        return self::where('tipe', 'KAB')
            ->where('wilayah_kode', 'LIKE', $provinsiKode . '%')
            ->orderBy('wilayah', 'ASC')
            ->get();
    }

    /**
     * Get kecamatan by kabupaten code
     * Example: If kabupaten code is 1101, get all that start with 1101 and type KEC
     */
    public static function getKecamatanByKabupaten($kabupatenKode)
    {
        return self::where('tipe', 'KEC')
            ->where('wilayah_kode', 'LIKE', $kabupatenKode . '%')
            ->orderBy('wilayah', 'ASC')
            ->get();
    }

    /**
     * Get desa/kelurahan by kecamatan code
     * Example: If kecamatan code is 1101010, get all that start with 1101010 and type DESA
     */
    public static function getDesaByKecamatan($kecamatanKode)
    {
        return self::where('tipe', 'DESA')
            ->where('wilayah_kode', 'LIKE', $kecamatanKode . '%')
            ->orderBy('wilayah', 'ASC')
            ->get();
    }

    /**
     * Get wilayah by ID
     */
    public static function getWilayahById($id)
    {
        return self::find($id);
    }

    /**
     * Get full address string from IDs
     */
    public static function getFullAddress($provinsiId, $kabupatenId, $kecamatanId, $desaId)
    {
        $address = [];
        
        if ($desaId) {
            $desa = self::find($desaId);
            if ($desa) $address[] = $desa->wilayah;
        }
        
        if ($kecamatanId) {
            $kecamatan = self::find($kecamatanId);
            if ($kecamatan) $address[] = $kecamatan->wilayah;
        }
        
        if ($kabupatenId) {
            $kabupaten = self::find($kabupatenId);
            if ($kabupaten) $address[] = $kabupaten->wilayah;
        }
        
        if ($provinsiId) {
            $provinsi = self::find($provinsiId);
            if ($provinsi) $address[] = $provinsi->wilayah;
        }
        
        return implode(', ', $address);
    }

    /**
     * Search wilayah by keyword (autocomplete)
     * Returns desa, kecamatan, or kabupaten that match the search
     */
    public static function searchWilayah($keyword, $limit = 10, array $types = ['DESA', 'KEC', 'KAB'])
    {
        if (empty($keyword) || strlen($keyword) < 2) {
            return [];
        }

        $types = array_values(array_filter($types));
        if (empty($types)) {
            $types = ['DESA', 'KEC', 'KAB'];
        }

        $results = self::whereIn('tipe', $types)
            ->where('wilayah', 'LIKE', '%' . $keyword . '%')
            ->orderByRaw("
                CASE 
                    WHEN tipe = 'DESA' THEN 1
                    WHEN tipe = 'KEC' THEN 2
                    WHEN tipe = 'KAB' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('wilayah', 'ASC')
            ->limit($limit)
            ->get();

        // Build full path for each result
        $formattedResults = [];
        foreach ($results as $item) {
            $fullPath = self::buildFullPath($item);
            $formattedResults[] = [
                'id' => $item->id_wilayah,
                'kode' => $item->wilayah_kode,
                'nama' => $item->wilayah,
                'tipe' => $item->tipe,
                'full_path' => $fullPath,
                'label' => $item->wilayah . ' (' . self::getTipeLabel($item->tipe) . ') - ' . $fullPath
            ];
        }

        return $formattedResults;
    }

    /**
     * Build full hierarchical path for a wilayah
     */
    private static function buildFullPath($wilayah)
    {
        $path = [];
        $kode = $wilayah->wilayah_kode;
        
        // Get parent hierarchy based on code structure
        if ($wilayah->tipe == 'DESA') {
            // Desa: 1101010001 -> get Kec (1101010), Kab (1101), Prov (11)
            $kecKode = substr($kode, 0, 7);
            $kabKode = substr($kode, 0, 4);
            $provKode = substr($kode, 0, 2);
            
            $kec = self::where('wilayah_kode', $kecKode)->where('tipe', 'KEC')->first();
            $kab = self::where('wilayah_kode', $kabKode)->where('tipe', 'KAB')->first();
            $prov = self::where('wilayah_kode', $provKode)->where('tipe', 'PROV')->first();
            
            if ($kec) $path[] = $kec->wilayah;
            if ($kab) $path[] = $kab->wilayah;
            if ($prov) $path[] = $prov->wilayah;
            
        } elseif ($wilayah->tipe == 'KEC') {
            // Kecamatan: 1101010 -> get Kab (1101), Prov (11)
            $kabKode = substr($kode, 0, 4);
            $provKode = substr($kode, 0, 2);
            
            $kab = self::where('wilayah_kode', $kabKode)->where('tipe', 'KAB')->first();
            $prov = self::where('wilayah_kode', $provKode)->where('tipe', 'PROV')->first();
            
            if ($kab) $path[] = $kab->wilayah;
            if ($prov) $path[] = $prov->wilayah;
            
        } elseif ($wilayah->tipe == 'KAB') {
            // Kabupaten: 1101 -> get Prov (11)
            $provKode = substr($kode, 0, 2);
            
            $prov = self::where('wilayah_kode', $provKode)->where('tipe', 'PROV')->first();
            
            if ($prov) $path[] = $prov->wilayah;
        }
        
        return implode(', ', $path);
    }

    /**
     * Resolve kabupaten/kota dari id wilayah (desa/kecamatan/kabupaten).
     */
    public static function resolveKabupatenKotaFromWilayahId($wilayahId): ?string
    {
        if (!$wilayahId) {
            return null;
        }

        $current = self::find($wilayahId);
        $guard = 0;

        while ($current && $guard < 6) {
            if ($current->tipe === 'KAB') {
                return $current->wilayah;
            }

            $kode = (string) $current->wilayah_kode;
            $parentKode = null;

            if ($current->tipe === 'DESA' && strlen($kode) >= 7) {
                $parentKode = substr($kode, 0, 7);
                $current = self::where('wilayah_kode', $parentKode)->where('tipe', 'KEC')->first();
            } elseif ($current->tipe === 'KEC' && strlen($kode) >= 4) {
                $parentKode = substr($kode, 0, 4);
                $current = self::where('wilayah_kode', $parentKode)->where('tipe', 'KAB')->first();
            } else {
                break;
            }

            $guard++;
        }

        return null;
    }

    /**
     * Label hierarki wilayah dari id (desa/kecamatan/kabupaten/provinsi), paling spesifik dulu.
     *
     * @param  bool  $includeProvinsi  false = stop di kabupaten/kota (untuk cetak)
     */
    public static function resolveHierarchyLabelsFromWilayahId($wilayahId, bool $includeProvinsi = true): array
    {
        if (!$wilayahId) {
            return [];
        }

        $current = self::find($wilayahId);
        if (!$current) {
            return [];
        }

        // Jika node sendiri adalah PROV dan tidak diminta, jangan tampilkan
        if ($current->tipe === 'PROV' && !$includeProvinsi) {
            return [];
        }

        $labels = [$current->wilayah];
        $node = $current;
        $guard = 0;

        while ($node && $guard < 6) {
            $kode = (string) $node->wilayah_kode;
            $parent = null;

            if ($node->tipe === 'DESA' && strlen($kode) >= 7) {
                $parent = self::where('wilayah_kode', substr($kode, 0, 7))->where('tipe', 'KEC')->first();
            } elseif ($node->tipe === 'KEC' && strlen($kode) >= 4) {
                $parent = self::where('wilayah_kode', substr($kode, 0, 4))->where('tipe', 'KAB')->first();
            } elseif ($node->tipe === 'KAB' && strlen($kode) >= 2) {
                if (!$includeProvinsi) {
                    break;
                }
                $parent = self::where('wilayah_kode', substr($kode, 0, 2))->where('tipe', 'PROV')->first();
            } else {
                break;
            }

            if (!$parent) {
                break;
            }

            $labels[] = $parent->wilayah;
            $node = $parent;
            $guard++;
        }

        return $labels;
    }

    /**
     * Get parent IDs from a wilayah code
     */
    public static function getParentIds($wilayahKode, $tipe)
    {
        $parents = [
            'provinsi_id' => null,
            'kabupaten_id' => null,
            'kecamatan_id' => null,
            'desa_id' => null
        ];

        if ($tipe == 'DESA') {
            $kecKode = substr($wilayahKode, 0, 7);
            $kabKode = substr($wilayahKode, 0, 4);
            $provKode = substr($wilayahKode, 0, 2);
            
            $prov = self::where('wilayah_kode', $provKode)->where('tipe', 'PROV')->first();
            $kab = self::where('wilayah_kode', $kabKode)->where('tipe', 'KAB')->first();
            $kec = self::where('wilayah_kode', $kecKode)->where('tipe', 'KEC')->first();
            
            $parents['provinsi_id'] = $prov ? $prov->id_wilayah : null;
            $parents['kabupaten_id'] = $kab ? $kab->id_wilayah : null;
            $parents['kecamatan_id'] = $kec ? $kec->id_wilayah : null;
            
        } elseif ($tipe == 'KEC') {
            $kabKode = substr($wilayahKode, 0, 4);
            $provKode = substr($wilayahKode, 0, 2);
            
            $prov = self::where('wilayah_kode', $provKode)->where('tipe', 'PROV')->first();
            $kab = self::where('wilayah_kode', $kabKode)->where('tipe', 'KAB')->first();
            
            $parents['provinsi_id'] = $prov ? $prov->id_wilayah : null;
            $parents['kabupaten_id'] = $kab ? $kab->id_wilayah : null;
            
        } elseif ($tipe == 'KAB') {
            $provKode = substr($wilayahKode, 0, 2);
            
            $prov = self::where('wilayah_kode', $provKode)->where('tipe', 'PROV')->first();
            
            $parents['provinsi_id'] = $prov ? $prov->id_wilayah : null;
        }
        
        return $parents;
    }

    /**
     * Get human readable tipe label
     */
    private static function getTipeLabel($tipe)
    {
        $labels = [
            'PROV' => 'Provinsi',
            'KAB' => 'Kabupaten/Kota',
            'KEC' => 'Kecamatan',
            'DESA' => 'Desa/Kelurahan'
        ];
        
        return $labels[$tipe] ?? $tipe;
    }
}