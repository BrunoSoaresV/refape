from __future__ import annotations

import base64
import io
import json
from datetime import datetime, timedelta
from pathlib import Path
from typing import Dict, List, Optional, Tuple
from uuid import uuid4

import face_recognition
import numpy as np
from PIL import Image
from sqlmodel import Session, func, select

from ..models_db import AttendanceEvent, Company, Employee, ModelArtifact, Sample
from ..schemas import (
    DatasetEmployeeSummary,
    DatasetIngestResult,
    DatasetSummary,
    FaceBox,
    Prediction,
    TrainingMetrics,
    TrainingRequest,
    TrainingResult,
    TrainingSample,
    VerificationRequest,
    VerificationResponse,
    AttendanceListResponse,
    AttendanceRecord,
)
from ..storage import ModelStorage


class FaceRecognitionService:
    COOLDOWN_PERIOD = timedelta(minutes=5)

    def __init__(self, storage: ModelStorage, dataset_dir: Path) -> None:
        self.storage = storage
        self.dataset_dir = dataset_dir
        self.dataset_dir.mkdir(parents=True, exist_ok=True)

    # ------------------------------------------------------------------
    # Dataset management helpers
    # ------------------------------------------------------------------
    def _decode_image(self, encoded_image: str) -> Image.Image:
        decoded = base64.b64decode(encoded_image)
        image = Image.open(io.BytesIO(decoded)).convert("RGB")
        return image

    def _save_image(self, company_id: str, employee_id: str, image: Image.Image) -> Path:
        employee_dir = self.dataset_dir / company_id / employee_id
        employee_dir.mkdir(parents=True, exist_ok=True)
        filename = f"{uuid4().hex}.jpg"
        path = employee_dir / filename
        image.save(path, format="JPEG", quality=95)
        return path

    def _ensure_company_employee(self, session: Session, company_id: str, employee_id: str) -> None:
        if session.get(Company, company_id) is None:
            session.add(Company(id=company_id))

        employee = session.exec(
            select(Employee).where(Employee.company_id == company_id, Employee.id == employee_id)
        ).first()
        if employee is None:
            session.add(Employee(id=employee_id, company_id=company_id))

    def ingest_samples(
        self, session: Session, company_id: str, samples: List[TrainingSample]
    ) -> DatasetIngestResult:
        if not samples:
            employees_summary = self._dataset_summary(session, company_id).employees
            totals = sum(item.images for item in employees_summary)
            return DatasetIngestResult(
                company_id=company_id,
                new_images=0,
                total_images=totals,
                employees=employees_summary,
            )

        new_images = 0
        for sample in samples:
            self._ensure_company_employee(session, company_id, sample.employee_id)
            for encoded_image in sample.images:
                image = self._decode_image(encoded_image)
                path = self._save_image(company_id, sample.employee_id, image)
                session.add(
                    Sample(
                        company_id=company_id,
                        employee_id=sample.employee_id,
                        file_path=str(path),
                    )
                )
                new_images += 1

        session.commit()
        summary = self._dataset_summary(session, company_id)
        return DatasetIngestResult(
            company_id=company_id,
            new_images=new_images,
            total_images=summary.total_images,
            employees=summary.employees,
        )

    def _dataset_summary(self, session: Session, company_id: str) -> DatasetSummary:
        employees: List[DatasetEmployeeSummary] = []
        total_images = 0

        rows = session.exec(
            select(Sample.employee_id, func.count(Sample.id))
            .where(Sample.company_id == company_id)
            .group_by(Sample.employee_id)
            .order_by(Sample.employee_id)
        ).all()

        for employee_id, count in rows:
            employees.append(DatasetEmployeeSummary(employee_id=employee_id, images=count))
            total_images += count

        return DatasetSummary(company_id=company_id, employees=employees, total_images=total_images)

    # ------------------------------------------------------------------
    # Training & inference
    # ------------------------------------------------------------------
    def _compute_embedding(
        self, image: np.ndarray
    ) -> Tuple[bool, np.ndarray | None, Tuple[int, int, int, int] | None]:
        locations = face_recognition.face_locations(image, model="hog")
        if not locations:
            return False, None, None
        encodings = face_recognition.face_encodings(image, known_face_locations=locations)
        if not encodings:
            return False, None, None
        return True, encodings[0], locations[0]

    def _load_training_images(self, session: Session, company_id: str) -> Dict[str, List[np.ndarray]]:
        samples = session.exec(select(Sample).where(Sample.company_id == company_id)).all()
        grouped: Dict[str, List[np.ndarray]] = {}
        for sample in samples:
            path = Path(sample.file_path)
            if not path.exists():
                continue
            image = face_recognition.load_image_file(path)
            grouped.setdefault(sample.employee_id, []).append(image)
        return grouped

    def _next_model_version(self, session: Session, company_id: str) -> int:
        latest = session.exec(
            select(func.max(ModelArtifact.version)).where(ModelArtifact.company_id == company_id)
        ).one()
        current = latest[0] if isinstance(latest, tuple) else latest
        if current is None:
            return 1
        return int(current) + 1

    def train(self, session: Session, request: TrainingRequest) -> TrainingResult:
        ingest_result = self.ingest_samples(session, request.company_id, request.samples)

        images_by_employee = self._load_training_images(session, request.company_id)
        embeddings: Dict[str, List[np.ndarray]] = {}
        valid_embeddings = 0
        for employee_id, images in images_by_employee.items():
            embeddings.setdefault(employee_id, [])
            for image in images:
                success, embedding, _ = self._compute_embedding(image)
                if success and embedding is not None:
                    embeddings[employee_id].append(embedding)
                    valid_embeddings += 1

        filtered_embeddings = {k: v for k, v in embeddings.items() if v}
        if not filtered_embeddings:
            raise ValueError("Não foi possível extrair embeddings válidos das imagens cadastradas.")

        version = self._next_model_version(session, request.company_id)
        self.storage.save(request.company_id, version, filtered_embeddings)
        self.storage.save_metadata(
            request.company_id,
            version,
            {employee_id: len(v) for employee_id, v in filtered_embeddings.items()},
        )

        metrics = TrainingMetrics(
            total_images=ingest_result.total_images,
            valid_embeddings=valid_embeddings,
            rejected_images=max(ingest_result.total_images - valid_embeddings, 0),
            trained_employees=len(filtered_embeddings),
        )

        artifact = ModelArtifact(
            company_id=request.company_id,
            version=version,
            embeddings_path=str(self.storage.embeddings_path(request.company_id, version)),
            metadata_path=str(self.storage.metadata_path(request.company_id, version)),
            metrics=metrics.model_dump_json(),
            created_at=datetime.utcnow(),
        )
        session.add(artifact)
        session.commit()

        return TrainingResult(
            company_id=request.company_id,
            model_version=version,
            metrics=metrics,
            dataset=ingest_result.dataset_summary(),
        )

    def list_models(self, session: Session, company_id: str) -> List[ModelArtifact]:
        return session.exec(
            select(ModelArtifact)
            .where(ModelArtifact.company_id == company_id)
            .order_by(ModelArtifact.version.desc())
        ).all()

    def latest_model(self, session: Session, company_id: str) -> ModelArtifact | None:
        return session.exec(
            select(ModelArtifact)
            .where(ModelArtifact.company_id == company_id)
            .order_by(ModelArtifact.version.desc())
            .limit(1)
        ).one_or_none()

    def dataset_summary(self, session: Session, company_id: str) -> DatasetSummary:
        return self._dataset_summary(session, company_id)

    def verify(self, session: Session, request: VerificationRequest) -> VerificationResponse:
        artifact: ModelArtifact | None
        if request.model_version is not None:
            artifact = session.exec(
                select(ModelArtifact)
                .where(
                    ModelArtifact.company_id == request.company_id,
                    ModelArtifact.version == request.model_version,
                )
            ).one_or_none()
        else:
            artifact = self.latest_model(session, request.company_id)

        if artifact is None:
            event = AttendanceEvent(
                company_id=request.company_id,
                employee_id=None,
                matched=False,
                distance=None,
                score=None,
                threshold=request.threshold,
                model_version=None,
            )
            session.add(event)
            session.commit()
            session.refresh(event)
            return VerificationResponse(matched=False, prediction=None, model_version=None, event_id=event.id)

        embeddings = self.storage.load(request.company_id, artifact.version)
        if not embeddings:
            event = AttendanceEvent(
                company_id=request.company_id,
                employee_id=None,
                matched=False,
                distance=None,
                score=None,
                threshold=request.threshold,
                model_version=artifact.version,
            )
            session.add(event)
            session.commit()
            session.refresh(event)
            return VerificationResponse(
                matched=False,
                prediction=None,
                model_version=artifact.version,
                event_id=event.id,
            )

        image = self._decode_image(request.image)
        np_image = np.array(image)
        success, target_embedding, face_location = self._compute_embedding(np_image)
        if not success or target_embedding is None:
            event = AttendanceEvent(
                company_id=request.company_id,
                employee_id=None,
                matched=False,
                distance=None,
                score=None,
                threshold=request.threshold,
                model_version=artifact.version,
            )
            session.add(event)
            session.commit()
            session.refresh(event)
            return VerificationResponse(
                matched=False,
                prediction=None,
                model_version=artifact.version,
                event_id=event.id,
                face_box=None,
            )

        best_match: Prediction | None = None
        lowest_distance = float("inf")

        for employee_id, known_embeddings in embeddings.items():
            distances = face_recognition.face_distance(known_embeddings, target_embedding)
            distance = float(distances.min())
            if distance < lowest_distance:
                lowest_distance = distance
                best_match = Prediction(
                    employee_id=employee_id,
                    distance=distance,
                    score=max(0.0, 1.0 - distance / max(request.threshold, 1e-6)),
                )

        face_box: FaceBox | None = None
        if face_location is not None:
            top, right, bottom, left = face_location
            width, height = image.size
            if width > 0 and height > 0:
                face_box = FaceBox(
                    top=float(top / height),
                    left=float(left / width),
                    width=float(max(right - left, 0) / width),
                    height=float(max(bottom - top, 0) / height),
                )

        matched = best_match is not None and lowest_distance <= request.threshold

        last_success_event: AttendanceEvent | None = None
        if best_match is not None:
            last_success_event = session.exec(
                select(AttendanceEvent)
                .where(
                    AttendanceEvent.company_id == request.company_id,
                    AttendanceEvent.employee_id == best_match.employee_id,
                    AttendanceEvent.matched == True,
                )
                .order_by(AttendanceEvent.recorded_at.desc())
                .limit(1)
            ).one_or_none()

        if matched and best_match is not None and last_success_event is not None:
            elapsed = datetime.utcnow() - last_success_event.recorded_at
            if elapsed < self.COOLDOWN_PERIOD:
                remaining = int((self.COOLDOWN_PERIOD - elapsed).total_seconds())
                return VerificationResponse(
                    matched=False,
                    prediction=best_match,
                    model_version=artifact.version,
                    event_id=last_success_event.id,
                    cooldown_seconds=remaining,
                    event_type=last_success_event.event_type,
                    face_box=face_box,
                )

        event_type: Optional[str] = None
        if matched and best_match is not None:
            if last_success_event is not None and last_success_event.event_type:
                event_type = "saida" if last_success_event.event_type.lower() == "entrada" else "entrada"
            else:
                event_type = "entrada"

        event = AttendanceEvent(
            company_id=request.company_id,
            employee_id=best_match.employee_id if matched and best_match else None,
            matched=matched,
            distance=best_match.distance if best_match else None,
            score=best_match.score if best_match else None,
            threshold=request.threshold,
            model_version=artifact.version,
            event_type=event_type,
        )
        session.add(event)
        session.commit()
        session.refresh(event)

        return VerificationResponse(
            matched=matched,
            prediction=best_match,
            model_version=artifact.version,
            event_id=event.id,
            event_type=event.event_type,
            face_box=face_box,
        )

    def list_attendance(
        self,
        session: Session,
        company_id: str,
        start: Optional[datetime] = None,
        end: Optional[datetime] = None,
        employee_id: Optional[str] = None,
        limit: int = 100,
        offset: int = 0,
    ) -> AttendanceListResponse:
        statement = select(AttendanceEvent).where(AttendanceEvent.company_id == company_id)

        if start is not None:
            statement = statement.where(AttendanceEvent.recorded_at >= start)
        if end is not None:
            statement = statement.where(AttendanceEvent.recorded_at <= end)
        if employee_id:
            statement = statement.where(AttendanceEvent.employee_id == employee_id)

        count_statement = select(func.count(AttendanceEvent.id)).where(AttendanceEvent.company_id == company_id)
        if start is not None:
            count_statement = count_statement.where(AttendanceEvent.recorded_at >= start)
        if end is not None:
            count_statement = count_statement.where(AttendanceEvent.recorded_at <= end)
        if employee_id:
            count_statement = count_statement.where(AttendanceEvent.employee_id == employee_id)

        total = session.exec(count_statement).one()

        items = session.exec(
            statement.order_by(AttendanceEvent.recorded_at.desc()).offset(offset).limit(limit)
        ).all()

        records = [
            AttendanceRecord(
                id=item.id,
                company_id=item.company_id,
                employee_id=item.employee_id,
                matched=item.matched,
                distance=item.distance,
                score=item.score,
                threshold=item.threshold,
                model_version=item.model_version,
                event_type=item.event_type,
                recorded_at=item.recorded_at,
            )
            for item in items
        ]

        total_value = int(total[0] if isinstance(total, tuple) else total)

        return AttendanceListResponse(company_id=company_id, total=total_value, items=records)
