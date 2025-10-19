# Refape Container Orchestration

This repository ships with Docker Compose and Kubernetes manifests so you can run the Laravel backoffice and the FastAPI face-recognition service with a single command.

## Docker Compose

1. Ensure Docker Desktop is running and you are in the project root (`c:\Users\bruno\Downloads\rf\refape`).
2. Build and start both services:
   ```powershell
   docker compose up --build
   ```
3. The Laravel app is available at http://localhost:8000 and proxies to the face service at `face-service:8001` inside the compose network (also published on http://localhost:8001).
4. Named volumes (`laravel_storage`, `face_models`) hold writable data so container rebuilds do not wipe the Laravel storage directory or trained embeddings.
5. Stop the stack with `docker compose down` (add `--volumes` if you want to reset stored data).

Environment hints:

- Override `APP_ENV`, `APP_DEBUG`, or `FACE_SERVICE_URL` by adding them to a `.env` file in the project root and referencing it with the `env_file` directive or by extending the compose file.
- The Laravel image copies `.env.example` to `.env` and generates an `APP_KEY` automatically on first boot. Provide your own `.env` if you need deterministic secrets.

## Kubernetes

The `k8s` directory contains a minimal manifest set targeting a namespace called `refape`:

1. Build and publish container images:
   ```powershell
   docker build -t <registry>/refape-laravel:latest -f docker/laravel/Dockerfile .
   docker build -t <registry>/refape-face-service:latest -f docker/face-service/Dockerfile .
   docker push <registry>/refape-laravel:latest
   docker push <registry>/refape-face-service:latest
   ```
2. Replace `your-registry/refape-*` in the Kubernetes manifests with the image names you published.
3. Apply the resources:
   ```powershell
   kubectl apply -f k8s/namespace.yaml
   kubectl apply -f k8s/face-service.yaml
   kubectl apply -f k8s/laravel-app.yaml
   ```
4. Expose the Laravel interface for local testing (one option):
   ```powershell
   kubectl -n refape port-forward service/laravel-app 8000:80
   ```
5. The manifests mount `emptyDir` volumes for writable paths; swap them for `PersistentVolumeClaim` objects when you need durable storage.

## Cleanup

- Compose stack: `docker compose down --volumes`
- Kubernetes resources: `kubectl delete -f k8s/laravel-app.yaml -f k8s/face-service.yaml -f k8s/namespace.yaml`

Adjust the manifests as needed to add databases, secrets, or ingress controllers for your target environment.
