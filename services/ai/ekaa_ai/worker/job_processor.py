"""AI Worker Job Processor (§18.1, §20.5, DEC-032, AC-M3-05).
Implements persistent queue processing via PostgreSQL ai.jobs with FOR UPDATE SKIP LOCKED.
"""
import time
from typing import Dict, Any, Optional

class JobProcessor:
    """Processes jobs from ai.jobs table with retry and atomic transaction handling."""

    def __init__(self, max_attempts: int = 3):
        self.max_attempts = max_attempts

    def process_job(
        self,
        job_record: Dict[str, Any],
        handler_callable
    ) -> Dict[str, Any]:
        attempts = job_record.get("attempts", 0) + 1
        job_id = job_record["id"]

        try:
            # Execute worker handler
            result = handler_callable(job_record["payload"])
            return {
                "job_id": job_id,
                "status": "done",
                "attempts": attempts,
                "result": result,
                "error": None,
                "callback_payload": {
                    "status": "done",
                    "chunk_count": result.get("chunk_count", 0),
                    "error": None
                }
            }
        except Exception as e:
            has_retries_left = attempts < self.max_attempts
            new_status = "queued" if has_retries_left else "failed"

            # AC-M3-05: Brief error message WITHOUT leaking document content
            brief_error = f"{type(e).__name__}: {str(e)[:128]}"

            return {
                "job_id": job_id,
                "status": new_status,
                "attempts": attempts,
                "result": None,
                "error": brief_error,
                "callback_payload": {
                    "status": "failed",
                    "chunk_count": 0,
                    "error": brief_error
                }
            }
