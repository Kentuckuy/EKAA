"""PDF Document Parser (§20.2, DEC-039, AC-M3-01, AC-M3-09).
Uses pypdf / pdfminer.six. Strictly excludes PyMuPDF (AGPL).
"""
import io
import re
from typing import List
from pypdf import PdfReader
from .base import BaseParser, ParsedDocument, PageContent

class PdfParser(BaseParser):
    """Text-based PDF parser with empty page OCR detection."""

    def parse(self, file_content: bytes, filename: str) -> ParsedDocument:
        stream = io.BytesIO(file_content)
        reader = PdfReader(stream)

        pages: List[PageContent] = []
        has_any_ocr_needed = False
        total_chars = 0

        for page_idx, page in enumerate(reader.pages, start=1):
            raw_text = page.extract_text() or ""
            cleaned_text = self._clean_page_text(raw_text)

            # Detect scanned image / empty text layer
            is_empty = len(cleaned_text.strip()) < 10
            needs_ocr = is_empty

            if needs_ocr:
                has_any_ocr_needed = True

            pages.append(PageContent(
                page_number=page_idx,
                text=cleaned_text if not needs_ocr else "",
                has_text=not is_empty,
                needs_ocr=needs_ocr
            ))

            if not needs_ocr:
                total_chars += len(cleaned_text)

        return ParsedDocument(
            filename=filename,
            pages=pages,
            needs_ocr=has_any_ocr_needed,
            total_characters=total_chars,
            metadata={"total_pages": len(reader.pages)}
        )

    def _clean_page_text(self, text: str) -> str:
        """Normalize whitespace and fix hyphenated line breaks."""
        if not text:
            return ""
        # Fix hyphenated Indonesian words across line ends (e.g., peratur- \n an -> peraturan)
        text = re.sub(r'(\w+)-\s*\n\s*(\w+)', r'\1\2', text)
        # Normalize multiple spaces and tabs to single space
        lines = [line.strip() for line in text.splitlines() if line.strip()]
        return "\n".join(lines)
