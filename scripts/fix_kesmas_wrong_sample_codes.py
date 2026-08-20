#!/usr/bin/env python3
"""
Perbaiki nomor sampel Kesmas yang salah (0001 / tanpa zero-pad) dengan mencocokkan
ke register Excel via CSV permohonan.

Register Excel sumber: public/assets/excel/ (generate CSV dulu).

Usage:
  python3 scripts/generate_kesmas_manual_numbers_from_register.py
  python3 scripts/fix_kesmas_wrong_sample_codes.py --dry-run
  python3 scripts/fix_kesmas_wrong_sample_codes.py --apply
"""

from __future__ import annotations

import argparse
import csv
import re
import subprocess
import sys
from difflib import SequenceMatcher
from pathlib import Path
from typing import Dict, List, Optional, Tuple

from kesmas_paths import OUTPUT_PERMOHONAN_CSV

DEFAULT_CSV = OUTPUT_PERMOHONAN_CSV


def normalize_name(s: str) -> str:
    s = (s or "").lower().strip()
    s = re.sub(r"[^a-z0-9\s]", " ", s)
    return re.sub(r"\s+", " ", s).strip()


def code_key(code: str) -> Tuple[str, str]:
    """(TYPE, LAB) dari AM.02/0316/2026"""
    code = (code or "").strip()
    m = re.match(r"^([A-Za-z]+)\.(\d{2})/", code)
    if not m:
        return ("", "")
    return (m.group(1).upper(), m.group(2))


def needs_fix(code: str) -> bool:
    if not code:
        return False
    if re.search(r"/0001/2026$", code):
        return True
    parts = code.split("/")
    if len(parts) >= 2 and re.fullmatch(r"\d{1,3}", parts[1]):
        return True
    return False


def normalize_code(code: str) -> str:
    parts = (code or "").strip().split("/")
    if len(parts) < 3:
        return code
    seq = int(re.sub(r"\D", "", parts[1]) or 0)
    if seq > 0:
        parts[1] = f"{seq:04d}"
    return "/".join(parts)


def load_permohonan_csv(path: Path) -> List[dict]:
    rows = []
    with path.open(newline="", encoding="utf-8") as f:
        for row in csv.DictReader(f):
            codes = [c.strip() for c in (row.get("nomor_sampel_semua") or "").split("|") if c.strip()]
            row["_codes"] = [normalize_code(c) for c in codes]
            rows.append(row)
    return rows


def middle_num(code: str) -> int:
    parts = (code or "").split("/")
    if len(parts) < 2:
        return 0
    return int(re.sub(r"\D", "", parts[1]) or 0)


def permohonan_has_high_register(samples: List[dict]) -> bool:
    nums = [middle_num(s["codesample_samples"]) for s in samples]
    return max(nums or [0]) >= 100


def best_csv_match(pelanggan: str, samples: List[dict], csv_rows: List[dict]) -> Optional[dict]:
    good_nums = {
        middle_num(s["codesample_samples"])
        for s in samples
        if not needs_fix(s["codesample_samples"])
    }
    target = normalize_name(pelanggan)
    best = None
    best_score = -1.0
    best_overlap = 0
    for row in csv_rows:
        csv_nums = {middle_num(c) for c in row["_codes"]}
        overlap = len(good_nums & csv_nums)
        name_score = 0.0
        if target:
            for candidate in (row.get("pelanggan") or "", row.get("alamat") or ""):
                name_score = max(
                    name_score,
                    SequenceMatcher(None, target, normalize_name(candidate)).ratio(),
                )
        score = overlap * 10.0 + name_score
        if score > best_score:
            best_score = score
            best_overlap = overlap
            best = row
    if best_overlap >= 2:
        return best
    if target and best_score >= 0.55:
        return best
    return None


def mysql_query(sql: str) -> str:
    env_file = Path(__file__).resolve().parents[1] / ".env"
    cfg = {}
    for line in env_file.read_text().splitlines():
        if "=" in line and not line.startswith("#"):
            k, v = line.split("=", 1)
            cfg[k.strip()] = v.strip().strip("'\"")
    cmd = [
        "mysql",
        "-h", cfg.get("DB_HOST", "127.0.0.1"),
        "-P", cfg.get("DB_PORT", "3306"),
        "-u", cfg.get("DB_USERNAME", "root"),
        f"-p{cfg.get('DB_PASSWORD', '')}",
        cfg.get("DB_DATABASE", ""),
        "-N",
        "-B",
        "-e",
        sql,
    ]
    proc = subprocess.run(cmd, capture_output=True, text=True)
    if proc.returncode != 0:
        raise RuntimeError(proc.stderr.strip() or proc.stdout)
    return proc.stdout


def fetch_bad_groups() -> Dict[str, List[dict]]:
    sql = """
    SELECT s.id_samples, s.permohonan_uji_id, s.codesample_samples, s.name_pelanggan,
           st.code_sample_type
    FROM tb_samples s
    LEFT JOIN ms_sample_type st ON st.id_sample_type = s.typesample_samples
    WHERE s.deleted_at IS NULL
      AND s.permohonan_uji_id IN (
        SELECT DISTINCT permohonan_uji_id FROM tb_samples
        WHERE deleted_at IS NULL
          AND (codesample_samples REGEXP '/0001/2026'
               OR codesample_samples REGEXP '\\.[0-9]{2}/[0-9]{1,3}/2026')
      )
    ORDER BY s.permohonan_uji_id, s.codesample_samples;
    """
    out = mysql_query(sql)
    groups: Dict[str, List[dict]] = {}
    for line in out.splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        if len(parts) < 5:
            continue
        pid = parts[1]
        groups.setdefault(pid, []).append(
            {
                "id_samples": parts[0],
                "permohonan_uji_id": parts[1],
                "codesample_samples": parts[2],
                "name_pelanggan": parts[3] if parts[3] != "NULL" else "",
                "type_code": parts[4] if parts[4] != "NULL" else "",
            }
        )
    return groups


def plan_fixes(groups: Dict[str, List[dict]], csv_rows: List[dict]) -> List[dict]:
    fixes = []
    for pid, samples in groups.items():
        pelanggan = next((s["name_pelanggan"] for s in samples if s["name_pelanggan"]), "")
        csv_row = best_csv_match(pelanggan, samples, csv_rows)
        if not csv_row:
            fixes.append(
                {
                    "permohonan_uji_id": pid,
                    "pelanggan": pelanggan,
                    "status": "NO_CSV_MATCH",
                    "id_samples": "",
                    "old": "",
                    "new": "",
                }
            )
            continue

        csv_codes = list(csv_row["_codes"])
        used = {
            normalize_code(s["codesample_samples"])
            for s in samples
            if not needs_fix(s["codesample_samples"])
        }
        available = [c for c in csv_codes if c not in used]

        for sample in samples:
            old = sample["codesample_samples"]
            if not needs_fix(old):
                continue
            if re.search(r"/0001/2026$", old) and not permohonan_has_high_register(samples):
                continue
            t, lab = code_key(old)
            if not t:
                t = (sample.get("type_code") or "").upper()
                parts = old.split("/")
                if len(parts) >= 1 and "." in parts[0]:
                    lab = parts[0].split(".")[-1]

            match = None
            for i, cand in enumerate(available):
                ct, cl = code_key(cand)
                if ct == t and cl == lab:
                    match = available.pop(i)
                    break

            if match and code_key(match)[0] != t:
                available.insert(0, match)
                match = None

            if match:
                fixes.append(
                    {
                        "permohonan_uji_id": pid,
                        "pelanggan": pelanggan or csv_row.get("pelanggan", ""),
                        "status": "FIX",
                        "id_samples": sample["id_samples"],
                        "old": old,
                        "new": match,
                    }
                )
            else:
                new_code = normalize_code(old)
                if new_code != old:
                    fixes.append(
                        {
                            "permohonan_uji_id": pid,
                            "pelanggan": pelanggan or csv_row.get("pelanggan", ""),
                            "status": "PAD_ONLY",
                            "id_samples": sample["id_samples"],
                            "old": old,
                            "new": new_code,
                        }
                    )
                else:
                    fixes.append(
                        {
                            "permohonan_uji_id": pid,
                            "pelanggan": pelanggan or csv_row.get("pelanggan", ""),
                            "status": "UNRESOLVED",
                            "id_samples": sample["id_samples"],
                            "old": old,
                            "new": "",
                        }
                    )
    return fixes


def apply_fixes(fixes: List[dict]) -> None:
    for fx in fixes:
        if fx["status"] not in ("FIX", "PAD_ONLY") or not fx["new"]:
            continue
        new_code = fx["new"].replace("'", "''")
        seq = middle_num(fx["new"])
        sql = (
            f"UPDATE tb_samples SET "
            f"codesample_samples='{new_code}', "
            f"count_id={seq}, "
            f"is_nomor_sampel_manual=1 "
            f"WHERE id_samples='{fx['id_samples']}';"
        )
        mysql_query(sql)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--csv", default=str(DEFAULT_CSV))
    parser.add_argument("--dry-run", action="store_true", default=True)
    parser.add_argument("--apply", action="store_true")
    args = parser.parse_args()

    csv_path = Path(args.csv)
    if not csv_path.exists():
        print(f"CSV tidak ditemukan: {csv_path}", file=sys.stderr)
        return 1

    csv_rows = load_permohonan_csv(csv_path)
    groups = fetch_bad_groups()
    fixes = plan_fixes(groups, csv_rows)

    fixable = [f for f in fixes if f["status"] in ("FIX", "PAD_ONLY")]
    unresolved = [f for f in fixes if f["status"] in ("NO_CSV_MATCH", "UNRESOLVED")]

    print(f"Permohonan terdampak : {len(groups)}")
    print(f"Dapat diperbaiki      : {len(fixable)}")
    print(f"Belum ter-resolve     : {len(unresolved)}")
    print()

    for fx in fixes:
        if fx["status"] in ("FIX", "PAD_ONLY"):
            print(f"[{fx['status']}] {fx['pelanggan'][:40]:40s} {fx['old']} -> {fx['new']}")
        elif fx["status"] == "UNRESOLVED":
            print(f"[UNRESOLVED] {fx['pelanggan'][:40]:40s} {fx['old']}")
        elif fx["status"] == "NO_CSV_MATCH":
            print(f"[NO_CSV] permohonan {fx['permohonan_uji_id'][:8]}... ({fx['pelanggan']})")

    if args.apply:
        apply_fixes(fixes)
        print("\nPerubahan diterapkan ke database.")
    else:
        print("\nDry-run saja. Jalankan dengan --apply untuk menyimpan.")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
