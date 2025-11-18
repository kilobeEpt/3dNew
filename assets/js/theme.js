const THEME_STORAGE_KEY = 'site-theme';
const THEME_LIGHT = 'light';
const THEME_DARK = 'dark';

class ThemeManager {
    constructor() {
        this.currentTheme = this.getStoredTheme() || this.getPreferredTheme();
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.setupMediaQuery();
    }

    getStoredTheme() {
        try {
            return localStorage.getItem(THEME_STORAGE_KEY);
        } catch (error) {
            console.error('Error reading theme from localStorage:', error);
            return null;
        }
    }

    getPreferredTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return THEME_DARK;
        }
        return THEME_LIGHT;
    }

    applyTheme(theme) {
        if (theme === THEME_DARK) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
        this.currentTheme = theme;
        this.storeTheme(theme);
    }

    storeTheme(theme) {
        try {
            localStorage.setItem(THEME_STORAGE_KEY, theme);
        } catch (error) {
            console.error('Error storing theme in localStorage:', error);
        }
    }

    toggle() {
        const newTheme = this.currentTheme === THEME_LIGHT ? THEME_DARK : THEME_LIGHT;
        this.applyTheme(newTheme);
        this.notifyListeners();
        return newTheme;
    }

    setupMediaQuery() {
        if (!window.matchMedia) return;

        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = (e) => {
            if (!this.getStoredTheme()) {
                this.applyTheme(e.matches ? THEME_DARK : THEME_LIGHT);
                this.notifyListeners();
            }
        };

        if (mediaQuery.addEventListener) {
            mediaQuery.addEventListener('change', handler);
        } else if (mediaQuery.addListener) {
            mediaQuery.addListener(handler);
        }
    }

    getTheme() {
        return this.currentTheme;
    }

    isDark() {
        return this.currentTheme === THEME_DARK;
    }

    onThemeChange(callback) {
        if (!this.listeners) {
            this.listeners = [];
        }
        this.listeners.push(callback);
    }

    notifyListeners() {
        if (this.listeners) {
            this.listeners.forEach(callback => callback(this.currentTheme));
        }
    }
}

const themeManager = new ThemeManager();

export { themeManager, ThemeManager, THEME_LIGHT, THEME_DARK };
