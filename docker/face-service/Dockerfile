# syntax=docker/dockerfile:1

FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        build-essential \
        cmake \
        libopenblas-dev \
        liblapack-dev \
        libboost-all-dev \
        libjpeg-dev \
        libpng-dev \
        python3-dev \
    && rm -rf /var/lib/apt/lists/*

COPY face_service/requirements.txt ./requirements.txt

RUN pip install --no-cache-dir -r requirements.txt

COPY face_service/app ./app

RUN mkdir -p /app/models

EXPOSE 8001

CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001"]
