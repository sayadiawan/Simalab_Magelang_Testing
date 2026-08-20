-- =============================================================================
-- Penomoran ulang no_rekammedis_pasien = 1, 2, 3, ... untuk subset pasien
-- "dari Ivan ke atas" (lihat definisi di bawah).
--
-- PERINGATAN: Backup ms_pasien (dan cek aplikasi) sebelum menjalankan.
-- Butuh MySQL 8.0+ atau MariaDB 10.2+ (CTE + ROW_NUMBER).
-- =============================================================================
-- DEFINISI (default):
--   @anchor = MIN(created_at) permohonan klinik-2 untuk pasien
--            UPPER(TRIM(nama_pasien)) = 'IVAN YOLAN PRASETYO' (bukan soft-delete).
--   Pasien yang dinomori: punya minimal satu baris tb_permohonan_uji_klinik_2
--   dengan MIN(created_at) permohonan per pasien >= @anchor.
--   Urutan nomor: MIN(created_at) naik, lalu nama pasien, lalu id_pasien.
--   (Jadi "mulai dari garis waktu pemeriksaan pertama Ivan", bukan urutan abjad
--   nama saja.)
--
-- Untuk HANYA pasien dengan waktu pertama STRICTLY SETELAH Ivan (tanpa Ivan &
--   tanpa orang lain yang share timestamp sama), uncomment blok ALTERNATIF di
--   bagian ranked (lihat komentar di file).
-- =============================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @anchor := (
  SELECT MIN(p.created_at)
  FROM tb_permohonan_uji_klinik_2 AS p
  INNER JOIN ms_pasien AS mp
    ON CONVERT(mp.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
     = CONVERT(p.pasien_permohonan_uji_klinik USING utf8mb4) COLLATE utf8mb4_bin
  WHERE p.deleted_at IS NULL
    AND UPPER(TRIM(mp.nama_pasien)) = 'IVAN YOLAN PRASETYO'
);

-- Preview (opsional): siapa saja yang akan dapat nomor & urutan
-- WITH first_per AS (
--   SELECT p.pasien_permohonan_uji_klinik AS id_pasien, MIN(p.created_at) AS first_ts
--   FROM tb_permohonan_uji_klinik_2 AS p
--   WHERE p.deleted_at IS NULL
--   GROUP BY p.pasien_permohonan_uji_klinik
-- )
-- SELECT fp.id_pasien,
--        mp.nama_pasien,
--        fp.first_ts,
--        ROW_NUMBER() OVER (
--          ORDER BY fp.first_ts ASC,
--                   UPPER(TRIM(mp.nama_pasien)) ASC,
--                   fp.id_pasien ASC
--        ) AS no_rm_baru
-- FROM first_per AS fp
-- INNER JOIN ms_pasien AS mp
--   ON CONVERT(mp.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
--    = CONVERT(fp.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
-- WHERE @anchor IS NOT NULL
--   AND fp.first_ts >= @anchor
-- ORDER BY no_rm_baru;

START TRANSACTION;

UPDATE ms_pasien AS p
INNER JOIN (
  WITH first_per AS (
    SELECT
      p.pasien_permohonan_uji_klinik AS id_pasien,
      MIN(p.created_at) AS first_ts
    FROM tb_permohonan_uji_klinik_2 AS p
    WHERE p.deleted_at IS NULL
    GROUP BY p.pasien_permohonan_uji_klinik
  ),
  ranked AS (
    SELECT
      fp.id_pasien,
      ROW_NUMBER() OVER (
        ORDER BY fp.first_ts ASC,
                 UPPER(TRIM(mp.nama_pasien)) ASC,
                 fp.id_pasien ASC
      ) AS rn
    FROM first_per AS fp
    INNER JOIN ms_pasien AS mp
      ON CONVERT(mp.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
       = CONVERT(fp.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
    WHERE @anchor IS NOT NULL
      AND fp.first_ts >= @anchor
    -- ALTERNATIF — hanya yang strictly setelah @anchor:
    -- AND fp.first_ts > @anchor
  )
  SELECT id_pasien, rn FROM ranked
) AS r
  ON CONVERT(p.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(r.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
SET p.no_rekammedis_pasien = r.rn;

COMMIT;

-- Jika no_rekammedis_pasien bertipe VARCHAR dan ingin padding nol (mis. 0001):
-- SET p.no_rekammedis_pasien = LPAD(r.rn, 4, '0');
