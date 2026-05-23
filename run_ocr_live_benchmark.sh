#!/usr/bin/env bash
# OCR-Live-Benchmark — offline, secret-free, no-repo.
# Verarbeitet lokale OCR-Testdateien und erzeugt einen Benchmark-Artefakt.
# KEINE Dateien ins Repo übernehmen!

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CAILAMA_VENV="${SCRIPT_DIR}/CaiLama/.venv/bin/python"
OUTPUT_DIR="/tmp/cailama-ocr-benchmark-results"
mkdir -p "$OUTPUT_DIR"

TEST_PDF_DIR="/srv/schach/cailama-data/ocr_test_pdf"
TEST_IMG_DIR="/srv/schach/cailama-data/ocr_test_img"

echo "=== CaiLama OCR Live Benchmark ==="
echo "Output: $OUTPUT_DIR"
echo ""

# Pruefe, dass CaiLama verfuegbar ist
if [[ ! -x "$CAILAMA_VENV" ]]; then
    echo "ERROR: CaiLama .venv not found at $CAILAMA_VENV"
    exit 1
fi

# Erzeuge eine kurze Python-Analyse auf Basis der lokalen OCR-Daten
# KEINE echten Pfade im Output; nur Dateinamen und Kennzahlen
"$CAILAMA_VENV" << 'PYEOF'
import json
import os
import sys
import time
from pathlib import Path
from datetime import datetime, timezone

# Add CaiLama source to path
sys.path.insert(0, str(Path(__file__).resolve().parent / "CaiLama" / "src"))

from cailama.knowledge.ocr_documents import (
    analyze_ocr_source,
    discover_ocr_sources,
    write_ocr_result_files,
    write_ocr_batch_summary,
)
from cailama.knowledge.ocr_quality_gates import (
    gate_summary,
    run_ocr_quality_gates,
)

# -----------------------------------------------------------
# Konfiguration (nur Dateinamen, keine Secrets/Pfade)
# -----------------------------------------------------------
OUTPUT_DIR = Path("/tmp/cailama-ocr-benchmark-results")
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

PDF_DIR = Path("/srv/schach/cailama-data/ocr_test_pdf")
IMG_DIR = Path("/srv/schach/cailama-data/ocr_test_img")

pdf_sources = [p for p in PDF_DIR.glob("*.pdf")]
img_sources = [p for p in IMG_DIR.glob("*.png") if "Zone" not in p.name]

all_sources = sorted(pdf_sources + img_sources)

if not all_sources:
    print(json.dumps({"error": "no ocr test sources found"}))
    sys.exit(1)

# -----------------------------------------------------------
# Batch-Analyse — kurz gehalten für schnellen Smoke-Check
# -----------------------------------------------------------
results = []
start_all = time.monotonic()

for source in all_sources:
    t0 = time.monotonic()
    try:
        result = analyze_ocr_source(
            source,
            output_dir=OUTPUT_DIR,
            languages="deu",
            max_pages=2,           # nur 2 Seiten pro PDF = schnell
            render_dpi=150,        # niedrig = schnell
            detect_chess_diagrams=True,
            save_page_images=False,
            preproc="auto",
            page_timeout_seconds=60,
            total_timeout_seconds=120,
        )
        gates = run_ocr_quality_gates(result)
        summary = gate_summary(gates)
        elapsed = time.monotonic() - t0
        
        # Nur Dateiname, kein vollständiger Pfad
        results.append({
            "source_name": source.name,
            "source_type": result.source_type,
            "page_count": len(result.pages),
            "text_length": len(result.combined_text),
            "diagram_count": result.diagram_count,
            "gate_count": summary["total"],
            "gates_passed": summary["passed"],
            "all_gates_passed": summary["all_passed"],
            "duration_seconds": round(elapsed, 1),
        })
        
        # Ergebnisdateien schreiben (außerhalb des Repos)
        write_ocr_result_files(result, OUTPUT_DIR)
        
    except Exception as exc:
        elapsed = time.monotonic() - t0
        results.append({
            "source_name": source.name,
            "source_type": "error",
            "error": str(exc)[:120],
            "duration_seconds": round(elapsed, 1),
        })

total_elapsed = time.monotonic() - start_all

# -----------------------------------------------------------
# Benchmark-Artefakt — secretfrei, nur Kennzahlen
# -----------------------------------------------------------
artifact = {
    "date": datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
    "repo": "CaiLama",
    "git_ref": os.popen("git -C CaiLama rev-parse --short HEAD 2>/dev/null || echo unknown").read().strip(),
    "dataset": "local ocr_test_pdf + ocr_test_img",
    "dataset_note": "private chess training material, not in repo",
    "command": "cailama-ocr [PATH] --max-pages 2 --render-dpi 150 --languages deu",
    "results": results,
    "total_sources": len(results),
    "total_duration_seconds": round(total_elapsed, 1),
    "avg_duration_seconds": round(
        sum(r.get("duration_seconds", 0) for r in results) / len(results), 1
    ),
    "followup": [
        "--max-pages 2 ist ein Schnell-Smoke; volle Analyse braucht max_pages=None",
        "render-dpi 150 ist ungenau fuer Diagramm-Erkennung; 220+ empfohlen",
        "Diagramm-FEN-Rekonstruktion ist noch nicht implementiert (manuell pruefen)",
    ],
}

out_file = OUTPUT_DIR / "ocr_benchmark_artifact.json"
out_file.write_text(json.dumps(artifact, ensure_ascii=False, indent=2), encoding="utf-8")

print(json.dumps({
    "status": "ok",
    "artifact": str(out_file),
    "total_sources": len(results),
    "total_seconds": round(total_elapsed, 1),
}, indent=2))
PYEOF
