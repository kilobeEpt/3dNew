class StateManager {
    constructor() {
        this.state = {
            user: null,
            currentRoute: null
        };
        this.listeners = [];
    }

    getState() {
        return { ...this.state };
    }

    setState(updates) {
        this.state = { ...this.state, ...updates };
        this.notify();
    }

    setUser(user) {
        this.state.user = user;
        this.notify();
    }

    getUser() {
        return this.state.user;
    }

    setCurrentRoute(route) {
        this.state.currentRoute = route;
        this.notify();
    }

    getCurrentRoute() {
        return this.state.currentRoute;
    }

    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    notify() {
        this.listeners.forEach(listener => listener(this.state));
    }

    hasPermission(permission) {
        const user = this.getUser();
        if (!user) return false;

        const role = user.role;
        
        const permissions = {
            super_admin: ['read', 'write', 'delete', 'manage_users', 'manage_settings'],
            admin: ['read', 'write', 'delete'],
            editor: ['read', 'write'],
            viewer: ['read']
        };

        return permissions[role]?.includes(permission) || false;
    }

    canWrite() {
        return this.hasPermission('write');
    }

    canDelete() {
        return this.hasPermission('delete');
    }

    canManageSettings() {
        return this.hasPermission('manage_settings');
    }
}

export const State = new StateManager();
