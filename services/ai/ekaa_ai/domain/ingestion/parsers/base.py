"""Base parser interfaces for document ingestion (§20.1, §20.2)."""
from abc import ABC, abstractmethod
from dataclasses import dataclass, field
from typing import List, Dict, Any, Optional

@dataclass
class PageContent:
    page_number: int
    text: str
    has_text: bool = True
    needs_ocr: bool = False
    section_hints: List[str] = field(default_factory=list)

@dataclass
class ParsedDocument:
    filename: str
    pages: List[PageContent]
    metadata: Dict[str, Any] = field(default_factory=dict)
    needs_ocr: bool = False
    total_characters: int = 0

class BaseParser(ABC):
    """Abstract base class for document format parsers."""

    @abstractmethod
    def parse(self, file_content: bytes, filename: str) -> ParsedDocument:
        """Parse raw file bytes into structured ParsedDocument."""
        pass
