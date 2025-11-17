import { Router } from './router.js';
import { AuthService } from './auth.js';
import { State } from './state.js';
import { Toast } from './components/toast.js';

import { DashboardView } from './views/dashboard.js';
import { ServicesView } from './views/services.js';
import { MaterialsView } from './views/materials.js';
import { PricingRulesView } from './views/pricing-rules.js';
import { GalleryView } from './views/gallery.js';
import { NewsView } from './views/news.js';
import { SettingsView } from './views/settings.js';
import { RequestsView } from './views/requests.js';
import { EstimatesView } from './views/estimates.js';
import { AuditLogsView } from './views/audit-logs.js';

class App {
    constructor() {
        this.state = State;
        this.auth = AuthService;
        this.router = new Router();
        this.toast = Toast;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuth();
        this.setupRouter();
    }

    setupEventListeners() {
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.handleLogout());
        }

        const userMenuTrigger = document.getElementById('user-menu-trigger');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');
        if (userMenuTrigger && userMenuDropdown) {
            userMenuTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenuDropdown.style.display = 
                    userMenuDropdown.style.display === 'none' ? 'block' : 'none';
            });

            document.addEventListener('click', () => {
                userMenuDropdown.style.display = 'none';
            });
        }

        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    sidebar?.classList.remove('open');
                }
            });
        });
    }

    setupRouter() {
        this.router.add('/dashboard', () => new DashboardView().render());
        this.router.add('/services', () => new ServicesView().render());
        this.router.add('/materials', () => new MaterialsView().render());
        this.router.add('/pricing-rules', () => new PricingRulesView().render());
        this.router.add('/gallery', () => new GalleryView().render());
        this.router.add('/news', () => new NewsView().render());
        this.router.add('/settings', () => new SettingsView().render());
        this.router.add('/requests', () => new RequestsView().render());
        this.router.add('/estimates', () => new EstimatesView().render());
        this.router.add('/audit-logs', () => new AuditLogsView().render());
        
        this.router.setDefault('/dashboard');
        this.router.init();
    }

    async checkAuth() {
        const token = this.auth.getAccessToken();
        
        if (!token) {
            this.showLogin();
            return;
        }

        try {
            const user = await this.auth.getCurrentUser();
            this.state.setUser(user);
            this.showAdmin();
            this.updateUserDisplay();
        } catch (error) {
            console.error('Auth check failed:', error);
            this.showLogin();
        }
    }

    showLogin() {
        document.getElementById('login-view').style.display = 'flex';
        document.getElementById('admin-view').style.display = 'none';
    }

    showAdmin() {
        document.getElementById('login-view').style.display = 'none';
        document.getElementById('admin-view').style.display = 'flex';
    }

    updateUserDisplay() {
        const user = this.state.getUser();
        if (!user) return;

        const userNameEl = document.getElementById('user-name');
        const userAvatarEl = document.getElementById('user-avatar');
        const userInfoNameEl = document.getElementById('user-info-name');
        const userInfoRoleEl = document.getElementById('user-info-role');

        if (userNameEl) {
            userNameEl.textContent = user.first_name || user.username;
        }

        if (userAvatarEl) {
            const initial = (user.first_name || user.username).charAt(0).toUpperCase();
            userAvatarEl.textContent = initial;
        }

        if (userInfoNameEl) {
            userInfoNameEl.textContent = `${user.first_name || ''} ${user.last_name || ''}`.trim() || user.username;
        }

        if (userInfoRoleEl) {
            userInfoRoleEl.textContent = this.formatRole(user.role);
        }
    }

    formatRole(role) {
        return role.split('_').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const submitBtn = e.target.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';

        try {
            const response = await this.auth.login(username, password);
            this.state.setUser(response.user);
            this.showAdmin();
            this.updateUserDisplay();
            this.toast.success('Welcome back!', 'Login successful');
            this.router.navigate('/dashboard');
        } catch (error) {
            this.toast.error('Login Failed', error.message || 'Invalid credentials');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Sign In';
        }
    }

    async handleLogout() {
        try {
            await this.auth.logout();
            this.state.setUser(null);
            this.showLogin();
            this.toast.success('Logged Out', 'You have been logged out successfully');
        } catch (error) {
            console.error('Logout error:', error);
            this.state.setUser(null);
            this.showLogin();
        }
    }
}

new App();
