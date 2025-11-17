document.addEventListener('DOMContentLoaded', function() {
    const healthCheckBtn = document.getElementById('health-check');
    const healthStatus = document.getElementById('health-status');
    const healthOutput = document.getElementById('health-output');

    if (healthCheckBtn) {
        healthCheckBtn.addEventListener('click', async function() {
            try {
                healthCheckBtn.disabled = true;
                healthCheckBtn.textContent = 'Checking...';

                const response = await fetch('/api/health');
                const data = await response.json();

                healthOutput.textContent = JSON.stringify(data, null, 2);
                healthStatus.style.display = 'block';

                healthCheckBtn.textContent = 'Check API Health';
                healthCheckBtn.disabled = false;
            } catch (error) {
                healthOutput.textContent = 'Error: ' + error.message;
                healthStatus.style.display = 'block';
                
                healthCheckBtn.textContent = 'Check API Health';
                healthCheckBtn.disabled = false;
            }
        });
    }
});

const API_BASE_URL = '/api';

async function apiRequest(endpoint, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        },
    };

    const token = localStorage.getItem('auth_token');
    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers,
        },
    };

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, mergedOptions);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Request failed');
        }

        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

window.api = {
    get: (endpoint) => apiRequest(endpoint, { method: 'GET' }),
    post: (endpoint, body) => apiRequest(endpoint, { 
        method: 'POST', 
        body: JSON.stringify(body) 
    }),
    put: (endpoint, body) => apiRequest(endpoint, { 
        method: 'PUT', 
        body: JSON.stringify(body) 
    }),
    delete: (endpoint) => apiRequest(endpoint, { method: 'DELETE' }),
};
