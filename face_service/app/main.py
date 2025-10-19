from __future__ import annotations

import os
from datetime import datetime
from typing import List, Optional

from fastapi import Depends, FastAPI, HTTPException, Query
from fastapi.middleware.cors import CORSMiddleware
from sqlmodel import Session

from .schemas import (
    DatasetIngestRequest,
    DatasetIngestResult,
    DatasetSummary,
    HealthResponse,
    ModelSummary,
    AttendanceListResponse,
    TrainingRequest,
    TrainingResult,
    VerificationRequest,
    VerificationResponse,
)
from .services.recognition import FaceRecognitionService
from .storage import ModelStorage
from .database import DATA_DIR, get_session, init_db

app = FastAPI(title="Refape Face Recognition API", version="1.0.0")

allowed_origins = [origin.strip() for origin in os.getenv("FACE_API_ALLOWED_ORIGINS", "*").split(",") if origin.strip()]
if allowed_origins == ["*"]:
    cors_origins = ["*"]
else:
    cors_origins = allowed_origins

app.add_middleware(
    CORSMiddleware,
    allow_origins=cors_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

MODELS_DIR = DATA_DIR / "models"
DATASETS_DIR = DATA_DIR / "datasets"

storage = ModelStorage(base_dir=MODELS_DIR)
service = FaceRecognitionService(storage=storage, dataset_dir=DATASETS_DIR)


@app.on_event("startup")
def on_startup() -> None:
    init_db()


@app.get("/health", response_model=HealthResponse)
async def health() -> HealthResponse:
    return HealthResponse()


@app.post("/datasets", response_model=DatasetIngestResult)
async def ingest_dataset(
    request: DatasetIngestRequest,
    session: Session = Depends(get_session),
) -> DatasetIngestResult:
    return service.ingest_samples(session, request.company_id, request.samples)


@app.get("/companies/{company_id:path}/dataset", response_model=DatasetSummary)
async def get_dataset_summary(
    company_id: str,
    session: Session = Depends(get_session),
) -> DatasetSummary:
    return service.dataset_summary(session, company_id)


@app.post("/train", response_model=TrainingResult)
async def train(
    request: TrainingRequest,
    session: Session = Depends(get_session),
) -> TrainingResult:
    try:
        return service.train(session, request)
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc


@app.get("/companies/{company_id:path}/models", response_model=List[ModelSummary])
async def list_models(
    company_id: str,
    session: Session = Depends(get_session),
) -> List[ModelSummary]:
    artifacts = service.list_models(session, company_id)
    return [ModelSummary.from_artifact(artifact) for artifact in artifacts]


@app.get("/companies/{company_id:path}/models/latest", response_model=ModelSummary)
async def latest_model(
    company_id: str,
    session: Session = Depends(get_session),
) -> ModelSummary:
    artifact = service.latest_model(session, company_id)
    if artifact is None:
        raise HTTPException(status_code=404, detail="Nenhum modelo treinado para a empresa informada.")
    return ModelSummary.from_artifact(artifact)


@app.post("/verify", response_model=VerificationResponse)
async def verify(
    request: VerificationRequest,
    session: Session = Depends(get_session),
) -> VerificationResponse:
    return service.verify(session, request)


@app.get("/companies/{company_id:path}/attendance", response_model=AttendanceListResponse)
async def attendance_history(
    company_id: str,
    session: Session = Depends(get_session),
    start: Optional[datetime] = Query(None, description="Filtro de data mínima (ISO 8601)"),
    end: Optional[datetime] = Query(None, description="Filtro de data máxima (ISO 8601)"),
    employee_id: Optional[str] = Query(None, description="Filtrar por colaborador"),
    limit: int = Query(100, ge=1, le=500),
    offset: int = Query(0, ge=0),
) -> AttendanceListResponse:
    return service.list_attendance(session, company_id, start=start, end=end, employee_id=employee_id, limit=limit, offset=offset)
