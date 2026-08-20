#!/usr/bin/env python3
"""
Generate nomor sampel manual Kesmas dari register Excel Mikro + Kimia.

Urutan: tanggal → nama pelanggan (normalisasi) → alamat → jenis sampel → unit.
Pasangan Mikro/Kimia untuk baris yang sama (tanggal + pelanggan + alamat + jenis) digabung.

Usage (default Excel: public/assets/excel/):
  python3 scripts/generate_kesmas_manual_numbers_from_register.py
  python3 scripts/generate_kesmas_manual_numbers_from_register.py --year 2026
"""

from __future__ import annotations

import argparse
import csv
import re
import unicodedata
import zipfile
import xml.etree.ElementTree as ET
from dataclasses import dataclass, field
from datetime import datetime, timedelta
from difflib import SequenceMatcher
from pathlib import Path
from typing import Dict, List, Optional, Tuple

from kesmas_paths import (
    KIMIA_REGISTER_XLSX,
    MIKRO_REGISTER_XLSX,
    OUTPUT_PREFIX,
)

NS = {"m": "http://schemas.openxmlformats.org/spreadsheetml/2006/main"}


@dataclass
class RegisterRow:
    source: str
    unit: str  # mikro | kimia
    lab_code: str  # 02 | 01
    tanggal: Optional[datetime]
    register_no: int
    pelanggan: str
    alamat: str
    jenis: str
    parameter: str
    bulan_label: str = ""
    sheet: str = ""

    @property
    def type_code(self) -> str:
        return jenis_to_sample_type_code(self.jenis)

    @property
    def manual_code(self) -> str:
        return f"{self.type_code}.{self.lab_code}/{self.register_no:04d}/{self.year}"

    year: int = 2026

    def match_key(self) -> Tuple:
        return (
            self.tanggal.date().isoformat() if self.tanggal else "",
            normalize_name(self.pelanggan),
            normalize_name(self.alamat),
            normalize_name(self.jenis),
        )

    def sort_key(self) -> Tuple:
        return (
            self.tanggal or datetime.max,
            normalize_name(self.pelanggan),
            name_similarity_bucket(self.pelanggan),
            normalize_name(self.alamat),
            normalize_name(self.jenis),
            self.unit,
            self.register_no,
        )


def col_to_idx(col: str) -> int:
    n = 0
    for c in col:
        n = n * 26 + (ord(c) - 64)
    return n


def normalize_name(value: str) -> str:
    value = unicodedata.normalize("NFKD", value or "")
    value = "".join(ch for ch in value if not unicodedata.combining(ch))
    value = value.lower().strip()
    value = re.sub(r"[^a-z0-9\s]", " ", value)
    value = re.sub(r"\s+", " ", value)
    return value


def name_similarity_bucket(name: str) -> str:
    """Kelompokkan nama yang sangat mirip agar berdekatan saat sort."""
    base = normalize_name(name)
    if not base:
        return ""
    tokens = base.split()
    return " ".join(tokens[:2]) if len(tokens) >= 2 else base


def names_are_similar(a: str, b: str, threshold: float = 0.88) -> bool:
    na, nb = normalize_name(a), normalize_name(b)
    if not na or not nb:
        return False
    if na == nb:
        return True
    return SequenceMatcher(None, na, nb).ratio() >= threshold


def jenis_to_sample_type_code(jenis: str) -> str:
    j = (jenis or "").strip().upper()
    if not j:
        return "MM"
    if j.startswith("MM") or j == "MAKANAN" or "MAKAN" in j:
        return "MM"
    if "AIR MINUM" in j or j.startswith("AM") or "PDAM" in j or "GALON" in j:
        return "AM"
    if "AIR BERSIH" in j or j == "AB" or "HIGIENE" in j or j.startswith("AH"):
        return "AH"
    if "USAP" in j or j.startswith("UA"):
        return "UA"
    if "LIMBAH" in j or j.startswith("AL"):
        return "AL"
    if "KOLAM" in j or j.startswith("AK"):
        return "AK"
    if j.startswith("UDARA") or j.startswith("KU"):
        return "KU"
    return "MM"


def excel_serial_to_date(raw: str) -> Optional[datetime]:
    if not raw:
        return None
    try:
        serial = float(raw)
        if serial > 30000:
            return datetime(1899, 12, 30) + timedelta(days=serial)
    except (TypeError, ValueError):
        pass
    for fmt in ("%d-%m-%Y", "%d/%m/%Y", "%Y-%m-%d"):
        try:
            return datetime.strptime(raw.strip(), fmt)
        except ValueError:
            continue
    return None


def read_register_xlsx(path: Path, source: str, unit: str, lab_code: str, year: int) -> List[RegisterRow]:
    rows: List[RegisterRow] = []
    with zipfile.ZipFile(path) as zf:
        shared: List[str] = []
        if "xl/sharedStrings.xml" in zf.namelist():
            root = ET.fromstring(zf.read("xl/sharedStrings.xml"))
            for si in root.findall("m:si", NS):
                if si.find("m:t", NS) is not None:
                    shared.append(si.find("m:t", NS).text or "")
                else:
                    shared.append("".join(t.text or "" for t in si.findall(".//m:t", NS)))

        sheets = sorted(n for n in zf.namelist() if "worksheets/sheet" in n and n.endswith(".xml"))
        for sheet in sheets:
            root = ET.fromstring(zf.read(sheet))
            month_label = ""
            current_date: Optional[datetime] = None

            for header_row in root.findall(".//m:sheetData/m:row", NS)[:6]:
                vals = _row_values(header_row, shared)
                joined = " ".join(vals)
                if "Bulan" in joined or "BULAN" in joined:
                    month_label = joined

            for row_el in root.findall(".//m:sheetData/m:row", NS):
                vals = _row_values(row_el, shared)
                if len(vals) < 6:
                    continue
                if vals[0] == "NO" and vals[1] == "TANGGAL":
                    continue

                nomor_raw = vals[2].strip()
                pelanggan = vals[3].strip()
                if not nomor_raw or not pelanggan:
                    continue
                if not re.fullmatch(r"\d+(?:\.\d+)?", nomor_raw):
                    continue

                tanggal_parsed = excel_serial_to_date(vals[1].strip())
                if tanggal_parsed:
                    current_date = tanggal_parsed
                tanggal = current_date

                rows.append(
                    RegisterRow(
                        source=source,
                        unit=unit,
                        lab_code=lab_code,
                        tanggal=tanggal,
                        register_no=int(float(nomor_raw)),
                        pelanggan=pelanggan,
                        alamat=vals[4].strip() if len(vals) > 4 else "",
                        jenis=vals[5].strip() if len(vals) > 5 else "",
                        parameter=vals[8].strip() if len(vals) > 8 else "",
                        bulan_label=month_label,
                        sheet=sheet,
                        year=year,
                    )
                )
    return rows


def _row_values(row_el, shared: List[str]) -> List[str]:
    cells: Dict[int, str] = {}
    for cell in row_el.findall("m:c", NS):
        ref = cell.attrib.get("r", "")
        m = re.match(r"([A-Z]+)", ref)
        if not m:
            continue
        ci = col_to_idx(m.group(1))
        cell_type = cell.attrib.get("t")
        v = cell.find("m:v", NS)
        val = v.text if v is not None else ""
        if cell_type == "s" and val != "":
            val = shared[int(val)]
        elif cell_type == "inlineStr":
            is_el = cell.find("m:is/m:t", NS)
            val = is_el.text if is_el is not None else ""
        cells[ci] = str(val).strip()
    if not cells:
        return []
    return [cells.get(i, "") for i in range(1, max(cells) + 1)]


def build_permohonan_groups(rows: List[RegisterRow]) -> List[dict]:
    """Gabung semua sampel per pelanggan + tanggal + alamat (satu baris permohonan)."""
    groups: Dict[Tuple, dict] = {}
    for row in sorted(rows, key=lambda r: r.sort_key()):
        key = (
            row.tanggal.date().isoformat() if row.tanggal else "",
            normalize_name(row.pelanggan),
            normalize_name(row.alamat),
        )
        if key not in groups:
            groups[key] = {
                "tanggal": row.tanggal.strftime("%Y-%m-%d") if row.tanggal else "",
                "pelanggan": row.pelanggan,
                "alamat": row.alamat,
                "samples": [],
            }
        groups[key]["samples"].append(row)

    result: List[dict] = []
    for i, g in enumerate(
        sorted(groups.values(), key=lambda x: (x["tanggal"], normalize_name(x["pelanggan"]))),
        start=1,
    ):
        samples = sorted(g["samples"], key=lambda r: r.register_no)
        result.append(
            {
                "urut": i,
                "tanggal": g["tanggal"],
                "pelanggan": g["pelanggan"],
                "alamat": g["alamat"],
                "jumlah_sampel": len(samples),
                "nomor_sampel_semua": " | ".join(s.manual_code for s in samples),
                "register_semua": " | ".join(str(s.register_no) for s in samples),
                "parameter_semua": " | ".join(s.parameter for s in samples),
                "detail_per_baris": "\n".join(
                    f"{s.register_no:4d} {s.unit:5s} {s.jenis} -> {s.manual_code} ({s.parameter})"
                    for s in samples
                ),
            }
        )
    return result


def build_outputs(rows: List[RegisterRow]) -> Tuple[List[dict], List[dict], List[dict]]:
    sorted_rows = sorted(rows, key=lambda r: r.sort_key())

    detail: List[dict] = []
    for i, row in enumerate(sorted_rows, start=1):
        detail.append(
            {
                "urut": i,
                "tanggal": row.tanggal.strftime("%Y-%m-%d") if row.tanggal else "",
                "pelanggan": row.pelanggan,
                "alamat": row.alamat,
                "jenis_sampel": row.jenis,
                "kode_jenis": row.type_code,
                "unit": row.unit,
                "lab_code": row.lab_code,
                "register_no": row.register_no,
                "nomor_sampel_manual": row.manual_code,
                "parameter": row.parameter,
                "bulan_register": row.bulan_label,
                "sumber_file": row.source,
            }
        )

    # Pasangkan Mikro + Kimia berdasarkan kemiripan tanggal/pelanggan/alamat/jenis
    mikro = [r for r in sorted_rows if r.unit == "mikro"]
    kimia = [r for r in sorted_rows if r.unit == "kimia"]
    used_kimia: set[int] = set()
    paired: List[dict] = []

    for m in mikro:
        best_idx = None
        best_score = 0.0
        for idx, k in enumerate(kimia):
            if idx in used_kimia:
                continue
            score = pair_score(m, k)
            if score > best_score:
                best_score = score
                best_idx = idx

        k = kimia[best_idx] if best_idx is not None and best_score >= 0.65 else None
        if k is not None:
            used_kimia.add(best_idx)

        paired.append(
            {
                "tanggal": m.tanggal.strftime("%Y-%m-%d") if m.tanggal else (k.tanggal.strftime("%Y-%m-%d") if k and k.tanggal else ""),
                "pelanggan": m.pelanggan,
                "alamat": m.alamat,
                "jenis_sampel": m.jenis,
                "kode_jenis": m.type_code,
                "parameter_mikro": m.parameter,
                "nomor_mikro_manual": m.manual_code,
                "register_mikro": m.register_no,
                "parameter_kimia": k.parameter if k else "",
                "nomor_kimia_manual": k.manual_code if k else "",
                "register_kimia": k.register_no if k else "",
                "skor_pasangan": round(best_score, 3) if k else "",
            }
        )

    # Kimia tanpa pasangan Mikro
    for idx, k in enumerate(kimia):
        if idx in used_kimia:
            continue
        paired.append(
            {
                "tanggal": k.tanggal.strftime("%Y-%m-%d") if k.tanggal else "",
                "pelanggan": k.pelanggan,
                "alamat": k.alamat,
                "jenis_sampel": k.jenis,
                "kode_jenis": k.type_code,
                "parameter_mikro": "",
                "nomor_mikro_manual": "",
                "register_mikro": "",
                "parameter_kimia": k.parameter,
                "nomor_kimia_manual": k.manual_code,
                "register_kimia": k.register_no,
                "skor_pasangan": "",
            }
        )

    paired.sort(
        key=lambda p: (
            p["tanggal"] or "9999-99-99",
            normalize_name(p["pelanggan"]),
            normalize_name(p["alamat"]),
            normalize_name(p["jenis_sampel"]),
        )
    )
    for i, p in enumerate(paired, start=1):
        p["urut"] = i

    permohonan = build_permohonan_groups(sorted_rows)

    return detail, paired, permohonan


def pair_score(m: RegisterRow, k: RegisterRow) -> float:
    # Hanya pasangkan jenis sampel yang sama (MM+MM, AM+AM, dst.)
    if m.type_code != k.type_code:
        return 0.0

    score = 0.0
    if m.tanggal and k.tanggal and m.tanggal.date() == k.tanggal.date():
        score += 0.35
    elif m.tanggal and k.tanggal and abs((m.tanggal - k.tanggal).days) <= 1:
        score += 0.2
    else:
        return 0.0

    if names_are_similar(m.pelanggan, k.pelanggan, 0.85):
        score += 0.35
    else:
        return 0.0

    if normalize_name(m.alamat) == normalize_name(k.alamat):
        score += 0.2
    elif names_are_similar(m.alamat, k.alamat, 0.8):
        score += 0.1

    if normalize_name(m.jenis) == normalize_name(k.jenis):
        score += 0.15
    elif m.type_code == "MM":
        score += 0.1

    reg_gap = abs(m.register_no - k.register_no)
    if reg_gap == 1:
        score += 0.25
    elif reg_gap <= 3:
        score += 0.1

    return score


def write_csv(path: Path, rows: List[dict]) -> None:
    if not rows:
        return
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", newline="", encoding="utf-8-sig") as fh:
        writer = csv.DictWriter(fh, fieldnames=list(rows[0].keys()))
        writer.writeheader()
        writer.writerows(rows)


def main() -> None:
    parser = argparse.ArgumentParser(description="Generate nomor sampel manual dari register Kesmas")
    parser.add_argument(
        "--mikro",
        default=str(MIKRO_REGISTER_XLSX),
        help=f"Path register Mikrobiologi (default: {MIKRO_REGISTER_XLSX})",
    )
    parser.add_argument(
        "--kimia",
        default=str(KIMIA_REGISTER_XLSX),
        help=f"Path register Kimia-Fisika (default: {KIMIA_REGISTER_XLSX})",
    )
    parser.add_argument(
        "--output",
        default=str(OUTPUT_PREFIX),
        help=f"Output prefix (default: {OUTPUT_PREFIX})",
    )
    parser.add_argument("--year", type=int, default=2026)
    args = parser.parse_args()

    mikro_path = Path(args.mikro)
    kimia_path = Path(args.kimia)
    out_prefix = Path(args.output)

    if not mikro_path.exists():
        raise SystemExit(f"File tidak ditemukan: {mikro_path}")
    if not kimia_path.exists():
        raise SystemExit(f"File tidak ditemukan: {kimia_path}")

    mikro_rows = read_register_xlsx(mikro_path, mikro_path.name, "mikro", "02", args.year)
    kimia_rows = read_register_xlsx(kimia_path, kimia_path.name, "kimia", "01", args.year)
    all_rows = mikro_rows + kimia_rows

    detail, paired, permohonan = build_outputs(all_rows)

    detail_path = out_prefix.with_name(out_prefix.name + "_detail.csv")
    paired_path = out_prefix.with_name(out_prefix.name + "_paired.csv")
    permohonan_path = out_prefix.with_name(out_prefix.name + "_permohonan.csv")
    write_csv(detail_path, detail)
    write_csv(paired_path, paired)
    write_csv(permohonan_path, permohonan)

    print(f"Mikro rows : {len(mikro_rows)}")
    print(f"Kimia rows : {len(kimia_rows)}")
    print(f"Detail CSV : {detail_path}")
    print(f"Paired CSV : {paired_path}")
    print(f"Permohonan CSV : {permohonan_path}")
    print("\nContoh 5 baris pertama (paired):")
    for row in paired[:5]:
        print(
            f"  {row['urut']}. {row['tanggal']} | {row['pelanggan'][:30]} | "
            f"Mikro={row['nomor_mikro_manual']} | Kimia={row['nomor_kimia_manual']}"
        )


if __name__ == "__main__":
    main()
