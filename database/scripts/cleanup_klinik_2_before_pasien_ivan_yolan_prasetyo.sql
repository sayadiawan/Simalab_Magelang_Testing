-- =============================================================================
-- PERINGATAN: Backup database penuh sebelum menjalankan skrip ini.
--
-- BAGIAN 1 — Hapus permanen transaksi klinik-2 (termasuk hasil, verifikasi, dll.)
--   untuk semua pemohonan di tb_permohonan_uji_klinik_2 yang lebih awal dari
--   pemeriksaan pertama pasien bernama IVAN YOLAN PRASETYO.
--
-- BAGIAN 2 — Kosongkan no_rekammedis_pasien untuk SEMUA baris di ms_pasien.
--   (Dapat di-comment jika hanya ingin Bagian 1.)
--
-- Asumsi "sebelum": waktu created_at pada tb_permohonan_uji_klinik_2.
-- Titik acuan = MIN(created_at) dari permohonan yang pasiennya
-- UPPER(TRIM(nama_pasien)) = 'IVAN YOLAN PRASETYO'.
-- Yang dihapus: baris dengan created_at < titik acuan (beserta turunan).
-- =============================================================================
-- Engine: MySQL / MariaDB.
-- Perbandingan UUID: CONVERT(... USING utf8mb4) agar kompatibel utf8mb3/utf8mb4
-- dan campuran collation (ERROR 1267 / 1253).
-- =============================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @anchor := (
  SELECT MIN(p.created_at)
  FROM tb_permohonan_uji_klinik_2 AS p
  INNER JOIN ms_pasien AS mp
    ON CONVERT(mp.id_pasien USING utf8mb4) COLLATE utf8mb4_bin
     = CONVERT(p.pasien_permohonan_uji_klinik USING utf8mb4) COLLATE utf8mb4_bin
  WHERE UPPER(TRIM(mp.nama_pasien)) = 'IVAN YOLAN PRASETYO'
);

-- Cek manual: jika NULL, Bagian 1 tidak menghapus apa pun (aman).
-- SELECT @anchor AS anchor_created_at;

-- =============================================================================
-- BAGIAN 1 — Hapus data klinik-2 sebelum titik acuan
-- =============================================================================

START TRANSACTION;

SELECT IF(
  @anchor IS NULL,
  'INFO: Pasien IVAN YOLAN PRASETYO tidak ada di permohonan klinik-2 — tidak ada baris yang dihapus di Bagian 1.',
  'OK: Titik acuan ditemukan — menghapus permohonan dengan created_at lebih awal.'
) AS precheck_bagian_1;

DROP TEMPORARY TABLE IF EXISTS tmp_puk_ids;
CREATE TEMPORARY TABLE tmp_puk_ids (
  id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY
);

INSERT INTO tmp_puk_ids (id)
SELECT p.id_permohonan_uji_klinik
FROM tb_permohonan_uji_klinik_2 AS p
WHERE @anchor IS NOT NULL
  AND p.created_at < @anchor;

DROP TEMPORARY TABLE IF EXISTS tmp_param_ids;
CREATE TEMPORARY TABLE tmp_param_ids (
  id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY
);

INSERT INTO tmp_param_ids (id)
SELECT pk.id_permohonan_uji_parameter_klinik
FROM tb_permohonan_uji_parameter_klinik AS pk
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(pk.permohonan_uji_klinik USING utf8mb4) COLLATE utf8mb4_bin;

DROP TEMPORARY TABLE IF EXISTS tmp_sub_ids;
CREATE TEMPORARY TABLE tmp_sub_ids (
  id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY
);

INSERT INTO tmp_sub_ids (id)
SELECT s.id_permohonan_uji_sub_parameter_klinik
FROM tb_permohonan_uji_sub_parameter_klinik AS s
INNER JOIN tmp_param_ids AS tp
  ON CONVERT(tp.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(s.permohonan_uji_parameter_klinik_id USING utf8mb4) COLLATE utf8mb4_bin;

-- --- Hapus turunan (anak dulu) ---

-- Jika tabel tidak ada di lingkungan Anda, comment blok ini.
DELETE g FROM global_lab_sequence_detail AS g
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(g.reference_id USING utf8mb4) COLLATE utf8mb4_bin
WHERE g.lab_type = 'klinik';

DELETE v FROM tb_verification_activity_samples AS v
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(v.is_klinik USING utf8mb4) COLLATE utf8mb4_bin;

DELETE h FROM tb_permohonan_uji_sub_parameter_klinik_history AS h
INNER JOIN tmp_sub_ids AS ts
  ON CONVERT(ts.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(h.permohonan_uji_sub_parameter_klinik_id USING utf8mb4) COLLATE utf8mb4_bin;

DELETE h FROM tb_permohonan_uji_parameter_klinik_history AS h
INNER JOIN tmp_param_ids AS tp
  ON CONVERT(tp.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(h.permohonan_uji_parameter_klinik_id USING utf8mb4) COLLATE utf8mb4_bin;

DELETE s FROM tb_permohonan_uji_sub_parameter_klinik AS s
INNER JOIN tmp_param_ids AS tp
  ON CONVERT(tp.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(s.permohonan_uji_parameter_klinik_id USING utf8mb4) COLLATE utf8mb4_bin;

DELETE pk FROM tb_permohonan_uji_parameter_klinik AS pk
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(pk.permohonan_uji_klinik USING utf8mb4) COLLATE utf8mb4_bin;

DELETE pp FROM tb_permohonan_uji_paket_klinik AS pp
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(pp.permohonan_uji_klinik USING utf8mb4) COLLATE utf8mb4_bin;

DELETE pay FROM tb_permohonan_uji_payment_klinik AS pay
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(pay.permohonan_uji_klinik_id USING utf8mb4) COLLATE utf8mb4_bin;

DELETE an FROM tb_permohonan_uji_analis_klinik AS an
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(an.permohonan_uji_klinik_id USING utf8mb4) COLLATE utf8mb4_bin;

DELETE s FROM tb_pengambilan_sample_klinik AS s
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(s.permohonan_uji_klinik_id USING utf8mb4) COLLATE utf8mb4_bin;

DELETE n FROM tb_number_klinik AS n
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(n.id_permohonan_uji_klinik USING utf8mb4) COLLATE utf8mb4_bin;

-- DELETE p2 FROM tb_permohonan_uji_paket_klinik2 AS p2
-- INNER JOIN tmp_puk_ids AS t ON t.id = p2.id_permohonan_uji_klinik;

-- DELETE p2 FROM tb_permohonan_uji_parameter_klinik_2 AS p2
-- INNER JOIN tmp_puk_ids AS t ON t.id = p2.permohonan_uji_klinik;

DELETE p FROM tb_permohonan_uji_klinik_2 AS p
INNER JOIN tmp_puk_ids AS t
  ON CONVERT(t.id USING utf8mb4) COLLATE utf8mb4_bin
   = CONVERT(p.id_permohonan_uji_klinik USING utf8mb4) COLLATE utf8mb4_bin;

COMMIT;

-- =============================================================================
-- BAGIAN 2 — Kosongkan nomor rekam medis (semua pasien)
-- =============================================================================
-- Hapus atau comment blok ini jika tidak ingin mengosongkan RM.

START TRANSACTION;

UPDATE ms_pasien
SET no_rekammedis_pasien = NULL
WHERE no_rekammedis_pasien IS NOT NULL;

-- Jika kolom NOT NULL, gunakan:
-- UPDATE ms_pasien SET no_rekammedis_pasien = '' WHERE no_rekammedis_pasien <> '';

COMMIT;
