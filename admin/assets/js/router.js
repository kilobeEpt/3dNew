import { State } from './state.js';

export class Router {
    constructor() {
        this.routes = new Map();
        this.defaultRoute = '/';
        this.currentRoute = null;
    }

    add(path, handler) {
        this.routes.set(path, handler);
    }

    setDefault(path) {
        this.defaultRoute = path;
    }

    init() {
        window.addEventListener('hashchange', () => this.handleRoute());
        this.handleRoute();
    }

    handleRoute() {
        const hash = window.location.hash.slice(1) || this.defaultRoute;
        const path = hash.split('?')[0];
        
        const handler = this.routes.get(path);
        
        if (handler) {
            this.currentRoute = path;
            State.setCurrentRoute(path);
            this.updateActiveNav(path);
            this.updatePageTitle(path);
            handler();
        } else {
            this.navigate(this.defaultRoute);
        }
    }

    navigate(path) {
        window.location.hash = path;
    }

    updateActiveNav(path) {
        document.querySelectorAll('.nav-item').forEach(item => {
            const route = item.getAttribute('data-route');
            if (route && path.includes(route)) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    updatePageTitle(path) {
        const titles = {
            '/dashboard': 'Dashboard',
            '/services': 'Services',
            '/materials': 'Materials',
            '/pricing-rules': 'Pricing Rules',
            '/gallery': 'Gallery',
            '/news': 'News',
            '/settings': 'Settings',
            '/requests': 'Customer Requests',
            '/estimates': 'Cost Estimates',
            '/audit-logs': 'Audit Logs'
        };

        const title = titles[path] || 'Admin Panel';
        const pageTitleEl = document.getElementById('page-title');
        if (pageTitleEl) {
            pageTitleEl.textContent = title;
        }
        document.title = `${title} - Admin Panel`;
    }
}
