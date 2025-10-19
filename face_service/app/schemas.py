from __future__ import annotations

from typing import List, Optional

from pydantic import BaseModel, Field


class TrainingSample(BaseModel):
    employee_id: str = Field(..., min_length=1)
    images: List[str] = Field(..., min_items=1, description="Lista de imagens em base64 do colaborador")


class TrainingRequest(BaseModel):
    company_id: str = Field(..., min_length=1)
    samples: List[TrainingSample] = Field(..., min_items=1)


class TrainingResult(BaseModel):
    company_id: str
    trained_employees: int
    total_images: int


class VerificationRequest(BaseModel):
    company_id: str = Field(..., min_length=1)
    image: str = Field(..., description="Imagem em base64 para reconhecimento")
    threshold: Optional[float] = Field(0.45, ge=0.0, le=1.0)


class Prediction(BaseModel):
    employee_id: str
    distance: float
    score: float


class VerificationResponse(BaseModel):
    matched: bool
    prediction: Optional[Prediction]


class HealthResponse(BaseModel):
    status: str = "ok"
