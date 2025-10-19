from __future__ import annotations

from contextlib import contextmanager
from pathlib import Path
from typing import Iterator

from sqlmodel import Session, SQLModel, create_engine

DATA_DIR = Path(__file__).resolve().parent.parent / "data"
DATA_DIR.mkdir(parents=True, exist_ok=True)

DB_PATH = DATA_DIR / "face_service.db"
ENGINE = create_engine(
    f"sqlite:///{DB_PATH}", connect_args={"check_same_thread": False}, pool_pre_ping=True
)


def init_db() -> None:
    """Create database tables on startup."""
    SQLModel.metadata.create_all(ENGINE)
    with ENGINE.begin() as connection:
        columns = {
            row[1]
            for row in connection.exec_driver_sql("PRAGMA table_info(attendanceevent);").fetchall()
        }
        if "event_type" not in columns:
            connection.exec_driver_sql("ALTER TABLE attendanceevent ADD COLUMN event_type VARCHAR")


@contextmanager
def session_scope() -> Iterator[Session]:
    session = Session(ENGINE)
    try:
        yield session
        session.commit()
    except Exception:
        session.rollback()
        raise
    finally:
        session.close()


def get_session() -> Iterator[Session]:
    with Session(ENGINE) as session:
        yield session
