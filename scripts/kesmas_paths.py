"""Path default register Excel Kesmas & output CSV (relatif ke root project)."""

from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = SCRIPT_DIR.parent
EXCEL_DIR = PROJECT_ROOT / "public" / "assets" / "excel"

MIKRO_REGISTER_XLSX = EXCEL_DIR / "2. Register Unit Mikrobiologi 2026.xlsx"
KIMIA_REGISTER_XLSX = EXCEL_DIR / "3. Register Unit Kimia-Fisika 2026.xlsx"

OUTPUT_PREFIX = SCRIPT_DIR / "output" / "kesmas_manual_sample_numbers_2026"
OUTPUT_DETAIL_CSV = SCRIPT_DIR / "output" / "kesmas_manual_sample_numbers_2026_detail.csv"
OUTPUT_PAIRED_CSV = SCRIPT_DIR / "output" / "kesmas_manual_sample_numbers_2026_paired.csv"
OUTPUT_PERMOHONAN_CSV = SCRIPT_DIR / "output" / "kesmas_manual_sample_numbers_2026_permohonan.csv"
