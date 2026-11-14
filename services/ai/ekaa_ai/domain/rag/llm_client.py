"""LLM Client Interface & Implementations (§12.2, §21.2, DEC-013)."""
import hashlib
from abc import ABC, abstractmethod
from typing import List, Dict, Any, Optional

class LLMClient(ABC):
    """Abstract LLM Client."""

    @property
    @abstractmethod
    def model_name(self) -> str:
        pass

    @abstractmethod
    def generate_answer(
        self,
        system_instruction: str,
        user_prompt: str,
        temperature: float = 0.0
    ) -> Dict[str, Any]:
        """Generate structured completion."""
        pass

class MockLLMClient(LLMClient):
    """Deterministic Mock LLM for reproducible testing and CI."""

    def __init__(self, model_name: str = "mock-llm-v1"):
        self._model_name = model_name

    @property
    def model_name(self) -> str:
        return self._model_name

    def generate_answer(
        self,
        system_instruction: str,
        user_prompt: str,
        temperature: float = 0.0
    ) -> Dict[str, Any]:
        prompt_hash = hashlib.sha256((system_instruction + user_prompt).encode("utf-8")).hexdigest()[:16]

        return {
            "model": self._model_name,
            "prompt_version": f"sha256-{prompt_hash}",
            "temperature": temperature,
            "answer_text": "Berdasarkan dokumen yang diperiksa, informasi yang relevan telah ditemukan.",
            "is_sufficient": True
        }
