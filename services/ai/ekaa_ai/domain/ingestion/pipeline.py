"""Ingestion Pipeline (§20.1, §20.4, AC-M3-01 s.d. AC-M3-04).
Coordinates parsing, structural chunking, embedding generation, and atomic version updates.
"""
from typing import List, Dict, Any, Optional
from .parsers.pdf_parser import PdfParser
from .parsers.docx_parser import DocxParser
from .parsers.tabular_text_parser import TabularTextParser
from .chunker import StructureChunker, TextChunk
from ..embedding.provider import EmbeddingProvider

class IngestionPipeline:
    """End-to-end ingestion pipeline."""

    def __init__(self, embedding_provider: EmbeddingProvider, target_tokens: int = 350):
        self.embedding_provider = embedding_provider
        self.chunker = StructureChunker(target_tokens=target_tokens)
        self.pdf_parser = PdfParser()
        self.docx_parser = DocxParser()
        self.tabular_parser = TabularTextParser()

    def process_document(
        self,
        file_content: bytes,
        filename: str,
        document_id: str,
        version_id: str,
        acl_principals: List[str],
        status: str = "active",
        is_current: bool = True
    ) -> Dict[str, Any]:
        ext = filename.split(".")[-1].lower()

        # 1. Parse document based on format
        if ext == "pdf":
            parsed = self.pdf_parser.parse(file_content, filename)
        elif ext == "docx":
            parsed = self.docx_parser.parse(file_content, filename)
        elif ext in ["xlsx", "csv"]:
            parsed = self.tabular_parser.parse(file_content, filename)
        else:
            raise ValueError(f"Unsupported file format: {ext}")

        # 2. Structural chunking
        chunks = self.chunker.chunk_document(parsed, document_id, version_id)

        # 3. Batch embedding generation
        chunk_texts = [c.content for c in chunks]
        embeddings = self.embedding_provider.embed_texts(chunk_texts) if chunk_texts else []

        # 4. Assemble database chunk records
        records = []
        for chunk, emb in zip(chunks, embeddings):
            records.append({
                "id": chunk.id,
                "document_id": chunk.document_id,
                "version_id": chunk.version_id,
                "chunk_index": chunk.chunk_index,
                "content": chunk.content,
                "embedding": emb,
                "token_count": chunk.token_count,
                "page_start": chunk.page_start,
                "page_end": chunk.page_end,
                "section_path": chunk.section_path,
                "acl_principals": acl_principals,
                "status": status,
                "is_current": is_current,
            })

        return {
            "document_id": document_id,
            "version_id": version_id,
            "chunk_count": len(records),
            "chunks": records,
            "needs_ocr": parsed.needs_ocr,
            "embedding_model": self.embedding_provider.model_name,
            "dimension": self.embedding_provider.dimension,
        }
