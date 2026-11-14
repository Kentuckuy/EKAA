"""DOCX Document Parser (§20.2, AC-M3-01).
Uses python-docx to extract text, heading styles, and tables.
"""
import io
import docx
from typing import List
from .base import BaseParser, ParsedDocument, PageContent

class DocxParser(BaseParser):
    """Parses Microsoft Word (.docx) documents preserving heading structure."""

    def parse(self, file_content: bytes, filename: str) -> ParsedDocument:
        stream = io.BytesIO(file_content)
        doc = docx.Document(stream)

        text_blocks: List[str] = []

        # 1. Paragraphs with heading hierarchy
        for para in doc.paragraphs:
            text = para.text.strip()
            if not text:
                continue
            if para.style and para.style.name and para.style.name.startswith("Heading"):
                text_blocks.append(f"\n## {text}\n")
            else:
                text_blocks.append(text)

        # 2. Tables extracted as structured rows with headers
        for table in doc.tables:
            header_cells = [cell.text.strip() for cell in table.rows[0].cells]
            for row in table.rows[1:]:
                row_cells = [cell.text.strip() for cell in row.cells]
                row_str = " | ".join(f"{h}: {c}" for h, c in zip(header_cells, row_cells) if c)
                if row_str:
                    text_blocks.append(f"[Tabel] {row_str}")

        combined_text = "\n\n".join(text_blocks)

        # Word docs do not have native pagination in the raw XML; treated as single sequential flow
        pages = [
            PageContent(
                page_number=1,
                text=combined_text,
                has_text=bool(combined_text),
                needs_ocr=False
            )
        ]

        return ParsedDocument(
            filename=filename,
            pages=pages,
            needs_ocr=False,
            total_characters=len(combined_text)
        )
