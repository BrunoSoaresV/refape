@extends('layouts.main', ['title' => 'Pontos registrados'])

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
                <li><a class="nav-link scrollto" href="{{ route('attendance.capture') }}">Realizar ponto</a></li>
                <li><a class="nav-link scrollto active" href="{{ route('attendance.index') }}">Pontos registrados</a></li>
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

<main class="pt-5 mt-4" id="attendanceHistory" data-company-id="{{ $companyId }}" data-face-api="{{ $faceApiBaseUrl }}">
    <section class="py-5 bg-light min-vh-100">
        <div class="container py-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <h2 class="h4 mb-1">Registros de ponto</h2>
                    <p class="text-muted mb-0">Visualize os batimentos realizados pelos colaboradores. A integração com o backend será adicionada na próxima etapa.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="exportCsv" class="btn btn-outline-primary btn-sm"><i class="bi bi-filetype-csv me-1"></i>Exportar CSV</button>
                    <button type="button" id="refreshHistory" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Atualizar</button>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <form id="filtersForm" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Data inicial</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">Data final</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-3">
                            <label for="employeeFilter" class="form-label">Colaborador</label>
                            <input type="text" class="form-control" id="employeeFilter" placeholder="Identificador">
                        </div>
                        <div class="col-md-3 d-grid d-md-block">
                            <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap mb-0" id="historyTable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Data e hora</th>
                                    <th scope="col">Colaborador</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Resultado</th>
                                    <th scope="col">Distância</th>
                                    <th scope="col">Modelo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Nenhum registro disponível. Os batimentos aparecerão aqui após a integração com o backend.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script src="{{ asset('assets/face-service.js') }}"></script>
<script>
    (function() {
        const root = document.getElementById('attendanceHistory');
        if (!root) {
            return;
        }

        const companyId = root.dataset.companyId || '';
        const faceApiBaseUrl = root.dataset.faceApi?.trim() || '';
        const hasFaceApi = faceApiBaseUrl.length > 0;

        const historyTableBody = document.querySelector('#historyTable tbody');
        const filtersForm = document.getElementById('filtersForm');
        const exportButton = document.getElementById('exportCsv');
        const refreshButton = document.getElementById('refreshHistory');
        const startInput = document.getElementById('startDate');
        const endInput = document.getElementById('endDate');
        const employeeInput = document.getElementById('employeeFilter');
    const emptyStateRow = '<tr><td colspan="6" class="text-center text-muted py-4">Nenhum registro para os filtros selecionados.</td></tr>';

        const feedbackContainer = document.createElement('div');
        feedbackContainer.setAttribute('aria-live', 'polite');
        feedbackContainer.className = 'mt-3';
        root.querySelector('.container').appendChild(feedbackContainer);

        let cachedRecords = [];

        function configureService() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                refreshButton.disabled = true;
                exportButton.disabled = true;
                return;
            }
            FaceService.configure({ baseUrl: faceApiBaseUrl });
        }

        function showFeedback(type, message) {
            feedbackContainer.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
        }

        function clearFeedback() {
            feedbackContainer.innerHTML = '';
        }

        function renderTable(records) {
            historyTableBody.innerHTML = '';
            if (!records.length) {
                historyTableBody.innerHTML = emptyStateRow;
                return;
            }

            records.forEach((record) => {
                const typeLabel = typeof record.event_type === 'string' ? record.event_type.toLowerCase() : null;
                const displayType = typeLabel === 'saida'
                    ? '<span class="badge bg-secondary-subtle text-secondary">Saída</span>'
                    : typeLabel === 'entrada'
                        ? '<span class="badge bg-primary-subtle text-primary">Entrada</span>'
                        : '—';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${new Date(record.recorded_at).toLocaleString()}</td>
                    <td class="fw-medium">${record.employee_id ?? '—'}</td>
                    <td>${displayType}</td>
                    <td>${record.matched ? '<span class="badge bg-success-subtle text-success">Confirmado</span>' : '<span class="badge bg-warning-subtle text-warning">Não reconhecido</span>'}</td>
                    <td>${record.distance !== null && record.distance !== undefined ? record.distance.toFixed(4) : '—'}</td>
                    <td>${record.model_version ?? '—'}</td>
                `;
                historyTableBody.appendChild(row);
            });
        }

        function formatDateValue(input) {
            if (!input.value) {
                return null;
            }
            try {
                const date = new Date(input.value);
                if (Number.isNaN(date.getTime())) {
                    return null;
                }
                return date.toISOString();
            } catch (error) {
                console.error(error);
                return null;
            }
        }

        async function loadAttendance() {
            if (!hasFaceApi || typeof FaceService === 'undefined') {
                return;
            }

            const params = {
                start: formatDateValue(startInput),
                end: formatDateValue(endInput),
                employee_id: employeeInput.value.trim() || undefined,
                limit: 500,
                offset: 0,
            };

            try {
                clearFeedback();
                showFeedback('info', 'Carregando registros de ponto...');
                const response = await FaceService.listAttendance(companyId, params);
                cachedRecords = response?.items ?? [];
                renderTable(cachedRecords);
                clearFeedback();
                if ((response?.total ?? 0) > cachedRecords.length) {
                    showFeedback('warning', `Exibindo ${cachedRecords.length} de ${response.total} registros. Ajuste os filtros para refinar os resultados.`);
                }
            } catch (error) {
                console.error(error);
                showFeedback('danger', error?.message || 'Não foi possível carregar os registros de ponto.');
            }
        }

        function exportCsvFile() {
            if (!cachedRecords.length) {
                alert('Nenhum registro disponível para exportação.');
                return;
            }

            const header = ['ID', 'Data e hora', 'Colaborador', 'Tipo', 'Resultado', 'Distância', 'Score', 'Modelo', 'Limite'];
            const rows = cachedRecords.map(record => [
                record.id,
                new Date(record.recorded_at).toISOString(),
                record.employee_id ?? '',
                record.event_type ?? '',
                record.matched ? 'Confirmado' : 'Não reconhecido',
                record.distance ?? '',
                record.score ?? '',
                record.model_version ?? '',
                record.threshold,
            ]);

            const csvContent = [header, ...rows]
                .map(row => row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(','))
                .join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `pontos-${Date.now()}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        filtersForm.addEventListener('submit', function(event) {
            event.preventDefault();
            loadAttendance();
        });

        refreshButton.addEventListener('click', function() {
            loadAttendance();
        });

        exportButton.addEventListener('click', exportCsvFile);

        configureService();

        if (hasFaceApi && typeof FaceService !== 'undefined') {
            loadAttendance();
        } else {
            showFeedback('warning', 'Serviço de reconhecimento facial indisponível. Configure o FACE_API_BASE_URL para acessar os registros.');
        }
    })();
</script>
@endpush
