@extends('layouts.main', ['title' => 'Funcionários cadastrados'])

@section('body')
<header id="header" class="fixed-top header-inner-pages">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo">
            <h1 class="text-light mb-0"><a href="{{ route('dashboard') }}"><span>{{ $empresa->nome ?? 'Refape' }}</span></a></h1>
        </div>
        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto" href="{{ route('employees.create') }}">Cadastrar funcionários</a></li>
                <li><a class="nav-link scrollto active" href="{{ route('employees.index') }}">Funcionários cadastrados</a></li>
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

<main class="pt-5 mt-4" id="employeeDataset" data-company-id="{{ $companyId }}" data-face-api="{{ $faceApiBaseUrl }}">
    <section class="py-5 bg-light min-vh-100">
        <div class="container py-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="h4 mb-1">Resumo do dataset de reconhecimento facial</h2>
                    <p class="text-muted mb-0">Visualize os colaboradores cadastrados, o volume de imagens e acompanhe as versões do modelo treinado.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="refreshDataset" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Atualizar dados</button>
                    <button type="button" id="trainModel" class="btn btn-primary btn-sm"><i class="bi bi-cpu me-1"></i>Treinar modelo agora</button>
                </div>
            </div>

            @if(empty($faceApiBaseUrl))
                <div class="alert alert-warning" role="alert">
                    Configure a variável <code>FACE_API_BASE_URL</code> para habilitar a comunicação com o serviço de reconhecimento facial.
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="h5 mb-0">Colaboradores cadastrados</h3>
                                <span id="totalImages" class="badge bg-primary-subtle text-primary">0 imagens</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="employeesTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">Identificador</th>
                                            <th scope="col" class="text-center">Imagens</th>
                                            <th scope="col" class="text-end">Atualizado em</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Nenhum colaborador cadastrado até o momento.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h3 class="h5 mb-3">Último treinamento</h3>
                            <div id="latestModelInfo" class="alert alert-light border text-muted" role="alert">
                                Nenhum modelo treinado até o momento.
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h3 class="h5 mb-3">Histórico de modelos</h3>
                            <div id="modelsTimeline" class="timeline-sm"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="datasetFeedback" class="mt-4" aria-live="polite"></div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    .timeline-sm {
        position: relative;
        padding-left: 1.5rem;
    }
    .timeline-sm::before {
        content: '';
        position: absolute;
        top: 0.5rem;
        bottom: 0.5rem;
        left: 0.5rem;
        width: 2px;
        background: rgba(13, 110, 253, 0.25);
    }
    .timeline-sm-item {
        position: relative;
        margin-bottom: 1.5rem;
        padding-left: 1rem;
    }
    .timeline-sm-item:last-child {
        margin-bottom: 0;
    }
    .timeline-sm-badge {
        position: absolute;
        left: -0.95rem;
        top: 0.35rem;
        width: 0.75rem;
        height: 0.75rem;
        background: #0d6efd;
        border-radius: 50%;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12);
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/face-service.js') }}"></script>
<script>
    (function() {
        const root = document.getElementById('employeeDataset');
        if (!root) {
            return;
        }

        const companyId = root.dataset.companyId || '';
        const faceApiBaseUrl = root.dataset.faceApi?.trim() || '';
        const hasFaceApi = faceApiBaseUrl.length > 0;

        const refreshButton = document.getElementById('refreshDataset');
        const trainButton = document.getElementById('trainModel');
        const employeesTableBody = document.querySelector('#employeesTable tbody');
        const totalImagesBadge = document.getElementById('totalImages');
        const latestModelInfo = document.getElementById('latestModelInfo');
        const modelsTimeline = document.getElementById('modelsTimeline');
        const feedback = document.getElementById('datasetFeedback');

        function configureService() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return;
            }
            FaceService.configure({ baseUrl: faceApiBaseUrl });
        }

        if (!hasFaceApi) {
            refreshButton.disabled = true;
            trainButton.disabled = true;
        }

        function showFeedback(type, message) {
            feedback.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
        }

        function clearFeedback() {
            feedback.innerHTML = '';
        }

        function setLoading(isLoading) {
            refreshButton.disabled = isLoading;
            trainButton.disabled = isLoading;
        }

        function renderDataset(summary) {
            totalImagesBadge.textContent = `${summary.total_images} imagem${summary.total_images === 1 ? '' : 's'}`;
            employeesTableBody.innerHTML = '';

            if (!summary.employees || summary.employees.length === 0) {
                employeesTableBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Nenhum colaborador cadastrado até o momento.</td></tr>';
                return;
            }

            summary.employees.forEach((employee) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="fw-medium">${employee.employee_id}</td>
                    <td class="text-center">${employee.images}</td>
                    <td class="text-end text-muted">Atualização disponível após novo treinamento</td>
                `;
                employeesTableBody.appendChild(row);
            });
        }

        function renderLatestModel(model) {
            if (!model) {
                latestModelInfo.className = 'alert alert-light border text-muted';
                latestModelInfo.textContent = 'Nenhum modelo treinado até o momento.';
                return;
            }

            const trainedEmployees = model.metrics?.trained_employees ?? 0;
            const validEmbeddings = model.metrics?.valid_embeddings ?? 0;

            latestModelInfo.className = 'alert alert-success';
            latestModelInfo.innerHTML = `
                <strong>Modelo versão ${model.model_version}</strong><br>
                Treinado em ${new Date(model.created_at).toLocaleString()}<br>
                Colaboradores treinados: ${trainedEmployees}<br>
                Embeddings válidos: ${validEmbeddings}
            `;
        }

        function renderModelsTimeline(models) {
            modelsTimeline.innerHTML = '';
            if (!models || models.length === 0) {
                modelsTimeline.innerHTML = '<p class="text-muted mb-0">Treine o primeiro modelo para visualizar o histórico.</p>';
                return;
            }

            models.forEach((model) => {
                const item = document.createElement('div');
                item.className = 'timeline-sm-item';
                item.innerHTML = `
                    <span class="timeline-sm-badge"></span>
                    <h4 class="h6 mb-1">Versão ${model.model_version}</h4>
                    <p class="text-muted mb-1">${new Date(model.created_at).toLocaleString()}</p>
                    <p class="small mb-0">Embeddings: ${model.metrics.valid_embeddings} | Funcionários: ${model.metrics.trained_employees}</p>
                `;
                modelsTimeline.appendChild(item);
            });
        }

        async function loadDataset() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return;
            }
            try {
                setLoading(true);
                clearFeedback();
                const summary = await FaceService.getDatasetSummary(companyId);
                renderDataset(summary);
            } catch (error) {
                console.error(error);
                showFeedback('danger', 'Não foi possível carregar o resumo do dataset.');
            } finally {
                setLoading(false);
            }
        }

        async function loadModels() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return;
            }
            try {
                const models = await FaceService.listModels(companyId);
                renderModelsTimeline(models);
                if (models.length > 0) {
                    renderLatestModel(models[0]);
                } else {
                    renderLatestModel(null);
                }
            } catch (error) {
                console.error(error);
                renderLatestModel(null);
            }
        }

        async function onTrainModel() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return;
            }
            try {
                setLoading(true);
                showFeedback('info', 'Treinando modelo. Isso pode levar alguns segundos...');
                const result = await FaceService.trainModel(companyId, []);
                showFeedback('success', `Modelo versão ${result.model_version} treinado com sucesso.`);
                await loadDataset();
                await loadModels();
            } catch (error) {
                console.error(error);
                const message = error?.message || 'Falha no treinamento. Tente novamente.';
                showFeedback('danger', message);
            } finally {
                setLoading(false);
            }
        }

        configureService();

        if (hasFaceApi && typeof FaceService !== 'undefined') {
            loadDataset();
            loadModels();
        }

        refreshButton.addEventListener('click', loadDataset);
        trainButton.addEventListener('click', onTrainModel);
    })();
</script>
@endpush
