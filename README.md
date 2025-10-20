# 🧠 Refape – Facial Recognition Attendance Platform

**Refape** (Facial Recognition for Presence) is an intelligent system for **employee attendance management through facial recognition**.
It leverages modern technologies in **backend, frontend, machine learning, and container orchestration** to deliver a scalable, maintainable, and production-ready solution.

---

## 🚀 Overview

The system integrates a **Laravel (PHP) administrative panel** with a **Python FastAPI microservice** responsible for facial recognition using a **Convolutional Neural Network (CNN)**.
Facial recognition runs locally on the FastAPI service, while the Laravel dashboard manages users, attendance logs, and reports.

This project was developed as the **Final Course Project (TCC)** for the **Technical Course in Informatics at the Federal Institute of Minas Gerais (IFMG)**, receiving a **100% approval rating** for its technical excellence and real-world applicability.

---

## 🧩 Architecture

| Layer                | Technology                       | Description                                                   |
| -------------------- | -------------------------------- | ------------------------------------------------------------- |
| **Frontend**         | HTML, CSS, Bootstrap, JavaScript | Responsive and intuitive web interface for users and admins   |
| **Backend**          | PHP (Laravel)                    | Core business logic, API endpoints, and attendance management |
| **AI Service**       | Python (FastAPI + CNN)           | Facial recognition microservice using deep learning models    |
| **Database**         | PostgreSQL                       | Relational database for employee and attendance data          |
| **Version Control**  | Git + GitHub                     | Source code management and collaboration                      |
| **Containerization** | Docker                           | Isolated and reproducible environments for all components     |
| **Cloud Services**   | Microsoft Azure                  | Cloud deployment and scalability                              |
| **Orchestration**    | Kubernetes                       | Multi-container management in production environments         |

---

## ⚙️ Features

✅ Employee registration and management
✅ Facial recognition for clock-in and clock-out
✅ Real-time validation using CNN-based model
✅ Dashboard for monitoring attendance records
✅ Secure RESTful API communication
✅ Containerized setup (Docker + Docker Compose)
✅ Cloud-ready deployment with Kubernetes manifests

---

## 🐳 Container Orchestration

This repository includes both **Docker Compose** and **Kubernetes manifests** to run the Laravel backoffice and the FastAPI face-recognition service together.

### 🧱 Docker Compose

1. Make sure **Docker Desktop** is running and navigate to the project root:

   ```powershell
   cd C:\Users\bruno\Downloads\rf\refape
   ```
2. Build and start all services:

   ```powershell
   docker compose up --build
   ```
3. Access:

   * Laravel app: [http://localhost:8000](http://localhost:8000)
   * Face service: [http://localhost:8001](http://localhost:8001)
4. Persistent data:

   * `laravel_storage` → keeps Laravel app data
   * `face_models` → stores trained embeddings
5. To stop and clean up:

   ```powershell
   docker compose down
   ```

   Add `--volumes` if you want to reset stored data.

> **Tip:**
> You can override environment variables like `APP_ENV`, `APP_DEBUG`, or `FACE_SERVICE_URL` using a `.env` file at the project root.

---

### ☸️ Kubernetes Deployment

The `k8s/` directory contains minimal manifests targeting a namespace called `refape`.

#### 1. Build and publish container images

```powershell
docker build -t <registry>/refape-laravel:latest -f docker/laravel/Dockerfile .
docker build -t <registry>/refape-face-service:latest -f docker/face-service/Dockerfile .
docker push <registry>/refape-laravel:latest
docker push <registry>/refape-face-service:latest
```

#### 2. Update manifests

Replace `your-registry/refape-*` with the published image names.

#### 3. Apply manifests

```powershell
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/face-service.yaml
kubectl apply -f k8s/laravel-app.yaml
```

#### 4. Local testing

```powershell
kubectl -n refape port-forward service/laravel-app 8000:80
```

Access the Laravel dashboard at [http://localhost:8000](http://localhost:8000).

> The manifests use `emptyDir` volumes for temporary writable storage.
> Replace them with `PersistentVolumeClaim` objects for production-grade durability.

---

## 🧹 Cleanup

* Docker Compose stack:

  ```bash
  docker compose down --volumes
  ```
* Kubernetes resources:

  ```bash
  kubectl delete -f k8s/laravel-app.yaml -f k8s/face-service.yaml -f k8s/namespace.yaml
  ```

---

## 🧠 Machine Learning Details

The **face-recognition service** is powered by a **Convolutional Neural Network (CNN)** trained to extract and compare facial embeddings.
It supports:

* Face detection and alignment
* Feature extraction via deep learning
* Embedding storage and comparison
* Real-time recognition through REST API

---

## 🧑‍💻 Development Setup

**Requirements**

* Docker Desktop or Minikube
* Git
* Python 3.10+
* PHP 8+
* Node.js (for Laravel frontend)

**Clone the repository**

```bash
git clone https://github.com/<your-username>/refape.git
cd refape
```

**Run in development mode**

```bash
docker compose up --build
```

---

** 
$env:FACE_API_ALLOWED_ORIGINS="http://127.0.0.1:8100"; .venv/Scripts/python.exe -m uvicorn face_service.app.main:app --host 0.0.0.0 --port 8000 **

**php artisan serve --host 127.0.0.1 --port 8100**

## 🏆 Credits

**Project Lead & Developer:** [Bruno Veríssimo](https://github.com/brunosoaresv)
**Institution:** Federal Institute of Minas Gerais (IFMG)
**Course:** Technical Course in Informatics
**Project Type:** Final Course Project (TCC)
**Grade:** 100% Approved

---
