"""
EKAA AI — Tabular Dataset Importer
==================================
Handles ingestion of tabular data (CSV, TSV, JSON, XLSX) into structured
analytical storage (DuckDB / Parquet) with strict Indonesian locale parsing.

Key Invariants & Requirements (AC-M5-07):
  1. Indonesian Number Format:
     - Decimal separator: ',' (comma)
     - Thousands separator: '.' (period)
     - Example: '1.234.567,89' -> 1234567.89
  2. Indonesian Date Formats:
     - DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD
  3. All-or-Nothing Import:
     - If ANY row fails schema validation or type parsing, the entire
       import is aborted and rejected.
     - Partial commits are STRICTLY PROHIBITED (no zombie/corrupted states).
     - Full error report detailing row index, column name, raw value, and
       failure reason is returned.
  4. Access Control Metadata:
     - Imported dataset must be tagged with allowed principals (acl_principals).
"""

from __future__ import annotations

import csv
import datetime
import io
import re
from typing import Any, Dict, List, Optional, Tuple


class ImportErrorDetails(ValueError):
    """Raised when one or more rows fail parsing during import."""

    def __init__(self, errors: List[Dict[str, Any]]) -> None:
        self.errors = errors
        summary = f"{len(errors)} error(s) found during tabular import."
        super().__init__(summary)


def parse_indonesian_number(raw_val: Any) -> float:
    """
    Parse a number formatted in Indonesian style or standard float.
    '1.234,56' -> 1234.56
    '1.234.567' -> 1234567.0
    '50,5%' -> 50.5
    """
    if isinstance(raw_val, (int, float)):
        return float(raw_val)

    if not isinstance(raw_val, str):
        raise ValueError(f"Cannot parse non-string value as number: {type(raw_val).__name__}")

    s = raw_val.strip().rstrip("%").strip()
    if not s:
        raise ValueError("Empty string cannot be parsed as number")

    # Detect signs
    is_negative = False
    if s.startswith("-") or (s.startswith("(") and s.endswith(")")):
        is_negative = True
        s = s.strip("-()").strip()

    # Indonesian: 1.234.567,89 -> remove dot, replace comma with dot
    # Standard: 1,234,567.89 -> remove comma
    if "." in s and "," in s:
        last_dot = s.rfind(".")
        last_comma = s.rfind(",")
        if last_comma > last_dot:
            # Indonesian
            cleaned = s.replace(".", "").replace(",", ".")
        else:
            # Standard US
            cleaned = s.replace(",", "")
    elif "," in s:
        # Check if comma is decimal (e.g., '12,5' or '1234,56') or thousands ('1,000')
        parts = s.split(",")
        if len(parts) == 2 and len(parts[1]) <= 4:
            cleaned = parts[0] + "." + parts[1]
        else:
            cleaned = s.replace(",", "")
    elif "." in s:
        parts = s.split(".")
        if len(parts) == 2 and len(parts[1]) <= 2:
            cleaned = s  # Standard decimal
        else:
            cleaned = s.replace(".", "")  # Indonesian thousands separator
    else:
        cleaned = s

    val = float(cleaned)
    return -val if is_negative else val


def parse_indonesian_date(raw_val: Any) -> datetime.date:
    """
    Parse a date string formatted as DD/MM/YYYY, DD-MM-YYYY, or YYYY-MM-DD.
    """
    if isinstance(raw_val, datetime.date):
        return raw_val

    if not isinstance(raw_val, str):
        raise ValueError(f"Cannot parse non-string value as date: {type(raw_val).__name__}")

    s = raw_val.strip()
    formats = [
        "%d/%m/%Y",
        "%d-%m-%Y",
        "%Y-%m-%d",
        "%d/%m/%y",
        "%d-%m-%y",
    ]
    for fmt in formats:
        try:
            return datetime.datetime.strptime(s, fmt).date()
        except ValueError:
            pass

    raise ValueError(f"Date '{raw_val}' does not match any expected date format (DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD)")


class TabularDatasetImporter:
    """
    Validates, parses, and imports tabular data files.
    """

    def __init__(
        self,
        schema: Dict[str, str],
        acl_principals: List[str],
        dataset_name: str,
    ) -> None:
        """
        schema: dict mapping column_name -> type
                supported types: 'text', 'number', 'date', 'integer'
        acl_principals: list of authorized group/user principals
        dataset_name: identifier for this dataset
        """
        self.schema = schema
        self.acl_principals = acl_principals
        self.dataset_name = dataset_name

    def import_csv(self, csv_content: str, delimiter: str = ",") -> Dict[str, Any]:
        """
        Import CSV string content with atomic all-or-nothing guarantee.
        If any row has errors, ImportErrorDetails is raised with all row errors.
        """
        reader = csv.DictReader(io.StringIO(csv_content), delimiter=delimiter)
        if not reader.fieldnames:
            raise ImportErrorDetails([{"row": 0, "column": None, "error": "CSV file contains no headers or is empty"}])

        # Validate headers match schema
        missing_headers = [col for col in self.schema.keys() if col not in reader.fieldnames]
        if missing_headers:
            raise ImportErrorDetails([{
                "row": 0,
                "column": None,
                "error": f"Missing required schema columns: {missing_headers}"
            }])

        parsed_rows: List[Dict[str, Any]] = []
        errors: List[Dict[str, Any]] = []

        for row_idx, raw_row in enumerate(reader, start=1):
            row_dict: Dict[str, Any] = {}
            row_has_error = False

            for col_name, col_type in self.schema.items():
                raw_cell = raw_row.get(col_name, "")
                try:
                    if col_type == "text":
                        row_dict[col_name] = str(raw_cell).strip()
                    elif col_type == "number":
                        row_dict[col_name] = parse_indonesian_number(raw_cell)
                    elif col_type == "integer":
                        row_dict[col_name] = int(parse_indonesian_number(raw_cell))
                    elif col_type == "date":
                        row_dict[col_name] = parse_indonesian_date(raw_cell).isoformat()
                    else:
                        row_dict[col_name] = raw_cell
                except Exception as ex:
                    errors.append({
                        "row": row_idx,
                        "column": col_name,
                        "raw_value": raw_cell,
                        "error": str(ex),
                    })
                    row_has_error = True

            if not row_has_error:
                parsed_rows.append(row_dict)

        # Atomic check: abort completely if any error occurred
        if errors:
            raise ImportErrorDetails(errors)

        return {
            "status": "success",
            "dataset_name": self.dataset_name,
            "total_rows": len(parsed_rows),
            "columns": list(self.schema.keys()),
            "rows": parsed_rows,
            "acl_principals": self.acl_principals,
            "imported_at": datetime.datetime.utcnow().isoformat() + "Z",
        }
