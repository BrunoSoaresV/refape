from __future__ import annotations

import json
from pathlib import Path
from typing import Dict, List

import numpy as np


class ModelStorage:
    def __init__(self, base_dir: Path) -> None:
        self.base_dir = base_dir
        self.base_dir.mkdir(parents=True, exist_ok=True)

    def _model_path(self, company_id: str) -> Path:
        return self.base_dir / f"{company_id}.npz"

    def save(self, company_id: str, embeddings: Dict[str, List[np.ndarray]]) -> None:
        array_data = {employee_id: np.vstack(v) for employee_id, v in embeddings.items() if v}
        np.savez_compressed(self._model_path(company_id), **array_data)

    def load(self, company_id: str) -> Dict[str, np.ndarray]:
        path = self._model_path(company_id)
        if not path.exists():
            return {}
        with np.load(path, allow_pickle=False) as data:
            return {key: data[key] for key in data.files}

    def metadata_path(self, company_id: str) -> Path:
        return self.base_dir / f"{company_id}.json"

    def save_metadata(self, company_id: str, metadata: Dict[str, int]) -> None:
        path = self.metadata_path(company_id)
        path.write_text(json.dumps(metadata, ensure_ascii=False, indent=2))

    def load_metadata(self, company_id: str) -> Dict[str, int]:
        path = self.metadata_path(company_id)
        if not path.exists():
            return {}
        return json.loads(path.read_text())
