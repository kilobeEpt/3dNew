import { themeManager } from './theme.js';
import { Navigation } from './navigation.js';
import { setupBackToTop, setupFadeInAnimations } from './utils.js';

class App {
    constructor() {
        this.init();
    }

    init() {
        this.setupThemeToggle();
        this.navigation = new Navigation();
        setupBackToTop();
        setupFadeInAnimations();
        this.setupAccessibility();
    }

    setupThemeToggle() {
        const themeToggle = document.querySelector('.theme-toggle');
        if (!themeToggle) return;

        this.updateThemeIcon(themeToggle, themeManager.getTheme());

        themeToggle.addEventListener('click', () => {
            const newTheme = themeManager.toggle();
            this.updateThemeIcon(themeToggle, newTheme);
        });

        themeManager.onThemeChange((theme) => {
            this.updateThemeIcon(themeToggle, theme);
        });
    }

    updateThemeIcon(button, theme) {
        const isDark = theme === 'dark';
        button.innerHTML = isDark
            ? `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>`
            : `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
              </svg>`;
        button.setAttribute('aria-label', isDark ? 'Switch to light theme' : 'Switch to dark theme');
    }

    setupAccessibility() {
        document.addEventListener('DOMContentLoaded', () => {
            const main = document.querySelector('main');
            if (main && !main.getAttribute('id')) {
                main.setAttribute('id', 'main-content');
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new App();
    });
} else {
    new App();
}

export { App };
