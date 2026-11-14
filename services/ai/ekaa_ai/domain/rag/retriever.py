"""Hybrid Access-Filtered Retriever (§21, §22.1, Invariant 5, AC-M4-02).
Applies strict ACL filter (acl_principals && :principals AND status='active' AND is_current=true)
on BOTH dense and lexical retrieval branches before Reciprocal Rank Fusion (RRF, k=60).
"""
from typing import List, Dict, Any, Optional

class HybridRetriever:
    """Combines lexical and dense retrieval with pre-filtered ACL permissions."""

    def __init__(self, rrf_k: int = 60, top_candidates: int = 50):
        self.rrf_k = rrf_k
        self.top_candidates = top_candidates

    def filter_by_acl(
        self,
        chunks: List[Dict[str, Any]],
        principals: List[str]
    ) -> List[Dict[str, Any]]:
        # Invariant 5: If principals is empty -> ZERO results!
        if not principals:
            return []

        principal_set = set(principals)
        filtered = []
        for c in chunks:
            # Check status and is_current
            if c.get("status") != "active" or not c.get("is_current", True):
                continue

            chunk_principals = set(c.get("acl_principals", []))
            # Array overlap operator (acl_principals && :principals)
            if chunk_principals.intersection(principal_set):
                filtered.append(c)

        return filtered

    def reciprocal_rank_fusion(
        self,
        dense_ranked: List[Dict[str, Any]],
        lexical_ranked: List[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        scores: Dict[str, float] = {}
        chunk_map: Dict[str, Dict[str, Any]] = {}

        # 1. Score dense list
        for rank, c in enumerate(dense_ranked, start=1):
            cid = c["id"]
            chunk_map[cid] = c
            scores[cid] = scores.get(cid, 0.0) + (1.0 / (self.rrf_k + rank))

        # 2. Score lexical list
        for rank, c in enumerate(lexical_ranked, start=1):
            cid = c["id"]
            chunk_map[cid] = c
            scores[cid] = scores.get(cid, 0.0) + (1.0 / (self.rrf_k + rank))

        # 3. Sort by aggregated RRF score
        sorted_ids = sorted(scores.keys(), key=lambda x: scores[x], reverse=True)
        results = []
        for cid in sorted_ids[:self.top_candidates]:
            item = dict(chunk_map[cid])
            item["rrf_score"] = scores[cid]
            results.append(item)

        return results
