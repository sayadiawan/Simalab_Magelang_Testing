#!/usr/bin/env python3
"""
Sinkronkan nomor sampel DB ke register Excel (via CSV).

Strategi:
  1. Kelompokkan per permohonan_uji_id, nama pelanggan dari sample ATAU ms_customer.
  2. Cocokkan ke CSV permohonan (nama + overlap nomor).
  3. Perbaiki sampel yang nomornya tidak ada di register → pasangkan type+lab (MM.01, MM.02, ...).
  4. Permohonan makanan banyak jenis: cocokkan titik_pengambilan ke CSV detail.

Register Excel: public/assets/excel/

Usage:
  python3 scripts/generate_kesmas_manual_numbers_from_register.py
  python3 scripts/sync_kesmas_sample_codes_from_register.py --dry-run
  python3 scripts/sync_kesmas_sample_codes_from_register.py --apply
  python3 scripts/sync_kesmas_sample_codes_from_register.py --apply --pelanggan "Paulus"
"""

from __future__ import annotations

import argparse
import csv
import re
import subprocess
import sys
from collections import defaultdict
from datetime import datetime
from difflib import SequenceMatcher
from pathlib import Path
from typing import Dict, List, Optional, Set, Tuple

from kesmas_paths import OUTPUT_DETAIL_CSV, OUTPUT_PERMOHONAN_CSV


def normalize_text(s: str) -> str:
    s = re.sub(r"<[^>]+>", " ", s or "")
    s = s.lower().strip()
    s = re.sub(r"[^a-z0-9\s]", " ", s)
    return re.sub(r"\s+", " ", s).strip()


def normalize_code(code: str, type_code: str = "") -> str:
    code = (code or "").strip()
    if not code:
        return ""
    if not re.match(r"^[A-Za-z]", code) and type_code:
        code = f"{type_code}.{code}"
    parts = code.split("/")
    if len(parts) < 3:
        return code
    seq = int(re.sub(r"\D", "", parts[1]) or 0)
    if seq > 0:
        parts[1] = f"{seq:04d}"
    return "/".join(parts)


def code_type_lab(code: str, fallback_type: str = "") -> Tuple[str, str]:
    code = (code or "").strip()
    m = re.match(r"^([A-Za-z]+)\.(\d{2})/", code)
    if m:
        return m.group(1).upper(), m.group(2)
    m2 = re.match(r"^(\d{2})/", code)
    if m2:
        return (fallback_type or "MM").upper(), m2.group(1)
    return (fallback_type or "").upper(), ""


def middle_num(code: str) -> int:
    parts = (code or "").split("/")
    if len(parts) < 2:
        return 0
    return int(re.sub(r"\D", "", parts[1]) or 0)


def pelanggan_core(name: str) -> str:
    """Nama tanpa teks kurung / sufiks program (untuk pencocokan)."""
    s = re.sub(r"\([^)]*\)", " ", name or "")
    s = re.sub(r"\s*/\s*program\b.*", " ", s, flags=re.I)
    return normalize_text(s)


def pelanggan_variants(name: str) -> List[str]:
    variants: List[str] = []
    raw = (name or "").strip()
    if raw:
        variants.append(raw)
    for part in re.findall(r"\(([^)]+)\)", raw):
        if part.strip():
            variants.append(part.strip())
    core = re.sub(r"\([^)]*\)", "", raw).strip(" ,")
    if core:
        variants.append(core)
    return variants


def name_sim(a: str, b: str) -> float:
    scores = []
    for av in pelanggan_variants(a) or [a]:
        for bv in pelanggan_variants(b) or [b]:
            a_norm, b_norm = normalize_text(av), normalize_text(bv)
            if not a_norm or not b_norm:
                continue
            direct = SequenceMatcher(None, a_norm, b_norm).ratio()
            core = SequenceMatcher(None, pelanggan_core(av), pelanggan_core(bv)).ratio()
            ta, tb = set(a_norm.split()), set(b_norm.split())
            token = len(ta & tb) / max(len(ta | tb), 1)
            scores.append(max(direct, core, token))
    return max(scores) if scores else 0.0


def parse_date(value: str) -> Optional[datetime]:
    value = (value or "").strip()
    if not value:
        return None
    for fmt in ("%Y-%m-%d", "%Y-%m-%d %H:%M:%S", "%d/%m/%Y"):
        try:
            return datetime.strptime(value[:19], fmt)
        except ValueError:
            continue
    return None


def date_sim(db_date: str, csv_date: str) -> float:
    d1, d2 = parse_date(db_date), parse_date(csv_date)
    if not d1 or not d2:
        return 0.0
    diff = abs((d1.date() - d2.date()).days)
    if diff == 0:
        return 1.0
    if diff <= 1:
        return 0.85
    if diff <= 3:
        return 0.5
    return 0.0


def load_permohonan_csv(path: Path) -> List[dict]:
    rows = []
    with path.open(newline="", encoding="utf-8-sig") as f:
        for row in csv.DictReader(f):
            codes = [
                normalize_code(c.strip())
                for c in (row.get("nomor_sampel_semua") or "").split("|")
                if c.strip()
            ]
            row["_codes"] = codes
            row["_pelanggan_norm"] = normalize_text(row.get("pelanggan") or "")
            rows.append(row)
    return rows


def load_detail_csv(path: Path) -> List[dict]:
    rows = []
    with path.open(newline="", encoding="utf-8-sig") as f:
        for row in csv.DictReader(f):
            row["_jenis_norm"] = normalize_text(row.get("jenis_sampel") or "")
            row["_pelanggan_norm"] = normalize_text(row.get("pelanggan") or "")
            row["_unit"] = (row.get("unit") or "").lower().strip()
            row["_code"] = normalize_code(row.get("nomor_sampel_manual") or "")
            rows.append(row)
    return rows


def mysql_query(sql: str) -> str:
    env_file = Path(__file__).resolve().parents[1] / ".env"
    cfg = {}
    for line in env_file.read_text().splitlines():
        if "=" in line and not line.startswith("#"):
            k, v = line.split("=", 1)
            cfg[k.strip()] = v.strip().strip("'\"")
    cmd = [
        "mysql", "-h", cfg.get("DB_HOST", "127.0.0.1"),
        "-P", cfg.get("DB_PORT", "3306"),
        "-u", cfg.get("DB_USERNAME", "root"),
        f"-p{cfg.get('DB_PASSWORD', '')}",
        cfg.get("DB_DATABASE", ""),
        "-N", "-B", "-e", sql,
    ]
    proc = subprocess.run(cmd, capture_output=True, text=True)
    if proc.returncode != 0:
        raise RuntimeError(proc.stderr.strip() or proc.stdout)
    return proc.stdout


def fetch_db_samples(pelanggan_filter: Optional[str] = None) -> List[dict]:
    where = "s.deleted_at IS NULL AND s.codesample_samples LIKE '%/2026%'"
    if pelanggan_filter:
        esc = pelanggan_filter.replace("'", "''")
        where += f""" AND (
            s.name_pelanggan LIKE '%{esc}%'
            OR c.name_customer LIKE '%{esc}%'
        )"""
    sql = f"""
    SELECT s.id_samples, s.permohonan_uji_id, s.codesample_samples,
           s.titik_pengambilan,
           COALESCE(NULLIF(TRIM(s.name_pelanggan), ''), c.name_customer, '') AS pelanggan,
           st.code_sample_type,
           GROUP_CONCAT(DISTINCT m.name_method ORDER BY m.name_method SEPARATOR '||') AS methods,
           DATE_FORMAT(pu.date_permohonan_uji, '%Y-%m-%d') AS tanggal_permohonan
    FROM tb_samples s
    LEFT JOIN tb_permohonan_uji pu ON pu.id_permohonan_uji = s.permohonan_uji_id
    LEFT JOIN ms_customer c ON c.id_customer = pu.customer_id
    LEFT JOIN ms_sample_type st ON st.id_sample_type = s.typesample_samples
    LEFT JOIN tb_sample_method sm ON sm.sample_id = s.id_samples AND sm.deleted_at IS NULL
    LEFT JOIN ms_method m ON m.id_method = sm.method_id
    WHERE {where}
    GROUP BY s.id_samples
    ORDER BY pelanggan, s.codesample_samples;
    """
    out = mysql_query(sql)
    samples = []
    for line in out.splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        if len(parts) < 8:
            continue
        type_code = parts[5] if parts[5] != "NULL" else ""
        code = normalize_code(parts[2], type_code)
        t, lab = code_type_lab(code, type_code)
        titik = parts[3] if parts[3] != "NULL" else ""
        methods = [x for x in parts[6].split("||") if x and x != "NULL"]
        pelanggan = parts[4] if parts[4] != "NULL" else ""
        tanggal = parts[7] if parts[7] != "NULL" else ""
        samples.append({
            "id_samples": parts[0],
            "permohonan_uji_id": parts[1] if parts[1] != "NULL" else "",
            "codesample_samples": parts[2],
            "code_norm": code,
            "titik_pengambilan": titik,
            "pelanggan": pelanggan,
            "tanggal_permohonan": tanggal,
            "type_code": type_code,
            "methods": methods,
            "_pelanggan_norm": normalize_text(pelanggan),
            "_jenis_norm": normalize_text(titik),
            "_type": t,
            "_lab": lab,
        })
    return samples


def permohonan_match_score(
    pelanggan: str,
    group: List[dict],
    row: dict,
    tanggal_permohonan: str = "",
) -> float:
    db_codes = {s["code_norm"] for s in group if s["code_norm"]}
    csv_codes = set(row["_codes"])
    full_overlap = len(db_codes & csv_codes)
    ns = name_sim(pelanggan, row.get("pelanggan") or "")
    count_diff = abs(len(csv_codes) - len(group))
    ds = date_sim(tanggal_permohonan, row.get("tanggal") or "")

    if ns < 0.55 and full_overlap < 2:
        return -1.0
    if full_overlap >= 1 and ns < 0.45:
        return -1.0
    if count_diff > max(3, len(group) // 2) and full_overlap < 2 and ds < 0.85:
        return -1.0

    count_score = max(0.0, 15.0 - count_diff * 4.0)
    if count_diff == 0:
        count_score += 18.0
    elif count_diff == 1:
        count_score += 8.0
    return full_overlap * 20.0 + ns * 25.0 + count_score + ds * 15.0


def best_permohonan_row(
    pelanggan: str,
    group: List[dict],
    perm_rows: List[dict],
    used_perm_indices: Optional[Set[int]] = None,
) -> Optional[Tuple[dict, int]]:
    if not normalize_text(pelanggan):
        return None
    tanggal = next((s.get("tanggal_permohonan", "") for s in group if s.get("tanggal_permohonan")), "")
    best = None
    best_idx = -1
    best_score = -1.0
    for idx, row in enumerate(perm_rows):
        score = permohonan_match_score(pelanggan, group, row, tanggal)
        if used_perm_indices and idx in used_perm_indices:
            score -= 30.0
        if score > best_score:
            best_score = score
            best = row
            best_idx = idx
    if best is None or best_score < 8.0:
        return None
    ns = name_sim(pelanggan, best.get("pelanggan") or "")
    db_codes = {s["code_norm"] for s in group if s["code_norm"]}
    full_overlap = len(db_codes & set(best["_codes"]))
    ds = date_sim(tanggal, best.get("tanggal") or "")
    count_diff = abs(len(best["_codes"]) - len(group))
    if ns >= 0.72:
        return best, best_idx
    if full_overlap >= 2 and ns >= 0.5:
        return best, best_idx
    if full_overlap >= 1 and ds >= 0.85 and ns >= 0.58:
        return best, best_idx
    if count_diff <= 1 and ds >= 0.85 and ns >= 0.58:
        return best, best_idx
    if count_diff == 0 and ds >= 0.5 and ns >= 0.55:
        return best, best_idx
    return None


def register_can_cover_group(group: List[dict], perm_row: dict, used_codes: Set[str]) -> bool:
    needed: Dict[Tuple[str, str], int] = defaultdict(int)
    for s in group:
        needed[(s["_type"], s["_lab"])] += 1
    available: Dict[Tuple[str, str], int] = defaultdict(int)
    for code in perm_row["_codes"]:
        if code in used_codes:
            continue
        t, lab = code_type_lab(code)
        available[(t, lab)] += 1
    return all(available.get(key, 0) >= qty for key, qty in needed.items())


def trusted_permohonan_match(
    pelanggan: str,
    group: List[dict],
    perm_row: dict,
    used_codes: Optional[Set[str]] = None,
) -> bool:
    csv_set = set(perm_row["_codes"])
    if any(s["code_norm"] in csv_set for s in group):
        return True
    ns = name_sim(pelanggan, perm_row.get("pelanggan") or "")
    count_diff = abs(len(csv_set) - len(group))
    tanggal = next((s.get("tanggal_permohonan", "") for s in group if s.get("tanggal_permohonan")), "")
    ds = date_sim(tanggal, perm_row.get("tanggal") or "")
    pool = used_codes or set()
    if ns >= 0.88 and count_diff <= 2 and register_can_cover_group(group, perm_row, pool):
        return True
    if ns >= 0.75 and count_diff <= 1 and ds >= 0.85 and register_can_cover_group(group, perm_row, pool):
        return True
    if ns >= 0.65 and count_diff == 0 and ds >= 0.85:
        return True
    if ns >= 0.82 and ds >= 0.85 and register_can_cover_group(group, perm_row, pool):
        return True
    return False


def titik_pair_score(sample: dict, row: dict) -> float:
    if name_sim(sample["pelanggan"], row.get("pelanggan") or "") < 0.82:
        return 0.0
    jsim = name_sim(sample["_jenis_norm"], row["_jenis_norm"])
    if jsim < 0.72:
        return 0.0
    row_type, row_lab = code_type_lab(row["_code"])
    if sample["_type"] and row_type and sample["_type"] != row_type:
        return 0.0
    lab = sample["_lab"] or row_lab
    unit = row["_unit"]
    if lab == "01" and unit != "kimia":
        return 0.0
    if lab == "02" and unit != "mikro":
        return 0.0
    return jsim * 5.0


def plan_sync(
    perm_rows: List[dict],
    detail_rows: List[dict],
    db_samples: List[dict],
    only_wrong: bool,
) -> List[dict]:
    fixes: List[dict] = []
    all_register_codes: Set[str] = set()
    for row in perm_rows:
        all_register_codes.update(row["_codes"])

    global_used: Set[str] = {
        s["code_norm"] for s in db_samples if s["code_norm"] in all_register_codes
    }
    used_perm_indices: Set[int] = set()

    by_perm: Dict[str, List[dict]] = defaultdict(list)
    for s in db_samples:
        key = s["permohonan_uji_id"] or s["id_samples"]
        by_perm[key].append(s)

    # Proses grup besar dulu agar baris register tidak tertahan grup kecil
    perm_groups = sorted(by_perm.items(), key=lambda x: len(x[1]), reverse=True)

    for perm_id, group in perm_groups:
        pelanggan = next((s["pelanggan"] for s in group if s["pelanggan"]), "")
        match = best_permohonan_row(pelanggan, group, perm_rows, used_perm_indices)
        if not match:
            for s in group:
                fixes.append({**_no_match(s, "NO_PERM_CSV")})
            continue
        perm_row, perm_idx = match

        csv_codes: List[str] = list(perm_row["_codes"])
        csv_set: Set[str] = set(csv_codes)
        used_codes: Set[str] = set()
        assigned: Dict[str, str] = {}

        # Simpan nomor yang sudah benar
        for s in group:
            if s["code_norm"] in csv_set:
                used_codes.add(s["code_norm"])
                assigned[s["id_samples"]] = s["code_norm"]

        wrong_samples = [s for s in group if s["code_norm"] not in csv_set]
        if only_wrong and not wrong_samples:
            continue
        ns = name_sim(pelanggan, perm_row.get("pelanggan") or "")
        tanggal = next((s.get("tanggal_permohonan", "") for s in group if s.get("tanggal_permohonan")), "")
        ds = date_sim(tanggal, perm_row.get("tanggal") or "")
        trusted = trusted_permohonan_match(
            pelanggan, group, perm_row, global_used | used_codes
        )
        if only_wrong and not trusted and ns < 0.82 and not (ns >= 0.7 and ds >= 0.85):
            for s in wrong_samples:
                fixes.append({**_no_match(s, "NO_ANCHOR", perm_row.get("pelanggan", ""))})
            continue

        # Pass 1: titik_pengambilan → detail CSV (makanan banyak jenis)
        detail_pool = [
            r for r in detail_rows
            if name_sim(pelanggan, r.get("pelanggan") or "") >= 0.82
            and r["_code"] in csv_set
        ]
        pairs: List[Tuple[float, dict, dict]] = []
        for s in wrong_samples:
            if s["id_samples"] in assigned:
                continue
            if not s["_jenis_norm"]:
                continue
            for row in detail_pool:
                c = row["_code"]
                if c in used_codes or c in global_used:
                    continue
                sc = titik_pair_score(s, row)
                if sc > 0:
                    pairs.append((sc, s, row))
        pairs.sort(key=lambda x: x[0], reverse=True)
        for _sc, s, row in pairs:
            c = row["_code"]
            if s["id_samples"] in assigned or c in used_codes or c in global_used:
                continue
            assigned[s["id_samples"]] = c
            used_codes.add(c)
            global_used.add(c)

        # Pass 2: type + lab dari sisa kode register (hanya sampel salah)
        available_by_tl: Dict[Tuple[str, str], List[str]] = defaultdict(list)
        for code in sorted(csv_codes, key=middle_num):
            if code in used_codes or code in global_used:
                continue
            t, lab = code_type_lab(code)
            available_by_tl[(t, lab)].append(code)

        for s in sorted(wrong_samples, key=lambda x: middle_num(x["code_norm"])):
            if s["id_samples"] in assigned:
                continue
            key_tl = (s["_type"], s["_lab"])
            pool = available_by_tl.get(key_tl, [])
            while pool and (pool[0] in global_used):
                pool.pop(0)
            if not pool:
                fixes.append({**_no_match(s, "NO_CODE_LEFT", perm_row.get("pelanggan", ""))})
                continue
            new_code = pool.pop(0)
            assigned[s["id_samples"]] = new_code
            used_codes.add(new_code)
            global_used.add(new_code)

        # Tulis FIX
        group_fixed = False
        for s in wrong_samples:
            new_code = assigned.get(s["id_samples"])
            if not new_code:
                continue
            old_norm = s["code_norm"]
            if old_norm == new_code:
                continue
            group_fixed = True
            fixes.append({
                "status": "FIX",
                "id_samples": s["id_samples"],
                "pelanggan": s["pelanggan"],
                "jenis": s["titik_pengambilan"],
                "methods": ", ".join(s["methods"]),
                "old": s["codesample_samples"],
                "new": new_code,
                "register_pelanggan": perm_row.get("pelanggan", ""),
            })
        if group_fixed:
            used_perm_indices.add(perm_idx)

    return fixes


def _no_match(s: dict, reason: str, reg_pel: str = "") -> dict:
    return {
        "status": reason,
        "id_samples": s["id_samples"],
        "pelanggan": s["pelanggan"],
        "jenis": s["titik_pengambilan"],
        "methods": ", ".join(s["methods"]),
        "old": s["codesample_samples"],
        "new": "",
        "register_pelanggan": reg_pel,
    }


def apply_fixes(fixes: List[dict]) -> int:
    n = 0
    for fx in fixes:
        if fx["status"] != "FIX":
            continue
        new_code = fx["new"].replace("'", "''")
        seq = middle_num(fx["new"])
        sql = (
            f"UPDATE tb_samples SET codesample_samples='{new_code}', "
            f"count_id={seq}, is_nomor_sampel_manual=1 "
            f"WHERE id_samples='{fx['id_samples']}';"
        )
        mysql_query(sql)
        n += 1
    return n


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--permohonan", default=str(OUTPUT_PERMOHONAN_CSV))
    parser.add_argument("--detail", default=str(OUTPUT_DETAIL_CSV))
    parser.add_argument("--pelanggan", default="", help="Filter nama pelanggan")
    parser.add_argument("--all", action="store_true",
                        help="Perbaiki semua (default: hanya nomor yang tidak ada di register)")
    parser.add_argument("--dry-run", action="store_true",
                        help="Pratinjau saja (default jika tanpa --apply)")
    parser.add_argument("--apply", action="store_true")
    args = parser.parse_args()

    perm_path = Path(args.permohonan)
    detail_path = Path(args.detail)
    if not perm_path.exists() or not detail_path.exists():
        print("CSV belum ada. Jalankan:", file=sys.stderr)
        print("  python3 scripts/generate_kesmas_manual_numbers_from_register.py", file=sys.stderr)
        return 1

    perm_rows = load_permohonan_csv(perm_path)
    detail_rows = load_detail_csv(detail_path)
    db_samples = fetch_db_samples(args.pelanggan or None)
    fixes = plan_sync(perm_rows, detail_rows, db_samples, only_wrong=not args.all)

    fix_list = [f for f in fixes if f["status"] == "FIX"]
    stats: Dict[str, int] = defaultdict(int)
    for f in fixes:
        stats[f["status"]] += 1

    print(f"Sampel DB       : {len(db_samples)}")
    print(f"Perlu update    : {len(fix_list)}")
    for k, v in sorted(stats.items()):
        if k != "FIX":
            print(f"  {k}: {v}")
    print()

    for fx in fix_list[:100]:
        print(f"[FIX] {fx['pelanggan'][:28]:28s} | {fx['old']} -> {fx['new']}")
    if len(fix_list) > 100:
        print(f"... dan {len(fix_list) - 100} baris lainnya")

    if args.apply:
        n = apply_fixes(fixes)
        print(f"\n{n} nomor diperbarui di database.")
    else:
        print("\nDry-run. Tambahkan --apply untuk menyimpan.")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
