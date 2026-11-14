from datetime import datetime, timezone
from fastapi import FastAPI, Header, HTTPException, status
from pydantic import BaseModel
from typing import List, Optional, Any, Dict

app = FastAPI(
    title="EKAA AI Internal API",
    version="1.0.0",
    docs_url="/internal/v1/docs",
    openapi_url="/internal/v1/openapi.json"
)

class HealthResponse(BaseModel):
    status: str
    service: str
    timestamp: str

class IngestionRequest(BaseModel):
    document_id: str
    version_id: str
    storage_key: str
    acl_principals: List[str]
    status: str

class AclUpdateRequest(BaseModel):
    acl_principals: List[str]

class StatusUpdateRequest(BaseModel):
    status: str
    is_current: bool

@app.get("/internal/v1/health", response_model=HealthResponse)
async def health_check():
    return HealthResponse(
        status="ok",
        service="ekaa-ai",
        timestamp=datetime.now(timezone.utc).isoformat()
    )

@app.post("/internal/v1/ingestions", status_code=status.HTTP_202_ACCEPTED)
async def dispatch_ingestion(
    payload: IngestionRequest,
    idempotency_key: str = Header(..., alias="Idempotency-Key"),
    correlation_id: Optional[str] = Header(None, alias="X-Correlation-Id")
):
    # Enforce idempotency & register into ai.jobs
    return {
        "status": "queued",
        "document_id": payload.document_id,
        "version_id": payload.version_id,
        "idempotency_key": idempotency_key
    }

@app.put("/internal/v1/documents/{document_id}/acl")
async def update_document_acl(document_id: str, payload: AclUpdateRequest):
    return {
        "status": "updated",
        "document_id": document_id,
        "acl_principals": payload.acl_principals
    }

@app.put("/internal/v1/documents/{document_id}/status")
async def update_document_status(document_id: str, payload: StatusUpdateRequest):
    return {
        "status": "updated",
        "document_id": document_id,
        "new_status": payload.status,
        "is_current": payload.is_current
    }

@app.post("/internal/v1/ask")
async def ask_question(payload: Dict[str, Any]):
    principals = payload.get("access", {}).get("principals", [])
    # Invariant 5: If principals is empty, return 0 results
    if not principals:
        return {
            "route": "document",
            "route_confidence": 1.0,
            "status": "no_evidence",
            "answer": "Bukti tidak ditemukan pada dokumen yang Anda miliki aksesnya.",
            "citations": [],
            "trace_id": "tr_empty_access"
        }
    return {
        "route": "document",
        "route_confidence": 0.95,
        "status": "no_evidence",
        "answer": "Sistem EKAA AI Service dalam mode persiapan (M0). Ingestion dan indeks aktif pada M3.",
        "citations": [],
        "trace_id": "tr_m0_stub"
    }
