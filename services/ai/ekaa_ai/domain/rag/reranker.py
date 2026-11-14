"""Cross-Encoder Reranker Interface & Implementations (§21.1, §21.5, DEC-014).
Scores top 20-50 retrieval candidates for semantic relevance.
"""
from abc import ABC, abstractmethod
from typing import List, Dict, Any, Tuple

class BaseReranker(ABC):
    """Abstract Reranker."""

    @abstractmethod
    def rerank(
        self,
        query: str,
        candidates: List[Dict[str, Any]],
        top_n: int = 8
    ) -> List[Dict[str, Any]]:
        """Rerank candidates and return top_n items with rerank_score."""
        pass

class MockReranker(BaseReranker):
    """Deterministic Mock Reranker for tests and CI."""

    def __init__(self, model_name: str = "bge-reranker-v2-m3"):
        self.model_name = model_name

    def rerank(
        self,
        query: str,
        candidates: List[Dict[str, Any]],
        top_n: int = 8
    ) -> List[Dict[str, Any]]:
        if not candidates:
            return []

        # Deterministic scoring based on term overlap and rrf score
        query_words = set(query.lower().split())
        scored: List[Tuple[float, Dict[str, Any]]] = []

        for c in candidates:
            content_words = set(c["content"].lower().split())
            overlap = len(query_words.intersection(content_words))
            base_score = c.get("rrf_score", 0.0) * 10
            relevance = min(1.0, 0.2 + (overlap * 0.15) + base_score)

            item = dict(c)
            item["rerank_score"] = round(relevance, 4)
            scored.append((relevance, item))

        scored.sort(key=lambda x: x[0], reverse=True)
        return [item for _, item in scored[:top_n]]
