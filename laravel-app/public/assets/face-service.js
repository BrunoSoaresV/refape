(function (global) {
    const defaultHeaders = {
        'Content-Type': 'application/json',
    };

    let baseUrl = '';

    function normalizeUrl(url) {
        if (!url) {
            return '';
        }
        return url.replace(/\/+$/, '');
    }

    function ensureConfigured() {
        if (!baseUrl) {
            throw new Error('Serviço de reconhecimento facial não configurado.');
        }
    }

    async function request(path, options) {
        ensureConfigured();
        const config = options ? { ...options } : {};
        config.method = config.method || 'GET';
        config.headers = { ...defaultHeaders, ...(config.headers || {}) };

        if (config.body && typeof config.body !== 'string') {
            config.body = JSON.stringify(config.body);
        }

        const url = `${baseUrl}${path}`;
        const response = await fetch(url, config);

        if (!response.ok) {
            let message = 'Erro ao comunicar com o serviço de reconhecimento facial.';
            try {
                const payload = await response.json();
                message = payload?.detail || payload?.message || message;
            } catch (parseError) {
                try {
                    const text = await response.text();
                    if (text) {
                        message = text;
                    }
                } catch (textError) {
                    console.warn(textError);
                }
                console.warn(parseError);
            }
            throw new Error(message);
        }

        if (response.status === 204) {
            return null;
        }

        try {
            return await response.json();
        } catch (error) {
            return null;
        }
    }

    const api = {
        configure(options) {
            baseUrl = normalizeUrl(options?.baseUrl) || '';
        },
        ingestSamples(companyId, samples) {
            return request('/datasets', {
                method: 'POST',
                body: {
                    company_id: companyId,
                    samples,
                },
            });
        },
        getDatasetSummary(companyId) {
            return request(`/companies/${encodeURIComponent(companyId)}/dataset`);
        },
        listModels(companyId) {
            return request(`/companies/${encodeURIComponent(companyId)}/models`);
        },
        getLatestModel(companyId) {
            return request(`/companies/${encodeURIComponent(companyId)}/models/latest`);
        },
        trainModel(companyId, samples) {
            return request('/train', {
                method: 'POST',
                body: {
                    company_id: companyId,
                    samples: samples || [],
                },
            });
        },
        verifyFace(companyId, imageBase64, threshold, modelVersion) {
            const body = {
                company_id: companyId,
                image: imageBase64,
                threshold,
            };
            if (modelVersion) {
                body.model_version = modelVersion;
            }
            return request('/verify', {
                method: 'POST',
                body,
            });
        },
        listAttendance(companyId, params) {
            const searchParams = new URLSearchParams();
            if (params) {
                if (params.start) {
                    searchParams.set('start', params.start);
                }
                if (params.end) {
                    searchParams.set('end', params.end);
                }
                if (params.employee_id) {
                    searchParams.set('employee_id', params.employee_id);
                }
                if (params.limit) {
                    searchParams.set('limit', params.limit);
                }
                if (params.offset) {
                    searchParams.set('offset', params.offset);
                }
            }
            const query = searchParams.toString();
            const path = query ? `/companies/${encodeURIComponent(companyId)}/attendance?${query}` : `/companies/${encodeURIComponent(companyId)}/attendance`;
            return request(path);
        },
    };

    global.FaceService = api;
}(window));
