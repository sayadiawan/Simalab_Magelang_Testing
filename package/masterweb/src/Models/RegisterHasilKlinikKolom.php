<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class RegisterHasilKlinikKolom extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = 'ms_register_hasil_klinis_kolom';
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_register_hasil_klinis_kolom';

    protected $fillable = [
        'id_register_hasil_klinis_kolom',
        'kode',
        'label',
        'grup',
        'sort',
        'tampil',
        'match_keys',
    ];

    protected $casts = [
        'tampil' => 'boolean',
        'sort' => 'integer',
        'match_keys' => 'array',
    ];

    public const GRUP_LABELS = [
        'kimia_darah' => 'Kimia Darah',
        'darah_rutin' => 'Darah Rutin',
        'widal' => 'Widal',
        'hbsag' => 'HbSAg',
        'urin_rutin' => 'Urin Rutin',
        'other' => 'Lain-lain',
    ];

    public const GRUP_ORDER = [
        'kimia_darah',
        'darah_rutin',
        'widal',
        'hbsag',
        'urin_rutin',
        'other',
    ];

    public function parametersatuan()
    {
        return $this->belongsToMany(
            ParameterSatuanKlinik::class,
            'ms_register_hasil_klinis_kolom_satuan',
            'id_register_hasil_klinis_kolom',
            'id_parameter_satuan_klinik'
        );
    }

    public static function visibleColumns()
    {
        return static::query()
            ->where('tampil', 1)
            ->with(['parametersatuan:id_parameter_satuan_klinik,name_parameter_satuan_klinik'])
            ->orderBy('sort')
            ->orderBy('label')
            ->get();
    }

    public static function allSettingsColumns()
    {
        return static::query()
            ->with(['parametersatuan:id_parameter_satuan_klinik,name_parameter_satuan_klinik'])
            ->orderBy('sort')
            ->orderBy('label')
            ->get();
    }

    /**
     * @return array{groups: array<int, array{key: string, label: string, columns: array}>, total: int, satuan_map: array<string, array{grup: string, kode: string}>}
     */
    public static function buildVisibleLayout(): array
    {
        $columns = static::visibleColumns();
        $byGrup = [];
        foreach (self::GRUP_ORDER as $grup) {
            $byGrup[$grup] = [];
        }

        $satuanMap = [];

        foreach ($columns as $col) {
            $grup = $col->grup;
            if (!isset($byGrup[$grup])) {
                $byGrup[$grup] = [];
            }

            $satuanIds = [];
            foreach ($col->parametersatuan as $satuan) {
                $sid = $satuan->id_parameter_satuan_klinik;
                $satuanIds[] = $sid;
                // Satu satuan hanya ke 1 kolom (yang pertama menurut sort)
                if (!isset($satuanMap[$sid])) {
                    $satuanMap[$sid] = [
                        'grup' => $grup,
                        'kode' => $col->kode,
                    ];
                }
            }

            $byGrup[$grup][] = [
                'id' => $col->id_register_hasil_klinis_kolom,
                'kode' => $col->kode,
                'label' => $col->label ?: $col->kode,
                'grup' => $grup,
                'satuan_ids' => $satuanIds,
                'match_keys' => is_array($col->match_keys) ? $col->match_keys : [],
            ];
        }

        $groups = [];
        $total = 0;
        foreach (self::GRUP_ORDER as $grup) {
            $cols = $byGrup[$grup] ?? [];
            if (empty($cols)) {
                continue;
            }
            $groups[] = [
                'key' => $grup,
                'label' => self::GRUP_LABELS[$grup] ?? $grup,
                'columns' => $cols,
            ];
            $total += count($cols);
        }

        return [
            'groups' => $groups,
            'total' => $total,
            'satuan_map' => $satuanMap,
        ];
    }
}
