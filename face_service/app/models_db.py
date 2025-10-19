from __future__ import annotations

from datetime import datetime
from typing import Optional

from sqlalchemy import UniqueConstraint
from sqlmodel import Field, SQLModel


class Company(SQLModel, table=True):
    id: str = Field(primary_key=True, index=True)
    created_at: datetime = Field(default_factory=datetime.utcnow, nullable=False)


class Employee(SQLModel, table=True):
    id: str = Field(primary_key=True)
    company_id: str = Field(foreign_key="company.id", primary_key=True)
    created_at: datetime = Field(default_factory=datetime.utcnow, nullable=False)


class Sample(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    company_id: str = Field(foreign_key="company.id", index=True)
    employee_id: str = Field(index=True)
    file_path: str
    created_at: datetime = Field(default_factory=datetime.utcnow, nullable=False)


class ModelArtifact(SQLModel, table=True):
    __table_args__ = (UniqueConstraint("company_id", "version", name="uq_company_version"),)

    id: Optional[int] = Field(default=None, primary_key=True)
    company_id: str = Field(foreign_key="company.id", index=True)
    version: int = Field(index=True)
    embeddings_path: str
    metadata_path: str
    metrics: str
    created_at: datetime = Field(default_factory=datetime.utcnow, nullable=False)


class AttendanceEvent(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    company_id: str = Field(foreign_key="company.id", index=True)
    employee_id: Optional[str] = Field(default=None, index=True)
    matched: bool = Field(default=False, nullable=False)
    distance: Optional[float] = Field(default=None)
    score: Optional[float] = Field(default=None)
    threshold: float = Field(nullable=False)
    model_version: Optional[int] = Field(default=None, index=True)
    event_type: Optional[str] = Field(default=None, index=True, description="Tipo do ponto: entrada ou saída")
    recorded_at: datetime = Field(default_factory=datetime.utcnow, nullable=False)
