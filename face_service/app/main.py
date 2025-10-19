from __future__ import annotations

from pathlib import Path

from fastapi import FastAPI, HTTPException

from .schemas import (
    HealthResponse,
    TrainingRequest,
    TrainingResult,
    VerificationRequest,
    VerificationResponse,
)
from .services.recognition import FaceRecognitionService
from .storage import ModelStorage

app = FastAPI(title="Refape Face Recognition API", version="1.0.0")

storage = ModelStorage(base_dir=Path(__file__).resolve().parent.parent / "models")
service = FaceRecognitionService(storage=storage)


@app.get("/health", response_model=HealthResponse)
async def health() -> HealthResponse:
    return HealthResponse()


@app.post("/train", response_model=TrainingResult)
async def train(request: TrainingRequest) -> TrainingResult:
    try:
        return service.train(request)
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc


@app.post("/verify", response_model=VerificationResponse)
async def verify(request: VerificationRequest) -> VerificationResponse:
    return service.verify(request)
