"""
EKAA AI — Prompt Injection & Security Guardrail
===============================================
Enforces security boundaries on incoming user inputs and indexed content
to neutralize prompt injection, jailbreaking, and data exfiltration (AC-M6-02).

Invariants:
  - Any prompt attempting to override system prompts, roleplay as unrestricted AI,
    or extract internal configuration MUST be BLOCKED.
  - Zero internal information, stack traces, or credentials shall be disclosed
    in the rejection response.
"""

from __future__ import annotations

import re
from typing import Any, Dict, List, Optional, Tuple

# Malicious signature patterns (Indonesian and English)
INJECTION_PATTERNS = [
    # Override / Jailbreak
    (r"ignore\s+(all\s+)?(previous|prior)\s+instructions?", "override_instruction"),
    (r"abaikan\s+(seluruh\s+|semua\s+)?instruksi\s+(sebelumnya|awal)", "override_instruction"),
    (r"lupakan\s+(semua|seluruh)\s+aturan", "override_instruction"),
    (r"disregard\s+(all\s+)?rules?", "override_instruction"),
    (r"you\s+are\s+now\s+(in\s+)?(developer|dan|god|unrestricted)\s+mode", "jailbreak_mode"),
    (r"kamu\s+sekarang\s+dalam\s+mode\s+(pengembang|tanpa\s+batas|bebas)", "jailbreak_mode"),
    (r"jailbreak", "jailbreak_mode"),
    (r"pretend\s+you\s+have\s+no\s+(rules|limits|safety)", "jailbreak_mode"),

    # Exfiltration of System Prompt or Secrets
    (r"reveal\s+(the\s+)?(system\s+prompt|initial\s+prompt|developer\s+message)", "system_prompt_exfiltration"),
    (r"tampilkan\s+(instruksi\s+rahasia|system\s+prompt|perintah\s+awal)", "system_prompt_exfiltration"),
    (r"print\s+(your\s+)?(system\s+prompt|internal\s+config)", "system_prompt_exfiltration"),
    (r"bocorkan\s+konfigurasi\s+internal", "system_prompt_exfiltration"),
    (r"show\s+(me\s+)?(passwords?|credentials?|api_keys?|tokens?)", "credential_harvesting"),
    (r"tampilkan\s+(kata\s+sandi|password|kunci\s+api|token)", "credential_harvesting"),

    # Privilege Escalation / ACL Bypass
    (r"bypass\s+(access\s+control|acl|permissions?)", "acl_bypass_attempt"),
    (r"lewati\s+(izin\s+akses|otorisasi|pembatasan)", "acl_bypass_attempt"),
    (r"tampilkan\s+semua\s+dokumen\s+tanpa\s+izin", "acl_bypass_attempt"),

    # Injection Payloads (SQL / Script)
    (r"(union\s+select|drop\s+table|insert\s+into|delete\s+from)", "sql_injection"),
    (r"<script[\s\S]*?>[\s\S]*?<\/script>", "xss_injection"),
    (r"javascript:\s*", "xss_injection"),
]


class InjectionGuard:
    """
    Security inspector analyzing user inputs and context chunks for malicious patterns.
    """

    def __init__(self, custom_patterns: Optional[List[Tuple[str, str]]] = None) -> None:
        raw_patterns = INJECTION_PATTERNS + (custom_patterns or [])
        self.compiled_rules = [
            (re.compile(pattern, re.IGNORECASE), threat_type)
            for pattern, threat_type in raw_patterns
        ]

    def sanitize_input(self, text: str) -> str:
        """
        Removes non-printable control characters, null bytes, and zero-width spaces.
        """
        if not text:
            return ""
        # Strip null bytes and control chars (except normal newline/tab/carriage return)
        cleaned = re.sub(r"[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]", "", text)
        # Strip zero-width characters commonly used to evade regex
        cleaned = re.sub(r"[\u200B-\u200D\uFEFF]", "", cleaned)
        return cleaned.strip()

    def inspect(self, text: str) -> Dict[str, Any]:
        """
        Inspect the provided text for injection or security risks.

        Returns:
            {
              "is_safe": bool,
              "threat_detected": str | None,
              "action": "allow" | "block",
              "sanitized_text": str,
              "rejection_message": str | None
            }
        """
        sanitized = self.sanitize_input(text)

        for regex, threat_type in self.compiled_rules:
            if regex.search(sanitized):
                return {
                    "is_safe": False,
                    "threat_detected": threat_type,
                    "action": "block",
                    "sanitized_text": sanitized,
                    "rejection_message": (
                        "Permintaan tidak dapat diproses karena terdeteksi pola instruksi "
                        "yang melanggar kebijakan keamanan sistem EKAA."
                    )
                }

        return {
            "is_safe": True,
            "threat_detected": None,
            "action": "allow",
            "sanitized_text": sanitized,
            "rejection_message": None
        }
