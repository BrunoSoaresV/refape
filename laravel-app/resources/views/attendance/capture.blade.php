@extends('layouts.main', ['title' => 'Realizar ponto'])

@section('body')
<header id="header" class="fixed-top header-inner-pages">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo">
            <h1 class="text-light mb-0"><a href="{{ route('dashboard') }}"><span>{{ $empresa->nome ?? 'Refape' }}</span></a></h1>
        </div>
        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto" href="{{ route('employees.create') }}">Cadastrar funcionários</a></li>
                <li><a class="nav-link scrollto" href="{{ route('employees.index') }}">Funcionários cadastrados</a></li>
                <li><a class="nav-link scrollto active" href="{{ route('attendance.capture') }}">Realizar ponto</a></li>
                <li><a class="nav-link scrollto" href="{{ route('attendance.index') }}">Pontos registrados</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link p-0">Sair</button>
                    </form>
                </li>
            </ul>
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>
    </div>
</header>

<main class="pt-5 mt-4" id="attendanceApp" data-company-id="{{ $companyId }}" data-face-api="{{ $faceApiBaseUrl }}">
    <section class="py-5 bg-light min-vh-100">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                <div>
                                    <h2 class="h4 mb-1">Captura em tempo real</h2>
                                    <p class="text-muted mb-0">Posicione o colaborador em frente à câmera, ajuste o enquadramento e clique em "Registrar ponto".</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" id="startCamera" class="btn btn-outline-primary btn-sm" {{ empty($faceApiBaseUrl) ? 'disabled' : '' }}><i class="bi bi-camera-video me-1"></i>Iniciar câmera</button>
                                    <button type="button" id="stopCamera" class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-stop-circle me-1"></i>Encerrar</button>
                                </div>
                            </div>

                            @if(empty($faceApiBaseUrl))
                                <div class="alert alert-warning" role="alert">
                                    Configure a variável <code>FACE_API_BASE_URL</code> e treine um modelo para habilitar a captura de ponto.
                                </div>
                            @endif

                            <div class="ratio ratio-4x3 bg-dark rounded position-relative overflow-hidden">
                                <video id="videoPreview" autoplay playsinline muted class="w-100 h-100 object-fit-cover"></video>
                                <canvas id="snapshotCanvas" class="d-none"></canvas>
                                <div id="cameraPlaceholder" class="position-absolute top-50 start-50 translate-middle text-white-50 small">Câmera desligada</div>
                                <div id="captureIndicator" class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none align-items-center justify-content-center">
                                    <div class="spinner-border text-light" role="status"></div>
                                </div>
                                <div id="faceBoxOverlay" class="position-absolute border border-success border-2 rounded d-none"></div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                                <div id="autoCaptureNotice" class="alert alert-primary py-2 px-3 mb-0 small">
                                    Captura automática em andamento.
                                </div>
                                <button type="button" id="downloadLastCapture" class="btn btn-outline-secondary btn-sm" disabled>Baixar última captura</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h3 class="h5 mb-3">Configurações</h3>
                            <div class="mb-3">
                                <label for="modelSelect" class="form-label">Modelo treinado</label>
                                <select id="modelSelect" class="form-select" {{ empty($faceApiBaseUrl) ? 'disabled' : '' }}>
                                    <option value="">Automático (versão mais recente)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="thresholdRange" class="form-label">Sensibilidade do reconhecimento</label>
                                <input type="range" class="form-range" id="thresholdRange" min="0.30" max="0.60" step="0.01" value="0.45">
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Mais tolerante</span>
                                    <span id="thresholdValue">0.45</span>
                                    <span>Mais rigoroso</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status da verificação</label>
                                <div id="statusBox" class="alert alert-light border mb-0" role="status">Aguardando captura.</div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h3 class="h5 mb-3">Histórico da sessão</h3>
                            <ul id="attemptHistory" class="list-group list-group-flush small"></ul>
                            <div id="emptyHistory" class="text-muted small">Nenhuma tentativa registrada ainda.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    #attemptHistory li {
        padding-left: 0;
        padding-right: 0;
    }
    #faceBoxOverlay {
        pointer-events: none;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.45);
        transition: all 0.12s ease-out;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/face-service.js') }}"></script>
<script>
    (function() {
        const root = document.getElementById('attendanceApp');
        if (!root) {
            return;
        }

        const companyId = root.dataset.companyId || '';
        const faceApiBaseUrl = root.dataset.faceApi?.trim() || '';
        const hasFaceApi = faceApiBaseUrl.length > 0;

    const startCameraBtn = document.getElementById('startCamera');
    const stopCameraBtn = document.getElementById('stopCamera');
    const downloadLastBtn = document.getElementById('downloadLastCapture');
    const autoCaptureNotice = document.getElementById('autoCaptureNotice');
    const faceOverlay = document.getElementById('faceBoxOverlay');
        const video = document.getElementById('videoPreview');
        const canvas = document.getElementById('snapshotCanvas');
        const overlay = document.getElementById('cameraPlaceholder');
        const captureIndicator = document.getElementById('captureIndicator');
        const thresholdRange = document.getElementById('thresholdRange');
        const thresholdValue = document.getElementById('thresholdValue');
        const statusBox = document.getElementById('statusBox');
        const historyList = document.getElementById('attemptHistory');
        const emptyHistory = document.getElementById('emptyHistory');
        const modelSelect = document.getElementById('modelSelect');

    let mediaStream = null;
    let lastCaptureDataUrl = null;
    let isProcessing = false;
    let autoCaptureTimer = null;
    let autoResumeTimeout = null;

    const AUTO_CAPTURE_INTERVAL_MS = 3000;

        function configureService() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return;
            }
            FaceService.configure({ baseUrl: faceApiBaseUrl });
        }

        function setStatus(type, title, description) {
            statusBox.className = `alert alert-${type}`;
            statusBox.innerHTML = `<strong>${title}</strong><br><span class="small">${description}</span>`;
        }

        function appendHistoryEntry(success, message, employeeId, eventId, variant = null, icon = null) {
            emptyHistory.classList.add('d-none');
            const item = document.createElement('li');
            const colorVariant = variant || (success ? 'success' : 'danger');
            const iconClass = icon || (success ? 'bi-check-circle-fill' : 'bi-x-circle-fill');
            item.className = `list-group-item d-flex justify-content-between align-items-center text-${colorVariant}`;
            const time = new Date().toLocaleTimeString();
            item.innerHTML = `
                <span>${time} - ${message}${employeeId ? ` (<strong>${employeeId}</strong>)` : ''}${eventId ? ` · <span class="text-muted">#${eventId}</span>` : ''}</span>
                <i class="bi ${iconClass}"></i>
            `;
            historyList.prepend(item);
        }

        function formatDuration(seconds) {
            if (!Number.isFinite(seconds)) {
                return '';
            }
            const total = Math.max(0, Math.ceil(seconds));
            if (total >= 60) {
                const minutes = Math.floor(total / 60);
                const remainingSeconds = total % 60;
                if (remainingSeconds === 0) {
                    return `${minutes} minuto${minutes > 1 ? 's' : ''}`;
                }
                return `${minutes}m ${remainingSeconds}s`;
            }
            return `${total}s`;
        }

        function updateAutoNotice(message, type = 'primary') {
            if (!autoCaptureNotice) {
                return;
            }
            autoCaptureNotice.className = `alert alert-${type} py-2 px-3 mb-0 small`;
            autoCaptureNotice.textContent = message;
        }

        function hideFaceOverlay() {
            if (!faceOverlay) {
                return;
            }
            faceOverlay.classList.add('d-none');
        }

        function updateFaceOverlay(box, state = 'info') {
            if (!faceOverlay || !box) {
                hideFaceOverlay();
                return;
            }
            faceOverlay.classList.remove('border-success', 'border-warning', 'border-danger', 'border-info');
            const stateClass = state === 'success'
                ? 'border-success'
                : state === 'warning'
                    ? 'border-warning'
                    : state === 'danger'
                        ? 'border-danger'
                        : 'border-info';
            faceOverlay.classList.add(stateClass);
            faceOverlay.style.top = `${Math.min(Math.max(box.top, 0), 1) * 100}%`;
            faceOverlay.style.left = `${Math.min(Math.max(box.left, 0), 1) * 100}%`;
            faceOverlay.style.width = `${Math.min(Math.max(box.width, 0), 1) * 100}%`;
            faceOverlay.style.height = `${Math.min(Math.max(box.height, 0), 1) * 100}%`;
            faceOverlay.classList.remove('d-none');
        }

        function stopAutoCapture() {
            if (autoCaptureTimer) {
                clearInterval(autoCaptureTimer);
                autoCaptureTimer = null;
            }
            if (autoResumeTimeout) {
                clearTimeout(autoResumeTimeout);
                autoResumeTimeout = null;
            }
            isProcessing = false;
        }

        function startAutoCapture() {
            if (autoResumeTimeout) {
                clearTimeout(autoResumeTimeout);
                autoResumeTimeout = null;
            }
            if (autoCaptureTimer || !mediaStream) {
                return;
            }
            updateAutoNotice('Captura automática em andamento.', 'primary');
            autoCaptureTimer = setInterval(() => {
                if (!isProcessing) {
                    verifyCapture();
                }
            }, AUTO_CAPTURE_INTERVAL_MS);
        }

        function pauseAutoCapture(seconds) {
            stopAutoCapture();
            if (!mediaStream) {
                updateAutoNotice('Captura automática pausada.', 'secondary');
                return;
            }
            const waitSeconds = Math.max(0, Math.floor(seconds || 0));
            if (waitSeconds > 0) {
                updateAutoNotice(`Aguardando ${formatDuration(waitSeconds)} para nova tentativa.`, 'info');
                autoResumeTimeout = setTimeout(() => {
                    autoResumeTimeout = null;
                    if (mediaStream) {
                        startAutoCapture();
                    }
                }, waitSeconds * 1000);
            } else {
                startAutoCapture();
            }
        }

        function updateThresholdLabel() {
            thresholdValue.textContent = parseFloat(thresholdRange.value).toFixed(2);
        }

        function setLoading(isLoading) {
            if (downloadLastBtn) {
                downloadLastBtn.disabled = !lastCaptureDataUrl || isLoading;
            }
            captureIndicator.classList.toggle('d-flex', isLoading);
            captureIndicator.classList.toggle('d-none', !isLoading);
        }

        async function startCamera() {
            if (!hasFaceApi) {
                setStatus('warning', 'Serviço indisponível', 'Configure o FACE_API_BASE_URL para habilitar a captura.');
                return;
            }
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                video.srcObject = mediaStream;
                overlay.classList.add('d-none');
                if (stopCameraBtn) {
                    stopCameraBtn.disabled = false;
                }
                if (startCameraBtn) {
                    startCameraBtn.disabled = true;
                }
                setStatus('info', 'Captura automática ativa', 'Permaneça em frente à câmera para registrar o ponto.');
                startAutoCapture();
            } catch (error) {
                console.error(error);
                setStatus('danger', 'Câmera bloqueada', 'Verifique as permissões do navegador e tente novamente.');
            }
        }

        function stopCamera() {
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
                mediaStream = null;
            }
            video.srcObject = null;
            overlay.classList.remove('d-none');
            if (stopCameraBtn) {
                stopCameraBtn.disabled = true;
            }
            if (startCameraBtn) {
                startCameraBtn.disabled = !hasFaceApi;
            }
            stopAutoCapture();
            updateAutoNotice('Captura automática pausada.', 'secondary');
            hideFaceOverlay();
            setStatus('light', 'Câmera desligada', 'Inicie a câmera para registrar um novo ponto.');
        }

        function captureImage() {
            if (!mediaStream) {
                return null;
            }
            const width = video.videoWidth;
            const height = video.videoHeight;
            if (!width || !height) {
                return null;
            }
            canvas.width = width;
            canvas.height = height;
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, width, height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
            lastCaptureDataUrl = dataUrl;
            if (downloadLastBtn) {
                downloadLastBtn.disabled = false;
            }
            return dataUrl.split(',')[1];
        }

        async function verifyCapture() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return;
            }
            if (isProcessing) {
                return;
            }
            isProcessing = true;
            const imageBase64 = captureImage();
            if (!imageBase64) {
                isProcessing = false;
                hideFaceOverlay();
                return;
            }
            const threshold = parseFloat(thresholdRange.value);
            const selectedModel = modelSelect.value ? parseInt(modelSelect.value, 10) : null;

            try {
                setLoading(true);
                setStatus('info', 'Verificando...', 'Estamos comparando a imagem capturada com o modelo treinado.');
                const response = await FaceService.verifyFace(companyId, imageBase64, threshold, selectedModel);
                const eventType = typeof response.event_type === 'string' ? response.event_type.toLowerCase() : null;
                const eventLabel = eventType === 'saida' ? 'Saída' : eventType === 'entrada' ? 'Entrada' : null;
                let overlayState = 'info';

                if (response.cooldown_seconds && response.cooldown_seconds > 0) {
                    const waitMessage = `Último ponto reconhecido recentemente. Aguarde ${formatDuration(response.cooldown_seconds)} para registrar novamente.`;
                    setStatus('info', 'Intervalo em andamento', waitMessage);
                    const cooldownMessage = eventLabel ? `Intervalo mínimo para ${eventLabel.toLowerCase()} ainda em andamento` : 'Intervalo mínimo não atingido';
                    appendHistoryEntry(false, cooldownMessage, response.prediction?.employee_id ?? null, response.event_id, 'warning', 'bi-hourglass-split');
                    pauseAutoCapture(response.cooldown_seconds);
                    overlayState = 'warning';
                } else if (response.matched && response.prediction) {
                    const detail = `Funcionário ${response.prediction.employee_id} identificado com distância ${response.prediction.distance.toFixed(4)}.`;
                    const successLabel = eventLabel ? `${eventLabel} confirmada` : 'Ponto registrado com sucesso';
                    const statusTitle = eventLabel ? `${eventLabel} confirmada (#${response.event_id})` : `Ponto confirmado (#${response.event_id})`;
                    setStatus('success', statusTitle, detail);
                    appendHistoryEntry(true, successLabel, response.prediction.employee_id, response.event_id);
                    pauseAutoCapture(15);
                    overlayState = 'success';
                } else {
                    setStatus('warning', `Não reconhecido (#${response.event_id ?? '—'})`, 'Nenhum colaborador atingiu o nível mínimo de confiança configurado.');
                    appendHistoryEntry(false, 'Reconhecimento não confirmado', null, response.event_id, 'warning', 'bi-exclamation-circle-fill');
                    overlayState = response.face_box ? 'warning' : 'danger';
                }

                updateFaceOverlay(response.face_box, overlayState);
            } catch (error) {
                console.error(error);
                setStatus('danger', 'Erro na verificação', error?.message || 'Falha ao comunicar com o serviço de reconhecimento facial.');
                appendHistoryEntry(false, 'Erro na verificação', null, null, 'danger', 'bi-exclamation-triangle-fill');
                pauseAutoCapture(10);
                hideFaceOverlay();
            } finally {
                setLoading(false);
                isProcessing = false;
            }
        }

        function downloadCapture() {
            if (!lastCaptureDataUrl) {
                return;
            }
            const link = document.createElement('a');
            link.href = lastCaptureDataUrl;
            link.download = `captura-${Date.now()}.jpg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        async function loadModels() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return [];
            }
            try {
                const models = await FaceService.listModels(companyId);
                modelSelect.innerHTML = '<option value="">Automático (versão mais recente)</option>';
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.model_version;
                    option.textContent = `Versão ${model.model_version} · ${new Date(model.created_at).toLocaleString()}`;
                    modelSelect.appendChild(option);
                });
                if (!models.length) {
                    setStatus('warning', 'Modelo não encontrado', 'Cadastre colaboradores e treine o modelo antes de realizar o ponto.');
                    if (startCameraBtn) {
                        startCameraBtn.disabled = true;
                    }
                    updateAutoNotice('Treine o modelo para habilitar a captura automática.', 'warning');
                }
                return models;
            } catch (error) {
                console.error(error);
                setStatus('danger', 'Erro ao carregar modelos', 'Não foi possível obter a lista de modelos treinados.');
                updateAutoNotice('Não foi possível carregar os modelos treinados.', 'danger');
                return [];
            }
        }

        if (!hasFaceApi) {
            if (startCameraBtn) {
                startCameraBtn.disabled = true;
            }
            if (downloadLastBtn) {
                downloadLastBtn.disabled = true;
            }
            setStatus('warning', 'Serviço indisponível', 'Configure o FACE_API_BASE_URL para habilitar a captura.');
            updateAutoNotice('Serviço indisponível.', 'danger');
        }

        configureService();
        updateThresholdLabel();
        const modelsPromise = loadModels();

        if (modelsPromise && typeof modelsPromise.then === 'function') {
            modelsPromise.then(models => {
                if (hasFaceApi && models.length > 0) {
                    startCamera().catch(error => {
                        console.error(error);
                    });
                }
            });
        }

        thresholdRange.addEventListener('input', updateThresholdLabel);
        if (startCameraBtn) {
            startCameraBtn.addEventListener('click', startCamera);
        }
        if (stopCameraBtn) {
            stopCameraBtn.addEventListener('click', stopCamera);
        }
        if (downloadLastBtn) {
            downloadLastBtn.addEventListener('click', downloadCapture);
        }
        window.addEventListener('beforeunload', stopCamera);
    })();
</script>
@endpush
