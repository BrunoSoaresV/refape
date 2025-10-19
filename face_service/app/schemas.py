from __future__ import annotations

from datetime import datetime
from typing import TYPE_CHECKING, List, Optional

from pydantic import BaseModel, Field, ConfigDict


class TrainingSample(BaseModel):
    employee_id: str = Field(..., min_length=1)
    images: List[str] = Field(..., min_items=1, description="Lista de imagens em base64 do colaborador")


class TrainingRequest(BaseModel):
    company_id: str = Field(..., min_length=1)
    samples: List[TrainingSample] = Field(default_factory=list)


class DatasetIngestRequest(TrainingRequest):
    samples: List[TrainingSample] = Field(..., min_items=1)


class DatasetEmployeeSummary(BaseModel):
    employee_id: str
    images: int


class DatasetSummary(BaseModel):
    company_id: str
    employees: List[DatasetEmployeeSummary]
    total_images: int


class DatasetIngestResult(BaseModel):
    company_id: str
    new_images: int
    total_images: int
    employees: List[DatasetEmployeeSummary]

    def dataset_summary(self) -> DatasetSummary:
        return DatasetSummary(
            company_id=self.company_id,
            employees=self.employees,
            total_images=self.total_images,
        )


class TrainingMetrics(BaseModel):
    total_images: int
    valid_embeddings: int
    rejected_images: int
    trained_employees: int


class TrainingResult(BaseModel):
    model_config = ConfigDict(protected_namespaces=())
    company_id: str
    model_version: int
    metrics: TrainingMetrics
    dataset: DatasetSummary


if TYPE_CHECKING:
    from .models_db import ModelArtifact


class ModelSummary(BaseModel):
    model_config = ConfigDict(protected_namespaces=())
    model_version: int
    created_at: datetime
    metrics: TrainingMetrics

    @classmethod
    def from_artifact(cls, artifact: "ModelArtifact") -> "ModelSummary":
        # Import locally to avoid circular dependency during module import.
        from .models_db import ModelArtifact

        metrics = TrainingMetrics.model_validate_json(artifact.metrics)
        return cls(
            model_version=artifact.version,
            created_at=artifact.created_at,
            metrics=metrics,
        )


class VerificationRequest(BaseModel):
    model_config = ConfigDict(protected_namespaces=())
    company_id: str = Field(..., min_length=1)
    image: str = Field(..., description="Imagem em base64 para reconhecimento")
    threshold: Optional[float] = Field(0.45, ge=0.0, le=1.0)
    model_version: Optional[int] = Field(None, ge=1)


class Prediction(BaseModel):
    employee_id: str
    distance: float
    score: float


class FaceBox(BaseModel):
    top: float
    left: float
    width: float
    height: float


class VerificationResponse(BaseModel):
    model_config = ConfigDict(protected_namespaces=())
    matched: bool
    prediction: Optional[Prediction]
    model_version: Optional[int]
    event_id: Optional[int]
    event_type: Optional[str] = None
    face_box: Optional[FaceBox] = None
    cooldown_seconds: Optional[int] = None


class AttendanceRecord(BaseModel):
    model_config = ConfigDict(protected_namespaces=())
    id: int
    company_id: str
    employee_id: Optional[str]
    matched: bool
    distance: Optional[float]
    score: Optional[float]
    threshold: float
    model_version: Optional[int]
    event_type: Optional[str]
    recorded_at: datetime


class AttendanceListResponse(BaseModel):
    model_config = ConfigDict(protected_namespaces=())
    company_id: str
    total: int
    items: List[AttendanceRecord]


class HealthResponse(BaseModel):
    status: str = "ok"
