"""Tabular-as-Text Parser (§20.2, AC-M3-01).
Parses CSV and XLSX as searchable text documents with repeated headers per chunk.
"""
import io
import csv
import openpyxl
from typing import List
from .base import BaseParser, ParsedDocument, PageContent

class TabularTextParser(BaseParser):
    """Indexes spreadsheets as text documents, repeating column headers on chunks."""

    def parse(self, file_content: bytes, filename: str) -> ParsedDocument:
        ext = filename.split(".")[-1].lower()
        if ext == "csv":
            return self._parse_csv(file_content, filename)
        elif ext == "xlsx":
            return self._parse_xlsx(file_content, filename)
        raise ValueError(f"Unsupported tabular extension: {ext}")

    def _parse_csv(self, file_content: bytes, filename: str) -> ParsedDocument:
        text_stream = io.StringIO(file_content.decode("utf-8", errors="replace"))
        reader = csv.reader(text_stream)
        rows = list(reader)

        if not rows:
            return ParsedDocument(filename=filename, pages=[], needs_ocr=False)

        headers = rows[0]
        text_blocks: List[str] = [f"Kolom: {', '.join(headers)}"]

        for row_idx, row in enumerate(rows[1:], start=1):
            row_items = [f"{h}: {val}" for h, val in zip(headers, row) if val.strip()]
            text_blocks.append(f"Baris {row_idx} -> " + " | ".join(row_items))

        combined = "\n".join(text_blocks)
        return ParsedDocument(
            filename=filename,
            pages=[PageContent(page_number=1, text=combined, has_text=True)],
            total_characters=len(combined)
        )

    def _parse_xlsx(self, file_content: bytes, filename: str) -> ParsedDocument:
        stream = io.BytesIO(file_content)
        wb = openpyxl.load_workbook(stream, read_only=True, data_only=True)
        text_blocks: List[str] = []

        for sheetname in wb.sheetnames:
            ws = wb[sheetname]
            rows = list(ws.iter_rows(values_only=True))
            if not rows:
                continue

            headers = [str(cell) if cell is not None else "" for cell in rows[0]]
            text_blocks.append(f"\n=== Sheet: {sheetname} ===\nKolom: {', '.join(headers)}")

            for row_idx, row in enumerate(rows[1:], start=1):
                row_items = [
                    f"{h}: {val}" for h, val in zip(headers, row)
                    if val is not None and str(val).strip()
                ]
                if row_items:
                    text_blocks.append(f"Baris {row_idx} -> " + " | ".join(row_items))

        combined = "\n".join(text_blocks)
        return ParsedDocument(
            filename=filename,
            pages=[PageContent(page_number=1, text=combined, has_text=True)],
            total_characters=len(combined)
        )
