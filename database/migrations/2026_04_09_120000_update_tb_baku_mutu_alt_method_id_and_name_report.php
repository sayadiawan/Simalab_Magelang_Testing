<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satukan method ALT (mikro) ke satu method_id. Kolom name_report tidak diubah.
 *
 * Catatan: Rollback tidak mengembalikan method_id lama.
 *
 * Jika tabel migrations pernah berisi nama 2026_04_09_120001_update_tb_baku_mutu_alt_method_id_and_name_report,
 * ganti ke nama file ini agar konsisten.
 */
class UpdateTbBakuMutuAltMethodIdAndNameReport extends Migration
{
    /** @var string */
    private const METHOD_ID_ALT = 'acec491a-be47-454c-97b2-67c873444069';

    /**
     * @return string[]
     */
    private function canonicalNameReportParts(): array
    {
        return [
            'ALT',
            'ALT (30°, 72 jam)',
            'ALT aerob (30°, 72 jam)',
            'ALT aerob termofilik (30°, 72 jam)',
            'ALT akhir (30°, 72 jam)',
            'ALT anaerob (30°, 72 jam)',
            'ALT anaerob termofilik (30°, 72 jam)',
            'ALT awal (30°, 72 jam)',
        ];
    }

    /**
     * Varian ejaan / simbol yang pernah dipakai di DB.
     *
     * @return string[]
     */
    private function matchVariantsForPart(string $partWithDegree): array
    {
        $variants = [
            $partWithDegree,
            str_replace('°', '◦', $partWithDegree),
            str_replace('°', '?', $partWithDegree),
            // Ejaan alternatif
            str_replace('termopilik', 'termofilik', $partWithDegree),
            str_replace('termofilik', 'termopilik', $partWithDegree),
            str_replace(['°', 'termopilik'], ['◦', 'termofilik'], $partWithDegree),
        ];

        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * @return string[]
     */
    private function allMatchValues(): array
    {
        $all = [];
        foreach ($this->canonicalNameReportParts() as $part) {
            foreach ($this->matchVariantsForPart($part) as $v) {
                $all[] = $v;
            }
        }

        return array_values(array_unique($all));
    }

    /**
     * Samakan dengan isian TinyMCE / HTML: entity, tag, nbsp.
     */
    private function normalizeNameReportForMatch($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = str_replace(["\xc2\xa0", '&nbsp;', '&#160;', "\xC2\xA0"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value));

        return $value;
    }

    /**
     * Set key normalized => true untuk pencocokan cepat.
     *
     * @return array<string, bool>
     */
    private function normalizedNeedleSet(): array
    {
        $set = [];
        foreach ($this->allMatchValues() as $m) {
            $n = $this->normalizeNameReportForMatch($m);
            if ($n !== '') {
                $set[$n] = true;
            }
        }

        return $set;
    }

    /**
     * Nama parameter mikro ALT: diawali "ALT" sebagai kata (bukan substring SALT / ALTERNATIF).
     * Mencakup hanya "ALT", "ALT (30°, …)", "ALT aerob …", dll.
     */
    private function isAltMicroNameReport(string $normalized): bool
    {
        if ($normalized === '') {
            return false;
        }

        return (bool) preg_match('/^ALT($|[\s\(,])/iu', $normalized);
    }

    /**
     * Wajib sebelum UPDATE: binding utf8mb4 (simbol °) tidak boleh dibandingkan/disimpan ke kolom latin1 (error 3988).
     */
    private function ensureUtf8mb4NameReportColumn(): void
    {
        if (!Schema::hasColumn('tb_baku_mutu', 'name_report')) {
            return;
        }

        $row = DB::selectOne(
            'SELECT COLUMN_TYPE AS column_type, IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default, COLLATION_NAME AS collation_name
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['tb_baku_mutu', 'name_report']
        );

        if (!$row) {
            return;
        }

        $collation = $row->collation_name ?? '';
        if ($collation !== '' && strpos($collation, 'utf8mb4') === 0) {
            return;
        }

        $type = $row->column_type;
        $null = ($row->is_nullable ?? '') === 'YES' ? 'NULL' : 'NOT NULL';

        $defaultClause = '';
        if (($row->column_default ?? null) !== null && ($row->column_default ?? '') !== '') {
            $defaultClause = ' DEFAULT ' . DB::connection()->getPdo()->quote($row->column_default);
        }

        DB::statement(
            'ALTER TABLE `tb_baku_mutu` MODIFY `name_report` ' . $type .
            ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ' . $null . $defaultClause
        );
    }

    /**
     * Samakan charset/collation method_id dengan ms_method.id_method agar JOIN tidak error 1267
     * (campuran utf8mb4_general_ci vs utf8mb4_unicode_ci).
     */
    private function referenceMsMethodIdColumn(): ?object
    {
        if (!Schema::hasTable('ms_method')) {
            return null;
        }

        return DB::selectOne(
            'SELECT CHARACTER_SET_NAME AS character_set, COLLATION_NAME AS collation
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['ms_method', 'id_method']
        );
    }

    /**
     * Hindari error 3988 (latin1) dan 1267 (collation mismatch pada JOIN ke ms_method).
     */
    private function ensureMethodIdColumnMatchesMsMethod(): void
    {
        if (!Schema::hasColumn('tb_baku_mutu', 'method_id')) {
            return;
        }

        $ref = $this->referenceMsMethodIdColumn();
        $targetCharset = ($ref && !empty($ref->character_set)) ? $ref->character_set : 'utf8mb4';
        $targetCollation = ($ref && !empty($ref->collation)) ? $ref->collation : 'utf8mb4_unicode_ci';

        $row = DB::selectOne(
            'SELECT COLUMN_TYPE AS column_type, IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default,
                    COLLATION_NAME AS collation_name, CHARACTER_SET_NAME AS character_set_name
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['tb_baku_mutu', 'method_id']
        );

        if (!$row) {
            return;
        }

        $curCharset = $row->character_set_name ?? '';
        $curCollation = $row->collation_name ?? '';
        if ($curCharset === $targetCharset && $curCollation === $targetCollation) {
            return;
        }

        $type = $row->column_type;
        $null = ($row->is_nullable ?? '') === 'YES' ? 'NULL' : 'NOT NULL';

        $defaultClause = '';
        if (($row->column_default ?? null) !== null && ($row->column_default ?? '') !== '') {
            $defaultClause = ' DEFAULT ' . DB::connection()->getPdo()->quote($row->column_default);
        }

        DB::statement(
            'ALTER TABLE `tb_baku_mutu` MODIFY `method_id` ' . $type .
            ' CHARACTER SET ' . $targetCharset . ' COLLATE ' . $targetCollation . ' ' . $null . $defaultClause
        );
    }

    public function up()
    {
        if (!Schema::hasTable('tb_baku_mutu')) {
            return;
        }

        $this->ensureUtf8mb4NameReportColumn();
        $this->ensureMethodIdColumnMatchesMsMethod();

        $needleSet = $this->normalizedNeedleSet();

        $candidatesQuery = DB::table('tb_baku_mutu');
        if (Schema::hasColumn('tb_baku_mutu', 'deleted_at')) {
            $candidatesQuery->whereNull('deleted_at');
        }

        // Kandidat: mengandung "ALT" (disaring lagi di PHP agar bukan SALT, dll.) atau sudah method ALT.
        $candidates = $candidatesQuery
            ->where(function ($q) {
                $q->where('name_report', 'like', '%ALT%')
                    ->orWhere('method_id', self::METHOD_ID_ALT);
            })
            ->get(['id_baku_mutu', 'name_report', 'method_id']);

        $idsToSync = [];
        foreach ($candidates as $row) {
            $norm = $this->normalizeNameReportForMatch($row->name_report);
            if ($norm === '') {
                continue;
            }
            if (isset($needleSet[$norm]) || $this->isAltMicroNameReport($norm)) {
                $idsToSync[] = $row->id_baku_mutu;
            }
        }

        $idsToSync = array_values(array_unique($idsToSync));

        if ($idsToSync === []) {
            return;
        }

        DB::table('tb_baku_mutu')
            ->whereIn('id_baku_mutu', $idsToSync)
            ->update([
                'method_id' => self::METHOD_ID_ALT,
            ]);
    }

    public function down()
    {
        // Data tidak dikembalikan; backup manual jika diperlukan.
    }
}
