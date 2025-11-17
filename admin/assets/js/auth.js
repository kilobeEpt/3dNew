import { API } from './api.js';

class Auth {
    constructor() {
        this.accessTokenKey = 'admin_access_token';
        this.refreshTokenKey = 'admin_refresh_token';
        this.userKey = 'admin_user';
    }

    async login(username, password) {
        const response = await API.post('/admin/auth/login', { username, password });
        
        if (response.success && response.data) {
            this.setAccessToken(response.data.access_token);
            this.setRefreshToken(response.data.refresh_token);
            return response.data;
        }
        
        throw new Error(response.message || 'Login failed');
    }

    async logout() {
        try {
            await API.post('/admin/auth/logout');
        } catch (error) {
            console.error('Logout API error:', error);
        } finally {
            this.clearTokens();
        }
    }

    async refreshAccessToken() {
        const refreshToken = this.getRefreshToken();
        
        if (!refreshToken) {
            throw new Error('No refresh token available');
        }

        const response = await API.post('/admin/auth/refresh', { 
            refresh_token: refreshToken 
        });

        if (response.success && response.data) {
            this.setAccessToken(response.data.access_token);
            return response.data.access_token;
        }

        throw new Error('Token refresh failed');
    }

    async getCurrentUser() {
        const response = await API.get('/admin/auth/me');
        
        if (response.success && response.data) {
            return response.data;
        }
        
        throw new Error('Failed to get current user');
    }

    setAccessToken(token) {
        localStorage.setItem(this.accessTokenKey, token);
    }

    getAccessToken() {
        return localStorage.getItem(this.accessTokenKey);
    }

    setRefreshToken(token) {
        localStorage.setItem(this.refreshTokenKey, token);
    }

    getRefreshToken() {
        return localStorage.getItem(this.refreshTokenKey);
    }

    clearTokens() {
        localStorage.removeItem(this.accessTokenKey);
        localStorage.removeItem(this.refreshTokenKey);
        localStorage.removeItem(this.userKey);
    }

    isAuthenticated() {
        return !!this.getAccessToken();
    }
}

export const AuthService = new Auth();
