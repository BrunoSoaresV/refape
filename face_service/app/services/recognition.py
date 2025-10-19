from __future__ import annotations

import base64
import io
from typing import Dict, List, Tuple

import face_recognition
import numpy as np
from PIL import Image

from ..schemas import Prediction, TrainingRequest, TrainingResult, VerificationRequest, VerificationResponse
from ..storage import ModelStorage


class FaceRecognitionService:
    def __init__(self, storage: ModelStorage) -> None:
        self.storage = storage

    def _decode_image(self, encoded_image: str) -> np.ndarray:
        decoded = base64.b64decode(encoded_image)
        image = Image.open(io.BytesIO(decoded)).convert('RGB')
        return np.array(image)

    def _compute_embedding(self, image: np.ndarray) -> Tuple[bool, np.ndarray | None]:
        locations = face_recognition.face_locations(image, model='hog')
        if not locations:
            return False, None
        encodings = face_recognition.face_encodings(image, known_face_locations=locations)
        if not encodings:
            return False, None
        return True, encodings[0]

    def train(self, request: TrainingRequest) -> TrainingResult:
        embeddings: Dict[str, List[np.ndarray]] = {}
        total_images = 0

        for sample in request.samples:
            embeddings.setdefault(sample.employee_id, [])
            for encoded_image in sample.images:
                image = self._decode_image(encoded_image)
                success, embedding = self._compute_embedding(image)
                if success and embedding is not None:
                    embeddings[sample.employee_id].append(embedding)
                    total_images += 1

        filtered_embeddings = {k: v for k, v in embeddings.items() if v}
        if not filtered_embeddings:
            raise ValueError('Não foi possível extrair embeddings válidos das imagens enviadas.')

        self.storage.save(request.company_id, filtered_embeddings)
        self.storage.save_metadata(request.company_id, {k: len(v) for k, v in filtered_embeddings.items()})

        return TrainingResult(
            company_id=request.company_id,
            trained_employees=len(filtered_embeddings),
            total_images=total_images,
        )

    def verify(self, request: VerificationRequest) -> VerificationResponse:
        embeddings = self.storage.load(request.company_id)
        if not embeddings:
            return VerificationResponse(matched=False, prediction=None)

        image = self._decode_image(request.image)
        success, target_embedding = self._compute_embedding(image)
        if not success or target_embedding is None:
            return VerificationResponse(matched=False, prediction=None)

        best_match: Prediction | None = None
        lowest_distance = float('inf')

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

        matched = best_match is not None and lowest_distance <= request.threshold
        return VerificationResponse(matched=matched, prediction=best_match)
