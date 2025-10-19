from __future__ import annotations

import json
from pathlib import Path
from typing import Dict, List

import numpy as np


class ModelStorage:
    def __init__(self, base_dir: Path) -> None:
        self.base_dir = base_dir
        self.base_dir.mkdir(parents=True, exist_ok=True)

    def _company_dir(self, company_id: str) -> Path:
        path = self.base_dir / company_id
        path.mkdir(parents=True, exist_ok=True)
        return path

    def embeddings_path(self, company_id: str, version: int) -> Path:
        return self._company_dir(company_id) / f"model_v{version}.npz"

    def metadata_path(self, company_id: str, version: int) -> Path:
        return self._company_dir(company_id) / f"model_v{version}.json"

    def save(
        self,
        company_id: str,
        version: int,
        embeddings: Dict[str, List[np.ndarray]],
    ) -> None:
        array_data = {employee_id: np.vstack(v) for employee_id, v in embeddings.items() if v}
        np.savez_compressed(self.embeddings_path(company_id, version), **array_data)

    def load(self, company_id: str, version: int) -> Dict[str, np.ndarray]:
        path = self.embeddings_path(company_id, version)
        if not path.exists():
            return {}
        with np.load(path, allow_pickle=False) as data:
            return {key: data[key] for key in data.files}

    def save_metadata(self, company_id: str, version: int, metadata: Dict[str, int]) -> None:
        path = self.metadata_path(company_id, version)
        path.write_text(json.dumps(metadata, ensure_ascii=False, indent=2))

    def load_metadata(self, company_id: str, version: int) -> Dict[str, int]:
        path = self.metadata_path(company_id, version)
        if not path.exists():
            return {}
        return json.loads(path.read_text())
