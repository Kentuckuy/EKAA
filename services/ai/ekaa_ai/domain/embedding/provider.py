"""Embedding Provider Interface & Implementations (§20.4, §21.5, DEC-014)."""
import hashlib
import math
from abc import ABC, abstractmethod
from typing import List

class EmbeddingProvider(ABC):
    """Abstract embedding provider interface."""

    @property
    @abstractmethod
    def dimension(self) -> int:
        """Vector dimension."""
        pass

    @property
    @abstractmethod
    def model_name(self) -> str:
        """Name of embedding model."""
        pass

    @abstractmethod
    def embed_texts(self, texts: List[str]) -> List[List[float]]:
        """Compute normalized vector embeddings for a batch of texts."""
        pass

    @abstractmethod
    def embed_query(self, query: str) -> List[float]:
        """Compute normalized vector embedding for a single search query."""
        pass

class MockEmbeddingProvider(EmbeddingProvider):
    """Deterministic, reproducible embedding provider for testing and CI."""

    def __init__(self, dimension: int = 1024):
        self._dim = dimension
        self._model_name = f"mock-embedding-{dimension}d"

    @property
    def dimension(self) -> int:
        return self._dim

    @property
    def model_name(self) -> str:
        return self._model_name

    def _generate_vector(self, text: str) -> List[float]:
        # Hash text to seed reproducible pseudorandom values
        h = hashlib.sha256(text.encode("utf-8")).digest()
        vec: List[float] = []
        for i in range(self._dim):
            byte_val = h[i % len(h)]
            # Map byte (0-255) to float (-1.0 to 1.0)
            val = ((byte_val ^ (i * 31 & 0xFF)) - 128.0) / 128.0
            vec.append(val)

        # L2-normalize vector
        norm = math.sqrt(sum(x * x for x in vec)) or 1.0
        return [x / norm for x in vec]

    def embed_texts(self, texts: List[str]) -> List[List[float]]:
        return [self._generate_vector(t) for t in texts]

    def embed_query(self, query: str) -> List[float]:
        return self._generate_vector(query)
