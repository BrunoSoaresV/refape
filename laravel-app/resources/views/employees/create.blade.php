@extends('layouts.main', ['title' => 'Cadastrar colaborador'])

@section('body')
<header id="header" class="fixed-top header-inner-pages">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo">
            <h1 class="text-light mb-0"><a href="{{ route('dashboard') }}"><span>{{ $empresa->nome ?? 'Refape' }}</span></a></h1>
        </div>
        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto active" href="{{ route('employees.create') }}">Cadastrar funcionários</a></li>
                <li><a class="nav-link scrollto" href="{{ route('employees.index') }}">Funcionários cadastrados</a></li>
                <li><a class="nav-link scrollto" href="{{ route('attendance.capture') }}">Realizar ponto</a></li>
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

<main class="pt-5 mt-4" id="employeeApp" data-company-id="{{ $companyId }}" data-face-api="{{ $faceApiBaseUrl }}">
    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h2 class="h4 mb-3">Cadastro e coleta de imagens</h2>
                            <p class="text-muted">Informe o identificador do colaborador e adicione pelo menos 3 imagens com boa iluminação. Você pode usar a webcam ou enviar arquivos do seu dispositivo.</p>

                            @if(empty($faceApiBaseUrl))
                                <div class="alert alert-warning" role="alert">
                                    Configure a variável <code>FACE_API_BASE_URL</code> para habilitar a comunicação com o serviço de reconhecimento facial.
                                </div>
                            @endif

                            <form id="employeeForm" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="employeeId" class="form-label">Identificador do colaborador</label>
                                    <input type="text" class="form-control" id="employeeId" placeholder="Ex: FUNC-001" required>
                                    <div class="invalid-feedback">Informe o identificador que será utilizado para reconhecer o colaborador.</div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Adicionar imagens do dispositivo</label>
                                        <input type="file" class="form-control" id="photoInput" accept="image/*" multiple {{ empty($faceApiBaseUrl) ? 'disabled' : '' }}>
                                        <div class="form-text">Formatos aceitos: JPG ou PNG. Tamanho máximo recomendado: 5 MB por imagem.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label d-block">Capturar com a webcam</label>
                                        <div class="d-flex gap-2 mb-2">
                                            <button type="button" id="startCamera" class="btn btn-outline-primary btn-sm" {{ empty($faceApiBaseUrl) ? 'disabled' : '' }}>Iniciar câmera</button>
                                            <button type="button" id="captureFrame" class="btn btn-primary btn-sm" disabled>Capturar foto</button>
                                            <button type="button" id="stopCamera" class="btn btn-outline-secondary btn-sm" disabled>Encerrar</button>
                                        </div>
                                        <div class="ratio ratio-4x3 bg-dark rounded position-relative overflow-hidden">
                                            <video id="cameraPreview" autoplay playsinline muted class="w-100 h-100 object-fit-cover"></video>
                                            <canvas id="captureCanvas" class="d-none"></canvas>
                                            <div id="cameraOverlay" class="position-absolute top-50 start-50 translate-middle text-white-50 small">Câmera desligada</div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="h6">Pré-visualização das imagens</h3>
                                    <p class="text-muted mb-2">Utilize imagens claras, sem óculos escuros e com o rosto centralizado. Clique em uma miniatura para removê-la.</p>
                                    <div id="previewGrid" class="row g-2"></div>
                                    <div id="emptyPreview" class="alert alert-light border text-muted" role="alert">
                                        Nenhuma imagem selecionada até o momento.
                                    </div>
                                </div>

                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="trainAfter" checked>
                                    <label class="form-check-label" for="trainAfter">Treinar o modelo automaticamente após salvar as imagens</label>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <button type="submit" class="btn btn-success" id="submitButton" {{ empty($faceApiBaseUrl) ? 'disabled' : '' }}>Enviar imagens</button>
                                    <button type="button" class="btn btn-outline-secondary" id="clearImages">Limpar imagens</button>
                                </div>
                            </form>
                            <div id="formFeedback" class="mt-3" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body p-4">
                            <h3 class="h5">Boas práticas</h3>
                            <ul class="list-unstyled lh-lg mb-4">
                                <li><i class="bi bi-check-circle me-2 text-success"></i>Capture imagens com diferentes expressões.</li>
                                <li><i class="bi bi-check-circle me-2 text-success"></i>Evite ambientes escuros ou com forte contraluz.</li>
                                <li><i class="bi bi-check-circle me-2 text-success"></i>Garanta que apenas o rosto do colaborador apareça no quadro.</li>
                                <li><i class="bi bi-check-circle me-2 text-success"></i>Treine o modelo sempre que novos colaboradores forem cadastrados.</li>
                            </ul>
                            <div class="alert alert-info" role="alert">
                                Após o treinamento, utilize a opção "Realizar ponto" para validar a captura com o modelo atualizado.
                            </div>
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
    #previewGrid img {
        cursor: pointer;
        border: 2px solid transparent;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    #previewGrid img:hover {
        transform: scale(1.02);
        border-color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/face-service.js') }}"></script>
<script>
    (function() {
    const root = document.getElementById('employeeApp');
        if (!root) {
            return;
        }

        const faceApiBaseUrl = root.dataset.faceApi?.trim() || '';
    const companyId = root.dataset.companyId || '';
    const hasFaceApi = faceApiBaseUrl.length > 0;

        const form = document.getElementById('employeeForm');
        const employeeIdInput = document.getElementById('employeeId');
        const fileInput = document.getElementById('photoInput');
        const previewGrid = document.getElementById('previewGrid');
        const emptyPreview = document.getElementById('emptyPreview');
        const feedback = document.getElementById('formFeedback');
        const submitButton = document.getElementById('submitButton');
        const clearButton = document.getElementById('clearImages');
        const trainAfterInput = document.getElementById('trainAfter');
        const startCameraBtn = document.getElementById('startCamera');
        const captureBtn = document.getElementById('captureFrame');
        const stopCameraBtn = document.getElementById('stopCamera');
        const cameraPreview = document.getElementById('cameraPreview');
        const captureCanvas = document.getElementById('captureCanvas');
        const cameraOverlay = document.getElementById('cameraOverlay');

        let mediaStream = null;
        const imageCollection = [];

        function configureService() {
            if (!hasFaceApi) {
                return;
            }
            try {
                    if (typeof FaceService === 'undefined') {
                        console.warn('FaceService helper não encontrado.');
                        return;
                    }
                FaceService.configure({ baseUrl: faceApiBaseUrl });
            } catch (error) {
                console.error(error);
            }
        }

        function updatePreview() {
            previewGrid.innerHTML = '';
            if (imageCollection.length === 0) {
                emptyPreview.classList.remove('d-none');
                return;
            }
            emptyPreview.classList.add('d-none');
            imageCollection.forEach((item, index) => {
                const col = document.createElement('div');
                col.className = 'col-4 col-md-3';
                const img = document.createElement('img');
                img.src = item.dataUrl;
                img.alt = 'Pré-visualização';
                img.className = 'img-fluid rounded';
                img.addEventListener('click', () => {
                    imageCollection.splice(index, 1);
                    updatePreview();
                });
                col.appendChild(img);
                previewGrid.appendChild(col);
            });
        }

        function clearImages() {
            imageCollection.splice(0, imageCollection.length);
            updatePreview();
        }

        function showFeedback(type, message) {
            feedback.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
        }

        function resetFeedback() {
            feedback.innerHTML = '';
        }

        function toggleForm(disabled) {
            submitButton.disabled = disabled;
            fileInput.disabled = disabled || !hasFaceApi;
            captureBtn.disabled = disabled || !mediaStream;
            startCameraBtn.disabled = disabled || !hasFaceApi || mediaStream !== null;
            stopCameraBtn.disabled = disabled || mediaStream === null;
        }

        async function startCamera() {
            if (!hasFaceApi) {
                return;
            }
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                cameraPreview.srcObject = mediaStream;
                captureBtn.disabled = false;
                stopCameraBtn.disabled = false;
                startCameraBtn.disabled = true;
                cameraOverlay.classList.add('d-none');
            } catch (error) {
                console.error(error);
                showFeedback('danger', 'Não foi possível acessar a câmera. Verifique as permissões do navegador.');
            }
        }

        function stopCamera() {
            if (mediaStream) {
                mediaStream.getTracks().forEach(track => track.stop());
                mediaStream = null;
            }
            cameraPreview.srcObject = null;
            captureBtn.disabled = true;
            stopCameraBtn.disabled = true;
            startCameraBtn.disabled = !hasFaceApi;
            cameraOverlay.classList.remove('d-none');
        }

        function capturePhoto() {
            if (!mediaStream) {
                return;
            }
            const video = cameraPreview;
            const canvas = captureCanvas;
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
            const base64 = dataUrl.split(',')[1];
            imageCollection.push({ dataUrl, base64 });
            updatePreview();
        }

        async function handleFileSelection(event) {
            const files = Array.from(event.target.files || []);
            if (!files.length) {
                return;
            }
            for (const file of files) {
                if (!file.type.startsWith('image/')) {
                    continue;
                }
                const reader = new FileReader();
                reader.onload = (loadEvent) => {
                    const dataUrl = loadEvent.target.result;
                    if (typeof dataUrl === 'string') {
                        const base64 = dataUrl.split(',')[1];
                        imageCollection.push({ dataUrl, base64 });
                        updatePreview();
                    }
                };
                reader.readAsDataURL(file);
            }
            fileInput.value = '';
        }

        async function submitForm(event) {
            event.preventDefault();
            resetFeedback();

            if (!hasFaceApi) {
                showFeedback('warning', 'Serviço de reconhecimento facial indisponível.');
                return;
            }

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            if (imageCollection.length === 0) {
                showFeedback('warning', 'Adicione ao menos uma imagem do colaborador.');
                return;
            }

            const employeeId = employeeIdInput.value.trim();
            toggleForm(true);
            showFeedback('info', 'Enviando imagens para o serviço de reconhecimento facial...');

            try {
                const samples = [{ employee_id: employeeId, images: imageCollection.map(item => item.base64) }];
                const ingestResponse = await FaceService.ingestSamples(companyId, samples);
                showFeedback('success', `Imagens registradas com sucesso. Total de ${ingestResponse.total_images} imagens no dataset.`);

                if (trainAfterInput.checked) {
                    showFeedback('info', 'Treinando modelo. Isso pode levar alguns segundos...');
                    const training = await FaceService.trainModel(companyId, []);
                    showFeedback('success', `Modelo versão ${training.model_version} treinado. Funcionários treinados: ${training.metrics.trained_employees}.`);
                }

                clearImages();
                employeeIdInput.focus();
            } catch (error) {
                console.error(error);
                const message = error?.message || 'Não foi possível concluir o cadastro. Tente novamente em instantes.';
                showFeedback('danger', message);
            } finally {
                toggleForm(false);
            }
        }

        configureService();
        updatePreview();

        window.addEventListener('beforeunload', stopCamera);
        startCameraBtn.addEventListener('click', startCamera);
        stopCameraBtn.addEventListener('click', stopCamera);
        captureBtn.addEventListener('click', capturePhoto);
        fileInput.addEventListener('change', handleFileSelection);
        clearButton.addEventListener('click', clearImages);
        form.addEventListener('submit', submitForm);
    })();
</script>
@endpush
