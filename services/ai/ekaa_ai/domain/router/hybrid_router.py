"""
EKAA AI — Hybrid Intent Router
==============================
Routes user queries to the appropriate processing pipeline (AC-M6-01, AC-M6-05).

Supported Intents:
  - 'document_rag': Questions about regulations, policies, circulars, SOPs,
                    and unstructured textual documentation.
  - 'analytics':    Questions asking for metrics, numeric aggregations, CAGR,
                    YoY growth, financial figures, and tabular dataset lookups.
  - 'clarification_required':
                    Ambiguous, overly brief, or multi-faceted queries where
                    intent confidence falls below the threshold (0.60).
  - 'out_of_scope': Queries outside the enterprise/academic institutional domain
                    (e.g., cooking recipes, general chit-chat, personal advice).

Invariant:
  - When intent is ambiguous or confidence < 0.60, the router MUST NOT guess
    blindly; it must emit 'clarification_required' with a clarifying prompt.
"""

from __future__ import annotations

import re
from typing import Any, Dict, List, Literal, Optional

IntentType = Literal["document_rag", "analytics", "clarification_required", "out_of_scope"]

# Confidence threshold below which clarification is requested (AC-M6-05)
CONFIDENCE_THRESHOLD = 0.60

# Domain keyword patterns (Indonesian)
ANALYTICS_KEYWORDS = [
    "berapa", "total", "jumlah", "cagr", "yoy", "mom", "pertumbuhan",
    "persentase", "rata-rata", "mean", "median", "anggaran", "biaya",
    "dana", "penerimaan", "pengeluaran", "rupiah", "apbd", "apbn",
    "kuartal", "semester", "tabel", "grafik", "kenaikan", "penurunan",
    "tren", "peringkat", "proporsi", "porsi", "kuantitatif", "realisasi"
]

DOCUMENT_KEYWORDS = [
    "peraturan", "sk", "surat keputusan", "kebijakan", "sop", "pedoman",
    "pasal", "ayat", "bab", "ketentuan", "syarat", "tata tertib",
    "prosedur", "mekanisme", "kewenangan", "tugas", "fungsi", "dokumen",
    "perjanjian", "kontrak", "hukum", "undang-undang", "panduan", "aturan"
]

OUT_OF_SCOPE_KEYWORDS = [
    "resep", "masak", "cuaca", "film", "bioskop", "zodiak", "horoskop",
    "lirik", "lagu", "chord", "game", "wisata", "liburan", "sepak bola",
    "pertandingan", "skor", "jokes", "lelucon", "ramalan"
]


class HybridRouter:
    """
    Classifies queries into actionable intents using hybrid lexical
    and semantic heuristics.
    """

    def __init__(self, confidence_threshold: float = CONFIDENCE_THRESHOLD) -> None:
        self.confidence_threshold = confidence_threshold

    def route(self, query: str) -> Dict[str, Any]:
        """
        Determine the intent for a user query.

        Returns:
            {
              "intent": "document_rag" | "analytics" | "clarification_required" | "out_of_scope",
              "confidence": float,
              "reason": str,
              "clarification_prompt": str | None,
              "metadata": dict
            }
        """
        q = query.strip()
        if not q:
            return {
                "intent": "clarification_required",
                "confidence": 1.0,
                "reason": "Pertanyaan kosong.",
                "clarification_prompt": "Silakan ajukan pertanyaan atau topik yang ingin Anda ketahui.",
                "metadata": {"query_length": 0}
            }

        # Check for overly brief or unspecific queries (e.g. "anggaran?", "bagaimana?")
        words = re.findall(r"\w+", q.lower())
        if len(words) <= 1:
            return {
                "intent": "clarification_required",
                "confidence": 0.30,
                "reason": "Pertanyaan terlalu singkat untuk menentukan konteks spesifik.",
                "clarification_prompt": f"Apakah Anda ingin mencari dokumen terkait '{q}', atau menghitung metrik data terkait?",
                "metadata": {"word_count": len(words)}
            }

        q_lower = q.lower()

        # Score matching categories with word boundaries to avoid substring false positives
        analytics_score = sum(1 for kw in ANALYTICS_KEYWORDS if re.search(rf"\b{re.escape(kw)}\b", q_lower))
        doc_score = sum(1 for kw in DOCUMENT_KEYWORDS if re.search(rf"\b{re.escape(kw)}\b", q_lower))
        out_of_scope_score = sum(1 for kw in OUT_OF_SCOPE_KEYWORDS if re.search(rf"\b{re.escape(kw)}\b", q_lower))

        # 1. Out of scope check
        if out_of_scope_score > 0 and analytics_score == 0 and doc_score == 0:
            return {
                "intent": "out_of_scope",
                "confidence": min(0.95, 0.70 + (out_of_scope_score * 0.1)),
                "reason": "Topik pertanyaan di luar ranah dokumen institusi dan analitik enterprise EKAA.",
                "clarification_prompt": "Sistem EKAA difokuskan untuk pencarian dokumen regulasi institusi dan analisis data numerik.",
                "metadata": {"matched_keywords": [kw for kw in OUT_OF_SCOPE_KEYWORDS if kw in q_lower]}
            }

        # 2. Both high or neither -> Ambiguity check
        total_matched = analytics_score + doc_score
        if total_matched == 0:
            # Low confidence fallback
            return {
                "intent": "clarification_required",
                "confidence": 0.45,
                "reason": "Kata kunci tidak cukup spesifik untuk membedakan kebutuhan dokumen regulasi atau data analitik.",
                "clarification_prompt": "Mohon perjelas apakah Anda membutuhkan kutipan dari dokumen regulasi atau perhitungan statistik/analitik numerik?",
                "metadata": {"matched_keywords": []}
            }

        # 3. Determine winner
        if analytics_score > doc_score:
            confidence = min(0.98, 0.65 + (analytics_score * 0.1) - (doc_score * 0.05))
            if confidence < self.confidence_threshold:
                return {
                    "intent": "clarification_required",
                    "confidence": confidence,
                    "reason": "Keyakinan klasifikasi berada di bawah batas ambang 0.60.",
                    "clarification_prompt": "Apakah Anda bermaksud meminta perhitungan data numerik atau penjelasan dari berkas kebijakan?",
                    "metadata": {"analytics_score": analytics_score, "doc_score": doc_score}
                }
            return {
                "intent": "analytics",
                "confidence": confidence,
                "reason": f"Ditemukan {analytics_score} indikator pertanyaan kuantitatif/analitik.",
                "clarification_prompt": None,
                "metadata": {"analytics_score": analytics_score, "doc_score": doc_score}
            }
        elif doc_score > analytics_score:
            confidence = min(0.98, 0.65 + (doc_score * 0.1) - (analytics_score * 0.05))
            if confidence < self.confidence_threshold:
                return {
                    "intent": "clarification_required",
                    "confidence": confidence,
                    "reason": "Keyakinan klasifikasi berada di bawah batas ambang 0.60.",
                    "clarification_prompt": "Apakah Anda bermaksud mencari isi pasal dokumen atau laporan angka terkait?",
                    "metadata": {"analytics_score": analytics_score, "doc_score": doc_score}
                }
            return {
                "intent": "document_rag",
                "confidence": confidence,
                "reason": f"Ditemukan {doc_score} indikator pencarian dokumen/regulasi.",
                "clarification_prompt": None,
                "metadata": {"analytics_score": analytics_score, "doc_score": doc_score}
            }
        else:
            # Exact tie -> Clarification required
            return {
                "intent": "clarification_required",
                "confidence": 0.50,
                "reason": "Pertanyaan memuat indikator seimbang antara kebutuhan dokumen dan analitik data.",
                "clarification_prompt": "Pertanyaan Anda memuat aspek regulasi dan data numerik. Bagian mana yang ingin Anda prioritaskan?",
                "metadata": {"analytics_score": analytics_score, "doc_score": doc_score}
            }
