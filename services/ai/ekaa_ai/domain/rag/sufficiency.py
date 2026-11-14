"""Sufficiency Check & Abstain Logic (§21.3, AC-M4-04).
Enforces principled abstain (returning status='no_evidence') rather than hallucinating answers.
"""
from typing import List, Dict, Any

class SufficiencyChecker:
    """Evaluates whether retrieved evidence is strong enough to answer."""

    def __init__(self, confidence_threshold: float = 0.45):
        self.confidence_threshold = confidence_threshold

    def evaluate_evidence(
        self,
        query: str,
        reranked_chunks: List[Dict[str, Any]]
    ) -> Dict[str, Any]:
        # Condition a: Retrieval is completely empty
        if not reranked_chunks:
            return {
                "sufficient": False,
                "reason": "EMPTY_RETRIEVAL",
                "top_score": 0.0
            }

        top_score = reranked_chunks[0].get("rerank_score", 0.0)

        # Condition b: Top reranker score below threshold
        if top_score < self.confidence_threshold:
            return {
                "sufficient": False,
                "reason": "LOW_RELEVANCE_SCORE",
                "top_score": top_score
            }

        return {
            "sufficient": True,
            "reason": "SUFFICIENT_EVIDENCE",
            "top_score": top_score
        }
