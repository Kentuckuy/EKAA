"""
EKAA AI — Narrative Number Validator
======================================
Verifies that every numeric literal appearing in a generated narrative text
was produced by the deterministic metric engine and matches a known result
(AC-M5-09).

Motivation
----------
The LLM synthesises prose from the structured metric results.  It must not
"hallucinate" a different number.  This validator scans the narrative for
floating-point and integer-looking tokens and cross-checks them against the
set of values emitted by the metric engine.

Usage
-----
    from ekaa_ai.domain.analytics.narrative_validator import (
        NarrativeValidator,
        NarrativeMismatchError,
    )

    validator = NarrativeValidator(metric_results=[...])
    try:
        validator.validate("Pertumbuhan tahunan sebesar 12,5% ...")
    except NarrativeMismatchError as e:
        print(e.mismatches)   # list of (token_str, float_value) pairs

Notes
-----
- Indonesian locale numbers (comma decimal, period thousands) are normalised
  before comparison, e.g. "1.234,56" → 1234.56.
- Percentage symbols are stripped; the underlying float is compared.
- Pure ordinals and years (4-digit integers 1900–2100) are skipped.
- Tolerance: ±0.01 absolute OR ±0.1% relative — whichever is wider.
"""

from __future__ import annotations

import math
import re
from typing import Any

# ---------------------------------------------------------------------------
# Tolerance for float comparison
# ---------------------------------------------------------------------------
_ABS_TOLERANCE = 0.01       # e.g. rounding to 2 decimal places in prose
_REL_TOLERANCE = 0.001      # 0.1 %

# Years in a plausible range are skipped (they appear as bare integers)
_YEAR_RANGE = range(1900, 2101)

# Regex to find numeric tokens in prose (including Indonesian punctuation)
# Matches: optional minus, digits, optional (thousands-sep) groups, optional decimal
_NUMBER_RE = re.compile(
    r"-?"                              # optional leading minus
    r"\d{1,3}"                         # leading digits (1-3)
    r"(?:[.,]\d{3})*"                  # optional thousands groups (. or ,)
    r"(?:[.,]\d+)?"                    # optional decimal part
    r"\s*%?"                           # optional trailing percent
)


# ---------------------------------------------------------------------------
# Custom exception
# ---------------------------------------------------------------------------


class NarrativeMismatchError(ValueError):
    """
    Raised when a numeric token in the narrative cannot be traced to any
    deterministic metric result.
    """

    def __init__(self, mismatches: list[tuple[str, float]]) -> None:
        self.mismatches = mismatches
        formatted = ", ".join(f"'{t}' ({v})" for t, v in mismatches)
        super().__init__(
            f"AC-M5-09 VIOLATION: {len(mismatches)} number(s) in narrative "
            f"not traceable to metric results: {formatted}"
        )


# ---------------------------------------------------------------------------
# Normaliser
# ---------------------------------------------------------------------------


def _normalise_id(token: str) -> float | None:
    """
    Convert an Indonesian-locale numeric token to a Python float.

    Strategy:
    1. Strip trailing % (we compare underlying values, not percentage-ness).
    2. Detect locale by counting separators:
       - If the LAST separator is ',' and earlier ones are '.'  → Indonesian
         (e.g. 1.234,56  →  1234.56)
       - If the LAST separator is '.' and earlier ones are ','  → European
         (some documents use European locale)
       - Single separator at position consistent with decimal  → decimal
       - No separator  → plain integer

    Returns None if the token cannot be parsed.
    """
    s = token.strip().rstrip("%").strip()
    if not s:
        return None

    # Detect separators
    dot_positions = [i for i, c in enumerate(s.lstrip("-")) if c == "."]
    comma_positions = [i for i, c in enumerate(s.lstrip("-")) if c == ","]

    if not dot_positions and not comma_positions:
        # plain integer or float without separators
        try:
            return float(s)
        except ValueError:
            return None

    last_dot = dot_positions[-1] if dot_positions else -1
    last_comma = comma_positions[-1] if comma_positions else -1

    # Both present: last separator is decimal
    if dot_positions and comma_positions:
        if last_comma > last_dot:
            # Indonesian: 1.234,56
            normalized = s.replace(".", "").replace(",", ".")
        else:
            # European: 1,234.56  → standard
            normalized = s.replace(",", "")
    elif comma_positions:
        # Only commas — could be thousands (1,234) or decimal (1,5)
        # Ambiguous: treat as decimal if single comma near end, else thousands
        if len(comma_positions) == 1:
            after_comma = len(s.lstrip("-").split(",")[1])
            if after_comma <= 2:
                # Likely decimal: 1,5  or  12,50
                normalized = s.replace(",", ".")
            else:
                # Likely thousands: 1,234
                normalized = s.replace(",", "")
        else:
            # Multiple commas  → all thousands separators (US style)
            normalized = s.replace(",", "")
    else:
        # Only dots — treat last one as decimal if length after < 3
        after_dot = len(s.lstrip("-").split(".")[-1])
        if after_dot <= 2:
            # Likely decimal: 1.5 or 12.50
            normalized = s  # already valid Python float
        else:
            # Thousands: 1.234
            normalized = s.replace(".", "")

    try:
        return float(normalized)
    except ValueError:
        return None


# ---------------------------------------------------------------------------
# Validator
# ---------------------------------------------------------------------------


class NarrativeValidator:
    """
    Validates that all numeric literals in a narrative text match metric
    engine results.

    Parameters
    ----------
    metric_results:
        List of metric-engine result envelopes.  If empty, validation is
        skipped (all numbers pass).  This allows use in contexts where
        metrics are not yet available.
    strict_mode:
        When True (default) raise NarrativeMismatchError on first batch of
        mismatches.  When False, return a report dict instead.
    """

    def __init__(
        self,
        metric_results: list[dict] | None = None,
        strict_mode: bool = True,
    ) -> None:
        self._metric_results: list[dict] = metric_results or []
        self._strict_mode = strict_mode
        self._allowed: list[float] = self._build_allowed_set()

    # ------------------------------------------------------------------

    def _build_allowed_set(self) -> list[float]:
        allowed: list[float] = []
        for result in self._metric_results:
            v = result.get("value")
            if isinstance(v, (int, float)) and math.isfinite(v):
                allowed.append(float(v))
                # Also add percentage representation if applicable
                allowed.append(float(v) * 100.0)
            elif isinstance(v, list):
                for item in v:
                    if isinstance(item, (int, float)) and math.isfinite(item):
                        allowed.append(float(item))
                        allowed.append(float(item) * 100.0)
        return allowed

    def validate(self, narrative: str) -> dict:
        """
        Scan *narrative* for numeric tokens and validate each against the
        allowed metric values.

        Returns a report dict:
            {"status": "ok", "checked": N, "passed": N}
        or raises NarrativeMismatchError if strict_mode=True and there are
        mismatches.
        """
        if not isinstance(narrative, str):
            raise TypeError("narrative must be a string")

        tokens_checked: list[tuple[str, float]] = []  # (raw_token, float_val)
        mismatches: list[tuple[str, float]] = []

        for match in _NUMBER_RE.finditer(narrative):
            raw = match.group(0).strip()
            val = _normalise_id(raw)
            if val is None:
                continue

            # Skip years
            int_val = int(val) if val == int(val) else None
            if int_val is not None and int_val in _YEAR_RANGE and "." not in raw and "," not in raw:
                continue

            tokens_checked.append((raw, val))

            # If no metric results available, skip traceability check
            if not self._allowed:
                continue

            if not self._is_traced(val):
                mismatches.append((raw, val))

        if mismatches and self._strict_mode:
            raise NarrativeMismatchError(mismatches)

        return {
            "status": "ok" if not mismatches else "mismatch",
            "checked": len(tokens_checked),
            "passed": len(tokens_checked) - len(mismatches),
            "mismatches": mismatches,
        }

    def _is_traced(self, val: float) -> bool:
        for av in self._allowed:
            diff = abs(val - av)
            # absolute tolerance
            if diff <= _ABS_TOLERANCE:
                return True
            # relative tolerance
            if av != 0 and diff / abs(av) <= _REL_TOLERANCE:
                return True
        return False
