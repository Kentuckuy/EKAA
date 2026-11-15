"""
EKAA AI — Deterministic Metric Engine
======================================
Invariant (AGENTS.md §INV-07): The LLM NEVER computes final output numbers.
All metrics exposed to the answer-generation layer MUST be produced by the
deterministic functions in this module.  Any attempt to let the LLM produce
a numeric result that bypasses this module is a security/quality violation.

Every public function returns a uniform envelope:
  {
    "metric_code": str,
    "value":       float | list[float] | dict,
    "formula":     str,          # human-readable formula string
    "inputs":      dict,         # verbatim inputs used
    "warnings":    list[str]     # non-fatal advisory messages
  }

Raises MetricError (a ValueError subclass) for invalid / ambiguous inputs.
"""

from __future__ import annotations

import math
import statistics
from typing import Any, Literal


# ---------------------------------------------------------------------------
# Custom exception
# ---------------------------------------------------------------------------

class MetricError(ValueError):
    """Raised when a metric cannot be computed with the given inputs."""


# ---------------------------------------------------------------------------
# Private helpers
# ---------------------------------------------------------------------------

def _envelope(
    metric_code: str,
    value: Any,
    formula: str,
    inputs: dict,
    warnings: list[str] | None = None,
) -> dict:
    return {
        "metric_code": metric_code,
        "value": value,
        "formula": formula,
        "inputs": inputs,
        "warnings": warnings or [],
    }


def _require_finite(*values: float, label: str = "value") -> None:
    """Raise MetricError if any value is non-finite (NaN, Inf)."""
    for v in values:
        if not math.isfinite(v):
            raise MetricError(f"{label} must be finite; got {v!r}")


# ---------------------------------------------------------------------------
# Public metric functions
# ---------------------------------------------------------------------------

def cagr(
    value_start: float,
    value_end: float,
    n_periods: float,
) -> dict:
    """
    Compound Annual Growth Rate (CAGR).

    Formula: (V_end / V_start)^(1 / n_periods) - 1

    Raises MetricError if:
      - value_start <= 0  (logarithmically undefined / economically nonsensical)
      - value_end  <  0  (would produce complex number)
      - n_periods  <= 0  (undefined)
    """
    _require_finite(value_start, value_end, n_periods, label="cagr input")
    if value_start <= 0:
        raise MetricError(
            f"cagr: value_start must be > 0; got {value_start}"
        )
    if value_end < 0:
        raise MetricError(
            f"cagr: value_end must be >= 0; got {value_end}"
        )
    if n_periods <= 0:
        raise MetricError(
            f"cagr: n_periods must be > 0; got {n_periods}"
        )

    result = (value_end / value_start) ** (1.0 / n_periods) - 1.0
    return _envelope(
        metric_code="CAGR",
        value=round(result, 10),
        formula="(V_end / V_start)^(1/n) - 1",
        inputs={
            "value_start": value_start,
            "value_end": value_end,
            "n_periods": n_periods,
        },
    )


def yoy(
    value_current: float,
    value_prior: float,
) -> dict:
    """
    Year-over-Year growth rate.

    Formula: (V_current - V_prior) / |V_prior|

    Raises MetricError if value_prior == 0.
    """
    _require_finite(value_current, value_prior, label="yoy input")
    if value_prior == 0:
        raise MetricError(
            "yoy: value_prior is 0; division by zero is undefined"
        )

    result = (value_current - value_prior) / abs(value_prior)
    return _envelope(
        metric_code="YOY",
        value=round(result, 10),
        formula="(V_current - V_prior) / |V_prior|",
        inputs={"value_current": value_current, "value_prior": value_prior},
    )


def mom(
    value_current: float,
    value_prior: float,
) -> dict:
    """
    Month-over-Month growth rate.

    Formula: (V_current - V_prior) / |V_prior|

    Raises MetricError if value_prior == 0.
    """
    _require_finite(value_current, value_prior, label="mom input")
    if value_prior == 0:
        raise MetricError(
            "mom: value_prior is 0; division by zero is undefined"
        )

    result = (value_current - value_prior) / abs(value_prior)
    return _envelope(
        metric_code="MOM",
        value=round(result, 10),
        formula="(V_current - V_prior) / |V_prior|",
        inputs={"value_current": value_current, "value_prior": value_prior},
    )


def moving_average(
    values: list[float],
    window: int,
) -> dict:
    """
    Simple Moving Average (SMA) over a rolling window.

    Returns a list of floats (length = len(values) - window + 1).

    Raises MetricError if:
      - values is empty
      - window < 1
      - window > len(values)
    """
    if not values:
        raise MetricError("moving_average: values list must not be empty")
    if window < 1:
        raise MetricError(
            f"moving_average: window must be >= 1; got {window}"
        )
    if window > len(values):
        raise MetricError(
            f"moving_average: window ({window}) exceeds number of values ({len(values)})"
        )

    for v in values:
        _require_finite(v, label="moving_average value")

    result = [
        round(sum(values[i : i + window]) / window, 10)
        for i in range(len(values) - window + 1)
    ]
    return _envelope(
        metric_code="SMA",
        value=result,
        formula="sum(values[i:i+window]) / window",
        inputs={"values": values, "window": window},
    )


def share(
    part: float,
    total: float,
) -> dict:
    """
    Proportion (percentage share).

    Formula: part / total * 100

    Raises MetricError if total == 0.
    """
    _require_finite(part, total, label="share input")
    if total == 0:
        raise MetricError(
            "share: total is 0; division by zero is undefined"
        )

    result = part / total * 100.0
    warnings: list[str] = []
    if result < 0 or result > 100:
        warnings.append(
            f"share result {result:.4f}% is outside [0, 100]; "
            "verify that part and total have the same sign and part ≤ total."
        )
    return _envelope(
        metric_code="SHARE",
        value=round(result, 10),
        formula="part / total * 100",
        inputs={"part": part, "total": total},
        warnings=warnings,
    )


def rank(
    values: list[float],
    ascending: bool = True,
) -> dict:
    """
    Dense rank of each value in the list.

    Returns a parallel list of integer dense-ranks starting at 1.
    Ties receive the same rank; no gaps after ties (dense).

    Raises MetricError if values is empty.
    """
    if not values:
        raise MetricError("rank: values list must not be empty")
    for v in values:
        _require_finite(v, label="rank value")

    sorted_unique = sorted(set(values), reverse=not ascending)
    rank_map = {v: i + 1 for i, v in enumerate(sorted_unique)}
    result = [rank_map[v] for v in values]
    return _envelope(
        metric_code="RANK",
        value=result,
        formula="dense rank (ascending={ascending})".format(ascending=ascending),
        inputs={"values": values, "ascending": ascending},
    )


AggFunc = Literal["sum", "mean", "min", "max", "count"]


def aggregate(
    values: list[float],
    func: AggFunc,
) -> dict:
    """
    Apply an aggregation function to a list of values.

    Supported functions: sum | mean | min | max | count

    Raises MetricError if values is empty (except for count).
    """
    if func == "count":
        return _envelope(
            metric_code="COUNT",
            value=len(values),
            formula="count(values)",
            inputs={"values": values, "func": func},
        )

    if not values:
        raise MetricError(
            f"aggregate: values list must not be empty for func={func!r}"
        )
    for v in values:
        _require_finite(v, label="aggregate value")

    func_map: dict[str, Any] = {
        "sum": (sum, "sum(values)"),
        "mean": (statistics.mean, "mean(values)"),
        "min": (min, "min(values)"),
        "max": (max, "max(values)"),
    }
    if func not in func_map:
        raise MetricError(
            f"aggregate: unknown func {func!r}; "
            f"must be one of {list(func_map.keys()) + ['count']}"
        )

    fn, formula = func_map[func]
    result = fn(values)
    return _envelope(
        metric_code=func.upper(),
        value=round(float(result), 10),
        formula=formula,
        inputs={"values": values, "func": func},
    )


def delta(
    value_new: float,
    value_old: float,
) -> dict:
    """
    Absolute change between two values.

    Formula: value_new - value_old
    """
    _require_finite(value_new, value_old, label="delta input")
    result = value_new - value_old
    return _envelope(
        metric_code="DELTA",
        value=round(result, 10),
        formula="value_new - value_old",
        inputs={"value_new": value_new, "value_old": value_old},
    )
