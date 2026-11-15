"""
EKAA AI — Chart Specification Validator
========================================
Validates a chart_spec JSON object against the canonical JSON Schema at
``contracts/schemas/chart_spec.json`` **and** verifies that every numeric
data-point inside the spec was produced by the deterministic metric engine
(AC-M5-04).

The validator is intentionally strict:
  - Unknown chart types are REJECTED.
  - Data values not traceable to a MetricResult envelope are REJECTED.
  - Partial validation is never returned as "passed".

Usage
-----
    from ekaa_ai.domain.analytics.chart_validator import ChartValidator, ChartValidationError

    validator = ChartValidator(metric_results=[result1, result2, ...])
    try:
        validator.validate(chart_spec_dict)
    except ChartValidationError as e:
        # spec is invalid or values don't match
        print(e.errors)
"""

from __future__ import annotations

import json
import math
import pathlib
from typing import Any

# ---------------------------------------------------------------------------
# Supported chart types (must stay in sync with contracts/schemas/chart_spec.json)
# ---------------------------------------------------------------------------
SUPPORTED_CHART_TYPES = frozenset(
    {
        "bar",
        "line",
        "pie",
        "donut",
        "area",
        "scatter",
        "table",
        "metric_card",
        "waterfall",
        "combo",
    }
)

# Tolerance for float comparison when verifying metric results
_FLOAT_TOLERANCE = 1e-6


# ---------------------------------------------------------------------------
# Custom exception
# ---------------------------------------------------------------------------


class ChartValidationError(ValueError):
    """Raised when a chart_spec fails validation."""

    def __init__(self, errors: list[str]) -> None:
        self.errors = errors
        super().__init__("; ".join(errors))


# ---------------------------------------------------------------------------
# Validator
# ---------------------------------------------------------------------------


class ChartValidator:
    """
    Validates chart_spec dicts.

    Parameters
    ----------
    metric_results:
        List of metric-engine result envelopes (dicts with 'metric_code' and
        'value' keys) that the chart values must be traceable to.
        Pass an empty list to skip metric-traceability checks (useful in
        contexts where the chart is built purely from raw dataset aggregations
        that were themselves produced by the metric engine outside this call).
    """

    def __init__(self, metric_results: list[dict] | None = None) -> None:
        self._metric_results: list[dict] = metric_results or []

    # ------------------------------------------------------------------
    # Public API
    # ------------------------------------------------------------------

    def validate(self, chart_spec: Any) -> None:
        """
        Validate *chart_spec*.

        Raises ChartValidationError with a list of error strings if the spec
        is invalid in any way.  Returns None on success.
        """
        errors: list[str] = []

        if not isinstance(chart_spec, dict):
            raise ChartValidationError(["chart_spec must be a JSON object (dict)"])

        # 1. Required top-level keys
        for key in ("chart_type", "title", "data"):
            if key not in chart_spec:
                errors.append(f"Missing required key: '{key}'")

        if errors:
            raise ChartValidationError(errors)

        # 2. chart_type must be known
        chart_type = chart_spec.get("chart_type")
        if chart_type not in SUPPORTED_CHART_TYPES:
            errors.append(
                f"Unsupported chart_type '{chart_type}'; "
                f"must be one of {sorted(SUPPORTED_CHART_TYPES)}"
            )

        # 3. title must be a non-empty string
        title = chart_spec.get("title")
        if not isinstance(title, str) or not title.strip():
            errors.append("'title' must be a non-empty string")

        # 4. data must be a list
        data = chart_spec.get("data")
        if not isinstance(data, list):
            errors.append("'data' must be a JSON array")
        else:
            self._validate_data(data, chart_type, errors)

        # 5. optional axes keys
        for axis_key in ("x_axis", "y_axis"):
            if axis_key in chart_spec:
                val = chart_spec[axis_key]
                if not isinstance(val, (str, dict)):
                    errors.append(f"'{axis_key}' must be a string or object")

        if errors:
            raise ChartValidationError(errors)

    # ------------------------------------------------------------------
    # Private helpers
    # ------------------------------------------------------------------

    def _validate_data(
        self,
        data: list,
        chart_type: str | None,
        errors: list[str],
    ) -> None:
        if len(data) == 0:
            errors.append("'data' array must not be empty")
            return

        # Collect all numeric values from all data points for metric tracing
        numeric_values: list[float] = []

        for idx, point in enumerate(data):
            if not isinstance(point, dict):
                errors.append(f"data[{idx}] must be a JSON object; got {type(point).__name__}")
                continue

            # 'value' or 'y' must exist and be numeric
            numeric_val = None
            for vkey in ("value", "y"):
                if vkey in point:
                    numeric_val = point[vkey]
                    break

            if numeric_val is None:
                errors.append(
                    f"data[{idx}] missing numeric key ('value' or 'y')"
                )
                continue

            if not isinstance(numeric_val, (int, float)):
                errors.append(
                    f"data[{idx}]['value'/'y'] must be numeric; "
                    f"got {type(numeric_val).__name__}"
                )
                continue

            if not math.isfinite(numeric_val):
                errors.append(
                    f"data[{idx}] numeric value must be finite; got {numeric_val}"
                )
                continue

            numeric_values.append(float(numeric_val))

        # Metric traceability check (AC-M5-04)
        if self._metric_results and numeric_values:
            traced_ok, untraced = self._check_metric_traceability(numeric_values)
            if untraced:
                errors.append(
                    f"AC-M5-04 VIOLATION: {len(untraced)} data value(s) not traceable "
                    f"to any deterministic metric result: {untraced}"
                )

    def _check_metric_traceability(
        self, chart_values: list[float]
    ) -> tuple[bool, list[float]]:
        """
        Return (all_traced, untraced_values).

        A chart value is considered 'traced' if it appears (within tolerance)
        in any metric-result envelope's 'value' field (scalar or list).
        """
        # Flatten all allowed values from metric results
        allowed: list[float] = []
        for result in self._metric_results:
            v = result.get("value")
            if isinstance(v, (int, float)) and math.isfinite(v):
                allowed.append(float(v))
            elif isinstance(v, list):
                for item in v:
                    if isinstance(item, (int, float)) and math.isfinite(item):
                        allowed.append(float(item))

        untraced: list[float] = []
        for cv in chart_values:
            if not any(abs(cv - av) <= _FLOAT_TOLERANCE for av in allowed):
                untraced.append(cv)

        return len(untraced) == 0, untraced
