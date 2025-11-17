const API_BASE_URL = '/api';
const CACHE_DURATION = 5 * 60 * 1000;

class APIClient {
    constructor() {
        this.cache = new Map();
    }

    async get(endpoint, options = {}) {
        const { useCache = true, params = {} } = options;
        const url = this.buildURL(endpoint, params);
        const cacheKey = url;

        if (useCache && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < CACHE_DURATION) {
                return cached.data;
            }
            this.cache.delete(cacheKey);
        }

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (useCache) {
                this.cache.set(cacheKey, {
                    data,
                    timestamp: Date.now(),
                });
            }

            return data;
        } catch (error) {
            console.error('API GET error:', error);
            throw error;
        }
    }

    async post(endpoint, body, options = {}) {
        const { params = {} } = options;
        const url = this.buildURL(endpoint, params);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(body),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new APIError(data.message || 'Request failed', data);
            }

            return data;
        } catch (error) {
            console.error('API POST error:', error);
            throw error;
        }
    }

    buildURL(endpoint, params = {}) {
        const url = new URL(`${API_BASE_URL}${endpoint}`, window.location.origin);
        Object.keys(params).forEach(key => {
            if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
                url.searchParams.append(key, params[key]);
            }
        });
        return url.toString();
    }

    clearCache() {
        this.cache.clear();
    }

    removeCacheEntry(endpoint) {
        const url = this.buildURL(endpoint);
        this.cache.delete(url);
    }

    async getServices(params = {}) {
        return this.get('/services', { params });
    }

    async getService(id) {
        return this.get(`/services/${id}`);
    }

    async getMaterials(params = {}) {
        return this.get('/materials', { params });
    }

    async getMaterial(id) {
        return this.get(`/materials/${id}`);
    }

    async getMaterialCategories() {
        return this.get('/materials/categories');
    }

    async getGallery(params = {}) {
        return this.get('/gallery', { params });
    }

    async getGalleryItem(id) {
        return this.get(`/gallery/${id}`);
    }

    async getNews(params = {}) {
        return this.get('/news', { params });
    }

    async getNewsPost(id) {
        return this.get(`/news/${id}`);
    }

    async getSettings(group = null) {
        return this.get('/settings', { params: group ? { group } : {} });
    }

    async submitContact(data) {
        return this.post('/contact', data);
    }

    async submitCostEstimate(data) {
        return this.post('/cost-estimates', data);
    }

    async getCsrfToken() {
        return this.get('/csrf-token', { useCache: false });
    }
}

class APIError extends Error {
    constructor(message, data) {
        super(message);
        this.name = 'APIError';
        this.data = data;
    }
}

const api = new APIClient();

export { api, APIClient, APIError };
